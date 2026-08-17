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

Set the bridge secret in the local environment instead of Git:

```powershell
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
- allowed module code prefixes;
- audit log location;
- cache profiles with project-specific paths;
- OCMOD refresh adapter path.

For production:

- HTTPS is required;
- additionally restrict the bridge by IP/web-server policy where possible;
- do not expose DB port 3306;
- do not store the bridge secret in the Git repository;
- do not enable arbitrary command execution;
- keep OPcache reset disabled unless actually required.

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

Before overwrite/delete the agent tries to download the current remote file into `.deploy-backups/`.

Remote deletion is disabled by default. Enable `allow_remote_delete` only after confirming the project expects Git deletions to remove production files.

The state SHA is advanced only after upload, bridge actions and health check succeed.

## 5. Installer XML

Installer XML paths are configurable. They are detected separately from deployable web files.

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
