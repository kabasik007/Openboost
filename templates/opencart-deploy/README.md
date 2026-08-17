# OpenCart Local Deploy Agent + Server Bridge

This template implements a reusable development deployment path:

```text
GitHub/Git branch
  ↓
local_agent.py on the developer PC
  ↓
FTP/FTPS incremental file upload
  ↓
server_bridge.php
  ↓
OCMOD DB upsert + full refresh adapter + configured cache actions
```

It is intended for development/staging first. Production auto-deploy should be an explicit project decision.

## Files

- `local_agent.py` — Git monitor, diff deployer, FTP/FTPS uploader, backup coordinator and signed bridge client.
- `project.example.json` — per-project configuration example.
- `server_bridge.php` — HMAC-authenticated privileged server endpoint for OCMOD/cache operations.
- `oc23_refresh_adapter.php` — OpenCart 2.3 OCMOD generated-tree rebuild adapter.

## 1. Local setup

Copy the template outside the project repository or into a private tooling directory.

Create a project config from `project.example.json`.

Important mapping:

```text
repo/upload/catalog/... → SITE_ROOT/catalog/...
repo/upload/admin/...   → SITE_ROOT/admin/...
repo/upload/system/...  → SITE_ROOT/system/...
```

The deployer maps the **contents** of `upload/` to the OpenCart root.

Do not set `remote_root` to `/upload` unless the actual OpenCart installation itself lives there.

Keep credentials out of Git. The example reads both secrets from environment variables:

```powershell
$env:OPENBOOST_FTP_PASSWORD="ftp-password"
$env:OPENBOOST_DEPLOY_SECRET="a-long-random-secret"
```

Run once:

```bash
python local_agent.py project.json --dry-run
python local_agent.py project.json
```

Watch continuously:

```bash
python local_agent.py project.json --watch
```

VS Code may launch the script, but VS Code is not part of the deployment protocol. Windows Task Scheduler or another process supervisor can run the watcher independently.

## 2. Server setup

Place `server_bridge.php` and the matching refresh adapter in a restricted server directory.

Edit the `$CONFIG` block in `server_bridge.php`:

- random `shared_secret` matching the local environment secret;
- real OpenCart storefront `config.php` path;
- real `admin_dir` when the admin folder was renamed;
- allowed module code prefixes;
- audit log and nonce-cache locations;
- cache profiles with **project-specific exact paths**;
- OCMOD refresh adapter path.

The starter intentionally ships Journal cache profiles with no paths. Do not replace those placeholders with a guessed universal cache directory. Inspect the real Journal/OpenCart installation first.

For production:

- HTTPS is required;
- additionally restrict the bridge by IP/web-server policy where possible;
- do not expose DB port 3306;
- do not store the bridge secret in the Git repository;
- do not enable arbitrary command execution;
- keep OPcache reset disabled unless actually required.

The bridge rejects stale timestamps and replayed nonces inside the configured window.

## 3. Stable OCMOD code

Use one ownership code forever:

```xml
<code>sitezilla_promo_hub</code>
<version>1.5.14</version>
```

Do not generate a new code per release.

When an old module already used versioned codes, configure explicit anchored legacy regexes:

```json
"legacy_code_regexes": [
  "^sitezilla_promo_hub_v?[0-9._-]+$"
]
```

The bridge performs this order:

```text
parse XML
→ replace XML <code> with canonical code
→ UPDATE canonical modification if present, otherwise INSERT
→ remove only explicitly matched owned legacy codes
→ OCMOD refresh action
```

This avoids duplicate modifications while preserving unrelated extensions.

## 4. Incremental file deployment

The state file stores the last successfully deployed commit SHA.

Next deployment uses:

```text
git diff --name-status OLD..NEW
```

Only changed files inside configured `upload/` are transferred.

Before overwrite/delete the agent tries to download the current remote file into `.deploy-backups/` and records whether that remote file previously existed.

Remote deletion is disabled by default. Enable `allow_remote_delete` only after confirming the project expects Git deletions to remove production files.

The state SHA is advanced only after upload, bridge actions and health check succeed.

## 5. Installer XML vs file-based OCMOD

Installer XML paths are configurable and are detected separately from deployable web files.

The default DB-installer candidates are only:

```text
install.xml
install.ocmod.xml
```

A file such as:

```text
upload/system/my_module.ocmod.xml
```

is a normal file-based OCMOD source and should remain in the `upload/` tree; do not also import it into the DB unless the target project explicitly uses that architecture.

If multiple installer XML files match, the local agent refuses the deployment by default rather than sequentially overwriting one canonical DB modification. Configure one exact installer path whenever possible.

When installer XML changed:

```text
local XML
  ↓ signed HTTPS
server bridge
  ↓
oc_modification upsert by canonical code
  ↓
legacy owned-code cleanup
  ↓
full OCMOD rebuild
```

Do not merely delete one generated file from `system/storage/modification`. OpenCart 2.3's own refresh lifecycle clears and rebuilds the generated tree from all enabled modification sources.

The 2.3 starter adapter follows the platform's normal DB modification name ordering and supports a configurable renamed admin directory. If a store relies on unusual OCMOD edge cases, validate against the exact OpenCart fork before production auto-deploy.

## 6. Cache profiles

Cache profiles are explicit server-side allowlists.

Example project rules:

```text
TPL/Twig → journal_templates
CSS/JS   → journal_assets
PHP      → no cache action by default
OCMOD    → ocmod_refresh
```

Journal3 paths differ by installation/version. Inspect the real project before configuring them.

## 7. Development branch vs production

Recommended:

```text
dev/<feature> → staging watcher, optional auto-deploy
main          → production, manual trigger by default
vX.Y.Z        → released production artifact/deployment
```

Use separate project JSON files and separate bridge secrets for staging and production.

## Known scope of the starter

The local starter supports FTP and FTPS with Python's standard library. Add an SFTP transport adapter (for example Paramiko) when the server supports SSH/SFTP; SFTP is preferable to plain FTP.

The included OCMOD refresh adapter targets OpenCart 2.3-style modification XML/lifecycle. OpenCart 3/4 should use their own tested adapters instead of silently reusing 2.3 behavior.

Automatic file restore/OCMOD rollback is not yet exposed as a one-command CLI operation in this starter. The agent records pre-overwrite backups and deployment metadata so a later rollback command can be added without redesigning the state format.
