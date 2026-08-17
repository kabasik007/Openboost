#!/usr/bin/env python3
"""SiteZilla/Openboost local OpenCart deploy agent.

Standard-library implementation for Git monitoring + FTP/FTPS incremental deploys.
The repository's upload/ CONTENTS map to the configured OpenCart site root.
Installer/OCMOD XML is sent to the signed PHP bridge instead of being uploaded
blindly to the public root.
"""

from __future__ import print_function

import argparse
import ftplib
import glob
import hashlib
import hmac
import json
import os
import posixpath
import subprocess
import sys
import time
import uuid
from datetime import datetime

try:
    from urllib.request import Request, urlopen
    from urllib.error import HTTPError, URLError
except ImportError:  # pragma: no cover
    from urllib2 import Request, urlopen, HTTPError, URLError


class DeployError(Exception):
    pass


def run(cmd, cwd=None, check=True):
    proc = subprocess.Popen(cmd, cwd=cwd, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    out, err = proc.communicate()
    out = out.decode('utf-8', 'replace').strip()
    err = err.decode('utf-8', 'replace').strip()
    if check and proc.returncode != 0:
        raise DeployError('%s\n%s' % (' '.join(cmd), err or out))
    return out


def load_json(path):
    with open(path, 'r') as fh:
        return json.load(fh)


def save_json(path, data):
    parent = os.path.dirname(path)
    if parent and not os.path.isdir(parent):
        os.makedirs(parent)
    tmp = path + '.tmp'
    with open(tmp, 'w') as fh:
        json.dump(data, fh, indent=2, sort_keys=True)
    if os.path.exists(path):
        os.remove(path)
    os.rename(tmp, path)


def secret_value(cfg, direct_key, env_key, default_env=None):
    direct = cfg.get(direct_key)
    if direct:
        return direct
    env_name = cfg.get(env_key) or default_env
    if env_name:
        return os.environ.get(env_name)
    return None


def now_stamp():
    return datetime.now().strftime('%Y%m%d-%H%M%S')


def git_sha(repo, ref):
    return run(['git', 'rev-parse', ref], cwd=repo)


def ensure_repo(cfg):
    repo = os.path.abspath(os.path.expanduser(cfg['repo_path']))
    if not os.path.isdir(os.path.join(repo, '.git')):
        raise DeployError('Not a Git repository: %s' % repo)
    return repo


def fetch_target(repo, branch):
    run(['git', 'fetch', '--prune', 'origin'], cwd=repo)
    return git_sha(repo, 'origin/%s' % branch)


def checkout_target(repo, branch, sha):
    current = run(['git', 'status', '--porcelain'], cwd=repo)
    if current:
        raise DeployError('Working tree is not clean; refusing automatic checkout.')
    run(['git', 'checkout', branch], cwd=repo)
    run(['git', 'merge', '--ff-only', sha], cwd=repo)


def changed_paths(repo, old_sha, new_sha):
    if not old_sha:
        out = run(['git', 'ls-tree', '-r', '--name-only', new_sha], cwd=repo)
        return [('A', p, None) for p in out.splitlines() if p]

    out = run(['git', 'diff', '--name-status', '-M', old_sha, new_sha], cwd=repo)
    changes = []
    for line in out.splitlines():
        parts = line.split('\t')
        status = parts[0]
        if status.startswith('R') and len(parts) >= 3:
            changes.append(('D', parts[1], None))
            changes.append(('A', parts[2], None))
        elif len(parts) >= 2:
            changes.append((status[0], parts[1], None))
    return changes


class FtpTransport(object):
    def __init__(self, cfg):
        self.cfg = cfg
        self.ftp = None

    def connect(self):
        password = secret_value(self.cfg, 'password', 'password_env', 'OPENBOOST_FTP_PASSWORD')
        if not password:
            raise DeployError('FTP password is not configured. Use ftp.password_env where possible.')
        cls = ftplib.FTP_TLS if self.cfg.get('tls', False) else ftplib.FTP
        self.ftp = cls()
        self.ftp.connect(self.cfg['host'], int(self.cfg.get('port', 21)), timeout=30)
        self.ftp.login(self.cfg['user'], password)
        if isinstance(self.ftp, ftplib.FTP_TLS):
            self.ftp.prot_p()
        return self

    def close(self):
        if self.ftp:
            try:
                self.ftp.quit()
            except Exception:
                try:
                    self.ftp.close()
                except Exception:
                    pass

    def _mkdirs(self, remote_dir):
        if not remote_dir or remote_dir == '/':
            return
        cur = ''
        for part in remote_dir.strip('/').split('/'):
            cur += '/' + part
            try:
                self.ftp.mkd(cur)
            except ftplib.error_perm:
                pass

    def upload(self, local_path, remote_path):
        self._mkdirs(posixpath.dirname(remote_path))
        with open(local_path, 'rb') as fh:
            self.ftp.storbinary('STOR ' + remote_path, fh)

    def download_if_exists(self, remote_path, local_path):
        parent = os.path.dirname(local_path)
        if parent and not os.path.isdir(parent):
            os.makedirs(parent)
        try:
            with open(local_path, 'wb') as fh:
                self.ftp.retrbinary('RETR ' + remote_path, fh.write)
            return True
        except ftplib.error_perm as exc:
            try:
                os.remove(local_path)
            except OSError:
                pass
            if str(exc).startswith('550'):
                return False
            raise

    def delete(self, remote_path):
        try:
            self.ftp.delete(remote_path)
            return True
        except ftplib.error_perm as exc:
            if str(exc).startswith('550'):
                return False
            raise


def map_upload_path(repo_path, cfg):
    prefix = cfg.get('upload_dir', 'upload').strip('/\\') + '/'
    normalized = repo_path.replace('\\', '/')
    if not normalized.startswith(prefix):
        return None
    rel = normalized[len(prefix):]
    if not rel or rel.startswith('../') or '/..' in rel:
        return None
    remote_root = cfg['ftp'].get('remote_root', '/').rstrip('/')
    return (remote_root + '/' + rel).replace('//', '/')


def discover_installer(repo, cfg):
    # These are DB/installer XML sources. File-based OCMODs that belong under
    # upload/system/*.ocmod.xml should remain normal upload-tree files.
    candidates = cfg.get('ocmod', {}).get('installer_files', [
        'install.xml',
        'install.ocmod.xml'
    ])
    found = []
    for pattern in candidates:
        found.extend(glob.glob(os.path.join(repo, pattern)))

    result = []
    seen = set()
    for path in sorted(found):
        ap = os.path.abspath(path)
        if os.path.isfile(ap) and ap not in seen:
            seen.add(ap)
            result.append(ap)

    if len(result) > 1 and not cfg.get('ocmod', {}).get('allow_multiple_installer_files', False):
        raise DeployError('Multiple installer XML files matched; configure one exact installer or explicitly allow multiple.')
    return result


def bridge_call(cfg, action, payload):
    bridge = cfg.get('bridge') or {}
    url = bridge.get('url')
    secret = secret_value(bridge, 'secret', 'secret_env', 'OPENBOOST_DEPLOY_SECRET')
    if not url:
        raise DeployError('Bridge URL is not configured.')
    if not secret:
        raise DeployError('Bridge secret is not configured.')

    body_obj = {'action': action, 'payload': payload}
    body = json.dumps(body_obj, separators=(',', ':'), sort_keys=True).encode('utf-8')
    ts = str(int(time.time()))
    nonce = uuid.uuid4().hex
    signed = ts.encode('ascii') + b'\n' + nonce.encode('ascii') + b'\n' + body
    sig = hmac.new(secret.encode('utf-8'), signed, hashlib.sha256).hexdigest()

    req = Request(url, data=body)
    req.add_header('Content-Type', 'application/json')
    req.add_header('X-Deploy-Timestamp', ts)
    req.add_header('X-Deploy-Nonce', nonce)
    req.add_header('X-Deploy-Signature', sig)

    try:
        res = urlopen(req, timeout=int(bridge.get('timeout', 60)))
        raw = res.read().decode('utf-8', 'replace')
    except (HTTPError, URLError) as exc:
        raise DeployError('Bridge request failed: %s' % exc)

    try:
        data = json.loads(raw)
    except ValueError:
        raise DeployError('Bridge returned invalid JSON: %s' % raw[:500])
    if not data.get('ok'):
        raise DeployError('Bridge action %s failed: %s' % (action, data.get('error', raw)))
    return data


def health_check(cfg):
    url = cfg.get('health_url')
    if not url:
        return {'skipped': True}
    try:
        res = urlopen(url, timeout=int(cfg.get('health_timeout', 20)))
        code = getattr(res, 'status', None) or res.getcode()
        if int(code) >= 400:
            raise DeployError('Health check HTTP %s' % code)
        return {'http_status': int(code)}
    except Exception as exc:
        raise DeployError('Health check failed: %s' % exc)


def requires_cache_actions(changes, installer_changed, cfg):
    actions = []
    if installer_changed:
        actions.append('ocmod_refresh')
    paths = [p.lower() for _, p, _ in changes]
    if any(p.endswith(('.tpl', '.twig')) for p in paths):
        actions.extend(cfg.get('cache_rules', {}).get('templates', []))
    if any(p.endswith(('.css', '.js')) for p in paths):
        actions.extend(cfg.get('cache_rules', {}).get('assets', []))
    if any(p.endswith('.php') for p in paths):
        actions.extend(cfg.get('cache_rules', {}).get('php', []))
    result = []
    for action in actions:
        if action not in result:
            result.append(action)
    return result


def deploy_once(cfg, dry_run=False):
    repo = ensure_repo(cfg)
    branch = cfg['branch']
    state_path = os.path.join(repo, cfg.get('state_file', '.openboost-deploy-state.json'))
    state = load_json(state_path) if os.path.isfile(state_path) else {}
    old_sha = state.get('last_deployed_sha')

    new_sha = fetch_target(repo, branch)
    if old_sha == new_sha:
        return {'changed': False, 'sha': new_sha}

    checkout_target(repo, branch, new_sha)
    changes = changed_paths(repo, old_sha, new_sha)
    upload_changes = []
    for status, path, _ in changes:
        remote = map_upload_path(path, cfg)
        if remote:
            upload_changes.append((status, path, remote))

    installer_paths = discover_installer(repo, cfg)
    installer_repo_paths = [os.path.relpath(p, repo).replace('\\', '/') for p in installer_paths]
    changed_names = set(p for _, p, _ in changes)
    installer_changed = (not old_sha and bool(installer_paths)) or any(p in changed_names for p in installer_repo_paths)

    summary = {
        'changed': True,
        'branch': branch,
        'old_sha': old_sha,
        'new_sha': new_sha,
        'files': upload_changes,
        'installer_changed': installer_changed,
    }
    if dry_run:
        return summary

    backup_root = os.path.join(repo, cfg.get('backup_dir', '.deploy-backups'), now_stamp())
    allow_delete = bool(cfg.get('allow_remote_delete', False))
    transport = FtpTransport(cfg['ftp']).connect()
    uploaded = []
    deleted = []
    backups = []
    try:
        for status, rel, remote in upload_changes:
            backup = os.path.join(backup_root, rel.replace('/', os.sep))
            existed = transport.download_if_exists(remote, backup)
            backups.append({
                'repository_path': rel,
                'remote_path': remote,
                'backup_path': backup if existed else None,
                'remote_existed': bool(existed),
                'change_status': status,
            })
            if status == 'D':
                if allow_delete and transport.delete(remote):
                    deleted.append(remote)
                continue
            local_path = os.path.join(repo, rel.replace('/', os.sep))
            if os.path.isfile(local_path):
                transport.upload(local_path, remote)
                uploaded.append(remote)
    finally:
        transport.close()

    bridge_results = []
    if installer_changed:
        oc_cfg = cfg.get('ocmod', {})
        canonical = oc_cfg.get('canonical_code')
        if not canonical:
            raise DeployError('ocmod.canonical_code is required when installer XML changes.')
        for xml_path in installer_paths:
            with open(xml_path, 'rb') as fh:
                xml_text = fh.read().decode('utf-8')
            bridge_results.append(bridge_call(cfg, 'ocmod_upsert', {
                'canonical_code': canonical,
                'legacy_code_regexes': oc_cfg.get('legacy_code_regexes', []),
                'xml': xml_text,
                'status': int(oc_cfg.get('status', 1)),
            }))

    for action in requires_cache_actions(changes, installer_changed, cfg):
        if action == 'ocmod_refresh':
            bridge_results.append(bridge_call(cfg, 'ocmod_refresh', {}))
        elif action == 'opcache_reset':
            bridge_results.append(bridge_call(cfg, 'opcache_reset', {}))
        else:
            bridge_results.append(bridge_call(cfg, 'cache_clear', {'profile': action}))

    health = health_check(cfg)
    state = {
        'last_deployed_sha': new_sha,
        'previous_deployed_sha': old_sha,
        'branch': branch,
        'deployed_at': datetime.now().isoformat(),
        'uploaded': uploaded,
        'deleted': deleted,
        'backups': backups,
        'backup_root': backup_root,
        'health': health,
    }
    save_json(state_path, state)
    summary.update(state)
    summary['bridge'] = bridge_results
    return summary


def main():
    parser = argparse.ArgumentParser(description='Openboost OpenCart deploy agent')
    parser.add_argument('config', help='Path to project JSON config')
    parser.add_argument('--watch', action='store_true', help='Keep polling the configured branch')
    parser.add_argument('--dry-run', action='store_true')
    args = parser.parse_args()

    cfg = load_json(args.config)
    interval = int(cfg.get('poll_seconds', 30))

    while True:
        try:
            result = deploy_once(cfg, dry_run=args.dry_run)
            print(json.dumps(result, indent=2, sort_keys=True))
        except Exception as exc:
            print('[DEPLOY ERROR] %s' % exc, file=sys.stderr)
            if not args.watch:
                return 1
        if not args.watch:
            return 0
        time.sleep(max(5, interval))


if __name__ == '__main__':
    sys.exit(main())
