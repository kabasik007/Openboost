---
name: opencart-deployment
description: Safe Git-to-OpenCart deployment workflow using a local watcher/uploader plus a server-side PHP bridge for OCMOD update, refresh, cache handling, health checks, backups and rollback.
---

# OpenCart Deployment Bridge

Use this skill when a task involves automatic or semi-automatic deployment from Git/GitHub to an OpenCart server.

## Core architecture

Preferred flow:

```text
GitHub branch / main / release
        ↓
local deploy agent
        ↓
git fetch + commit diff
        ↓
backup changed remote files
        ↓
upload contents of repository upload/ to OpenCart root
        ↓
server bridge for privileged OpenCart actions
        ↓
OCMOD upsert by stable code
        ↓
full OCMOD rebuild when required
        ↓
selective configured cache clear
        ↓
health check
        ↓
store deployed commit SHA
```

Do not map repository `upload/` to a remote `/upload/` directory. The *contents* of `upload/` map onto the OpenCart site root.

## Branch policy

Recommended default:

```text
dev/feature branch → staging, optionally auto-deploy
main              → production, manual approval by default
release tag        → production release deployment
```

Do not auto-deploy arbitrary branches to production.

## Incremental deployment

Do not re-upload the whole module when a commit diff is available.

Use the last successfully deployed SHA and the target SHA:

```text
git diff --name-status OLD_SHA..NEW_SHA
```

Handle:

- `A` / `M`: upload changed file;
- `D`: delete remote file only when the project explicitly allows remote deletion;
- `R`: treat as delete old + upload new, subject to delete policy.

Only paths under the configured `upload/` tree become remote web files.

Installer/OCMOD XML is handled separately and should not be blindly uploaded to the public site root.

## OCMOD identity and updates

OCMOD ownership must use a **stable canonical `<code>`**.

Good:

```xml
<code>sitezilla_promo_hub</code>
<version>1.5.14</version>
```

Bad:

```xml
<code>sitezilla_promo_hub_1_5_14</code>
```

Version belongs in `<version>`, release metadata, package filename and Git tag — not in the ownership code.

For legacy modules that already changed code per version:

1. define one canonical code;
2. explicitly configure legacy code regexes/prefixes owned by that module;
3. update or insert the canonical row first;
4. delete only matching legacy rows owned by that module;
5. perform one full modification refresh;
6. verify the generated injection exists once and stale copies are gone.

Never delete modifications using a vague generic substring.

## OCMOD database upsert

For OpenCart versions where installer XML is stored in the modification table, the server-side bridge may upsert by canonical code.

Required behavior:

- parse XML with DOM;
- reject malformed XML;
- read `name`, `code`, `version`, `author`, `link` where available;
- normalize the XML `<code>` to the configured canonical code before storing;
- preserve status where appropriate, or use an explicit configured status;
- use `DB_PREFIX`;
- update the existing canonical row, otherwise insert;
- remove only explicitly configured legacy codes;
- record a deployment audit entry/log when possible.

The database update and the modification refresh are separate steps.

## OCMOD refresh

For OpenCart 2.3, modification refresh is a **full generated-tree rebuild**, not a single-file cache delete. The core refresh process clears `DIR_MODIFICATION`, loads the base modification XML, module `system/*.ocmod.xml` files and enabled DB modifications, then rebuilds generated files.

Therefore:

- do not delete only one generated file and assume OCMOD is refreshed;
- do not leave an empty modification tree after clearing it;
- prefer the platform refresh implementation or a version-specific adapter reproducing the same lifecycle;
- refresh once after the complete set of OCMOD database changes;
- preserve `index.html`/required placeholders where the target version does so;
- capture modification errors/log output.

When supporting multiple OpenCart generations, use separate adapters rather than one hard-coded route/token assumption.

## Local agent responsibilities

The local agent should normally handle:

- repository path and monitored branch;
- `git fetch` and remote SHA comparison;
- optional CI-success gate;
- `git pull --ff-only` or detached checkout of the exact SHA;
- changed-file calculation;
- dry-run mode;
- backup of remote files that will be overwritten/deleted;
- FTP/FTPS/SFTP upload transport;
- optional controlled remote deletion;
- installer XML discovery;
- signed calls to the server bridge;
- site health check;
- persistent deployment state;
- rollback metadata/logging.

VS Code is optional. The agent should run independently from the editor, for example from Windows Task Scheduler, startup, a console process or a packaged executable.

## Server bridge responsibilities

The bridge should handle operations that are safer on the server than from a remote SQL connection:

- read local OpenCart configuration/DB credentials;
- OCMOD canonical-code upsert;
- explicitly owned legacy OCMOD cleanup;
- OpenCart-version-specific OCMOD refresh;
- configured cache clears;
- optional OPcache reset when explicitly enabled;
- health/status endpoint;
- deployment audit log.

Do not expose MySQL port 3306 merely for deployment.

## Authentication

A deployment bridge is privileged infrastructure.

Minimum requirements:

- HTTPS only in production;
- long random shared secret or stronger key mechanism;
- HMAC signature over timestamp + nonce + request body;
- reject stale timestamps;
- reject replayed nonces for the accepted time window where practical;
- constant-time signature comparison;
- request size limits;
- action allowlist;
- path allowlist for cache operations;
- never accept arbitrary SQL or arbitrary shell commands;
- keep the bridge outside predictable public paths where practical, or restrict it by web-server/IP policy in addition to HMAC.

Secrets belong in local environment/config ignored by Git, never committed in project YAML examples.

## Cache policy

Do not use `delete every cache directory` as the default.

Choose cache actions from the actual diff:

```text
OCMOD XML changed
→ OCMOD upsert + full OCMOD refresh
→ configured theme/runtime cache clear if required

TPL/Twig changed
→ template/theme cache clear

CSS/JS changed
→ asset/theme cache clear only when the deployment stack actually caches them server-side

PHP controller/model changed
→ normally upload only
→ optional OPcache reset when the host requires it

language file changed
→ normally upload only
```

Journal3 cache locations and behavior differ by project/version. Detect and configure them; do not hard-code one universal directory.

## Backup and rollback

Before overwriting/deleting remote files, store previous copies locally when practical:

```text
.deploy-backups/YYYYMMDD-HHMMSS/<relative-path>
```

Deployment metadata should include:

- old SHA;
- new SHA;
- branch;
- uploaded files;
- deleted files;
- installer XML code/version;
- bridge actions;
- health-check result;
- timestamp.

A rollback must restore owned files and previous owned OCMOD XML, then perform a normal full refresh. Never roll back by deleting unrelated modification/cache data.

## Safety gates

Before production deploy:

- target branch is allowed;
- working tree is clean or exact-SHA deployment is used;
- target commit passed required CI when a CI gate is configured;
- changed paths stay inside allowed project boundaries;
- remote destination is the configured site root;
- backup step succeeded or the project explicitly waived it;
- installer XML ownership is known;
- destructive deletes are explicit;
- health check is configured.

If any deployment step fails, do not advance `last_deployed_sha`.

## Reusable templates

Openboost ships reusable starting points under:

```text
templates/opencart-deploy/
  local_agent.py
  project.example.json
  server_bridge.php
  README.md
```

They are scaffolds. Before deploying a real store, adapt paths, OpenCart version, OCMOD refresh adapter, Journal/cache paths, transport and credentials to the target project.
