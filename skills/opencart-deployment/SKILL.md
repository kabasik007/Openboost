---
name: opencart-deployment
description: Architecture and safety rules for Git-to-OpenCart deployment, OCMOD update/refresh, cache handling, rollback and health checks. Openboost main carries knowledge, not an experimental deploy runtime.
---

# OpenCart Deployment Architecture

Use this skill when a task involves automatic or semi-automatic deployment from Git/GitHub to an OpenCart server.

This skill defines **contracts, safety rules and architecture**. It does not mean Openboost `main` ships a production-ready deploy agent or privileged server bridge.

Read `docs/OPENBOOST_REPOSITORY_BOUNDARY.md` before promoting any runtime deployment tool into Openboost itself.

## Core deployment model

A safe deployment system should conceptually separate:

```text
Git branch / release
        ↓
exact target commit
        ↓
changed-file calculation
        ↓
backup / rollback metadata
        ↓
controlled file transfer
        ↓
OpenCart-aware privileged actions when required
        ↓
OCMOD update + version-specific refresh
        ↓
explicit cache invalidation
        ↓
health check
        ↓
record deployed SHA
```

The concrete implementation belongs in the target project, a dedicated tooling repository, or an explicitly experimental branch unless the user deliberately promotes it.

## Branch policy

Recommended default:

```text
dev/feature branch → staging, optionally auto-deploy
main                → production, manual approval by default
release tag         → production release deployment
```

Do not auto-deploy arbitrary branches to production.

Deploy an exact commit SHA whenever practical so the deployed state is auditable.

## Incremental deployment

Prefer an incremental diff over blindly re-uploading the entire extension/site when a reliable previous deployed SHA exists.

Conceptually:

```text
git diff --name-status OLD_SHA..NEW_SHA
```

Handle intentionally:

- `A` / `M`: upload changed file;
- `D`: delete remotely only when explicitly allowed;
- `R`: treat as controlled remove-old + upload-new;
- files outside deployable project boundaries: reject/ignore according to project policy.

For standard OpenCart extension packages, repository `upload/` is a staging tree: the **contents** usually map onto the OpenCart root. Confirm the target project's packaging convention before using that rule.

Installer/OCMOD XML should follow the target OpenCart install/update lifecycle rather than being blindly copied into the public site root.

## OCMOD identity and release versions

OCMOD ownership should use a **stable canonical `<code>`**.

Good:

```xml
<code>sitezilla_promo_hub</code>
<version>1.5.14</version>
```

Bad:

```xml
<code>sitezilla_promo_hub_1_5_14</code>
```

The ownership identity belongs in `<code>`.

The release identity belongs in:

- `<version>`;
- project/module version source of truth;
- package filename;
- changelog;
- Git tag / GitHub Release.

For legacy modules that historically created one OCMOD row per version:

1. establish one canonical code;
2. verify which historical codes are truly owned by the same module;
3. update/insert the canonical modification first;
4. remove only explicit owned legacy codes/patterns;
5. perform one normal full refresh;
6. verify the generated injection exists once and stale owned copies are gone.

Never fuzzy-delete modification rows by generic words such as `promo`, `filter`, `checkout`, or `module`.

## OCMOD database update contract

When the target OpenCart generation stores modification XML in a database table, a deployment/update mechanism may upsert by canonical code.

Required behavior:

- validate/parse XML before storage;
- read and preserve appropriate metadata;
- normalize to the intended canonical code only when migration policy explicitly requires it;
- use `DB_PREFIX`;
- preserve unrelated modifications;
- update canonical row when it exists, otherwise insert;
- remove only explicitly owned legacy rows;
- keep DB update and modification refresh as separate verifiable steps;
- record enough audit information to diagnose what changed.

Do not expose arbitrary remote SQL merely to automate deployment.

## OpenCart 2.3 OCMOD refresh

For OpenCart 2.3, modification refresh is a **full generated-tree rebuild**, not deletion of one generated PHP file.

The effective refresh lifecycle rebuilds runtime modification output from enabled sources such as:

```text
system/modification.xml
system/*.ocmod.xml
enabled DB modifications
```

Therefore:

- finish the complete owned OCMOD update set first;
- perform one full refresh/rebuild;
- do not leave an empty/partially rebuilt modification tree;
- inspect modification logs/warnings;
- verify the expected generated injection exists once;
- verify stale owned injections are gone.

When supporting several OpenCart generations, use version-specific adapters/contracts instead of one hard-coded route/token assumption.

## Cache policy

Do not make `delete every cache directory` the default.

Derive cache invalidation from the actual change and target project:

```text
OCMOD XML changed
→ OCMOD update + full refresh
→ theme/runtime cache only if target stack requires it

TPL/Twig changed
→ relevant template/theme cache

CSS/JS changed
→ asset cache only where the target stack actually caches server-side assets

PHP changed
→ normally file update only
→ optional OPcache reset only when explicitly supported/required

language file changed
→ normally file update only
```

Journal3/custom theme cache paths differ by project and version. Detect them; do not hard-code one universal directory.

## Backup and rollback

Before destructive/overwriting deployment steps, capture enough state to restore the previous known-good version.

Deployment metadata should normally include:

- old SHA;
- new SHA;
- branch/tag;
- changed/uploaded/deleted files;
- module/OCMOD code and version;
- database/modification actions;
- cache actions;
- backup location/identifier;
- health-check result;
- timestamp.

Rollback should restore owned files and owned OCMOD state, then run the normal version-appropriate refresh path.

Never implement rollback by deleting unrelated modifications or broad cache/data sets.

## Privileged deployment actions

Some deployment actions may need to execute inside the server/OpenCart environment rather than from a developer workstation.

If a project uses a privileged bridge/service, require at minimum:

- HTTPS in production;
- strong authentication/signatures;
- timestamp freshness;
- replay protection/nonces where applicable;
- constant-time signature comparison;
- strict action allowlist;
- strict path/code ownership boundaries;
- request size limits;
- no arbitrary shell execution;
- no arbitrary SQL endpoint;
- secrets outside Git;
- audit logging;
- explicit target-version behavior.

Treat such a bridge as privileged infrastructure, not a casual helper script.

## Health checks and deployment state

A deployment should not be considered successful merely because files transferred.

Verify an appropriate subset of:

- expected HTTP route responds;
- admin/catalog boot without fatal error;
- changed module route works;
- OCMOD refresh completed;
- generated injection exists once;
- critical DB migration/update succeeded;
- required assets/templates render;
- expected version is visible/readable.

Advance `last_deployed_sha` or equivalent state only after the required success gates pass.

## CI and production gates

Before production deployment, verify as applicable:

- target branch/tag is allowed;
- exact target SHA is known;
- required CI passed;
- working tree/build artifact is clean/reproducible;
- changed paths stay within expected boundaries;
- backup/rollback prerequisites exist;
- destructive deletes are explicit;
- OCMOD ownership is known;
- credentials/secrets are not in repository content;
- health check is configured.

## Openboost repository boundary

Openboost should learn from deployment experiments without automatically absorbing their runtime implementation.

Default workflow:

```text
experimental deploy branch / target project
        ↓
prove behavior
        ↓
extract reusable OpenCart rules
        ↓
update this skill / OCMOD skill / Git workflow docs
        ↓
leave Python/PHP/daemon/bridge implementation in its own branch or repository
```

Only move operational deployment code into Openboost `main` when the user explicitly wants Openboost to ship and maintain it and the promotion gates in `docs/OPENBOOST_REPOSITORY_BOUNDARY.md` are satisfied.
