# Openboost

**Openboost is a reusable AI bootstrap for OpenCart development.**

The goal is simple: give an AI agent this repository plus the real OpenCart project/task, and the agent should already know how to inspect OpenCart architecture, modules, languages, translations, OCMOD, UI/theme rules, Git/GitHub workflow, deployment lifecycle, install/update lifecycle, and where to look before changing code.

## Minimal usage

```text
Use https://github.com/kabasik007/Openboost

Ось OpenCart-проєкт. Треба зробити ...
```

The agent should then automatically:

1. read `AGENTS.md` and `AI_BOOTSTRAP.md`;
2. load relevant Openboost skills;
3. analyze the target project's real OpenCart/PHP version;
4. find/validate or create the living architecture map;
5. detect languages, template engine/theme, modules, OCMOD/events, DB/install lifecycle;
6. find the existing implementation first;
7. explain briefly what it is analyzing;
8. when the project is GitHub-backed, create/reuse a task branch instead of normally working directly on the default branch;
9. make the smallest correct change and verify it;
10. publish through intentional commits + PR when write access is available;
11. maintain version/changelog/tag/GitHub Release history when a release-worthy version is produced;
12. use the deployment skill when Git-to-server automation, OCMOD refresh, cache handling or rollback is part of the task;
13. capture reusable OpenCart knowledge back into Openboost when appropriate.

## Default compatibility and development policy

```text
PHP minimum for new development: 7.1+
PHP 5.6: legacy-only, explicit requirement
OpenCart version: always detect from target project
Template engine: always detect from target project
Languages: detect actual folder codes and language_id behavior
Frontend UI: mobile-first
Admin UI: modern + responsive where practical
Project architecture: living code-backed docs/ARCHITECTURE.md or existing equivalent
GitHub writes: task branch + PR by default
Versioning: Semantic Versioning by default when no stronger project policy exists
Release history: CHANGELOG + immutable vX.Y.Z tag + GitHub Release
Deployment: incremental, reversible and target-version-aware
```

OpenCart 2.3-style module architecture remains a first-class target, but Openboost must not assume every project is 2.3.

## Skills

- `skills/opencart-project-analysis/SKILL.md` — where to look and how to trace an existing OpenCart project.
- `skills/opencart-architecture-map/SKILL.md` — living `ARCHITECTURE.md`, Core/Services/module/data ownership and runtime navigation map.
- `skills/opencart-module-development/SKILL.md` — module architecture, install/update/uninstall, migrations, permissions/events, services/models/views.
- `skills/opencart-ui-ux/SKILL.md` — mobile-first frontend, modern admin UI, theme presets/custom themes and design tokens.
- `skills/opencart-i18n/SKILL.md` — admin/catalog language files, UA/RU, `language_id`, multilingual entity data and SEO.
- `skills/opencart-ocmod/SKILL.md` — safe OCMOD design, refresh, runtime debugging and conflicts.
- `skills/opencart-deployment/SKILL.md` — Git branch monitoring, incremental FTP/FTPS/SFTP deployment, canonical OCMOD updates, refresh, cache profiles, health checks and rollback.
- `skills/git-github-workflow/SKILL.md` — task branches, commits, PRs, SemVer, changelog, tags, GitHub Releases and release artifacts.

See `skills/README.md` for automatic routing rules.

## GitHub history and releases

When Openboost is used against a GitHub-backed target repository, normal development should follow:

```text
default branch
      ↓
task branch
      ↓
implementation + validation
      ↓
intentional commit(s)
      ↓
pull request
      ↓
merge
      ↓
release-worthy change?
      ↓ yes
VERSION / module version + CHANGELOG
      ↓
vX.Y.Z tag
      ↓
GitHub Release + installable artifacts
```

Do not overwrite published tags to hide mistakes. Fix the code and publish a new PATCH/MINOR/MAJOR version as appropriate.

Openboost itself has a root `VERSION`, `CHANGELOG.md`, release workflow and PR template so it can follow the same history rules it recommends.

## Local OpenCart deployment starter

Openboost now includes a reusable development-deployment scaffold under:

```text
templates/opencart-deploy/
```

The intended architecture is:

```text
GitHub branch / main / release
        ↓
local Python deploy agent
        ↓
git fetch + OLD_SHA..NEW_SHA diff
        ↓
backup changed remote files
        ↓
FTP/FTPS upload of upload/ CONTENTS to OpenCart root
        ↓
HMAC-signed PHP server bridge
        ↓
OCMOD upsert by stable canonical <code>
        ↓
OpenCart-version-specific full modification rebuild
        ↓
explicit Journal/theme/runtime cache profile(s)
        ↓
health check + deployed SHA
```

Important rules:

- repository `upload/` contents map to the OpenCart site root; do not blindly deploy an `/upload/` directory;
- OCMOD ownership code stays stable across versions — the version belongs in `<version>`/release metadata;
- legacy versioned modification codes may be cleaned only through explicit owned patterns;
- OpenCart 2.3 OCMOD refresh is a full generated-tree rebuild, not deletion of one modified PHP file;
- server-side cache clearing uses configured allowlisted profiles instead of arbitrary paths;
- production auto-deploy is not the default; a dev branch → staging watcher is the safer starting point;
- the bridge uses local OpenCart DB credentials and does not require exposing MySQL to the developer workstation.

Starter files:

- `templates/opencart-deploy/local_agent.py`
- `templates/opencart-deploy/project.example.json`
- `templates/opencart-deploy/server_bridge.php`
- `templates/opencart-deploy/oc23_refresh_adapter.php`
- `templates/opencart-deploy/README.md`

Treat these as reusable scaffolds. Detect the target site's real admin folder, storage paths, Journal version/cache layout, transport and OpenCart generation before using them.

## Golden architecture reference

The first reference analysis is based on a user-provided OCFilter 4.8.2 package. Its reusable architecture lessons are documented at:

`skills/references/ocfilter-4.8.2-architecture.md`

The third-party module itself is not committed here.

Openboost adopts the strong patterns from that module — separation, migrations, language-aware data, permissions/events, thin integrations — while intentionally avoiding obfuscation, PHP 5.x-era compatibility as a default, giant controllers, and unnecessary third-party patches.

## Living bootstrap

Openboost is expected to improve continuously during real OpenCart development.

Read:

`docs/OPEN_CART_LIVING_KNOWLEDGE.md`

Reusable discoveries should update the appropriate skill/reference. One-store hacks should stay in that store's own documentation.

## Important distinction

```text
Openboost
= instructions + OpenCart knowledge + reusable tooling scaffolds

Your store/module repository
= code that should actually be analyzed and changed
```

Do not implement a customer's store feature inside Openboost.
