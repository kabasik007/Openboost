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
12. use deployment knowledge when Git-to-server, OCMOD refresh, cache or rollback behavior matters;
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
Experimental runtime tooling: keep out of Openboost main unless explicitly promoted
```

OpenCart 2.3-style module architecture remains a first-class target, but Openboost must not assume every project is 2.3.

## Repository boundary

Openboost `main` is primarily:

```text
instructions
+ OpenCart knowledge
+ skills
+ architecture/reference docs
+ lightweight documentation templates
```

It is **not** the default home for experimental operational tooling.

A Python deploy agent, PHP server bridge, daemon/watcher, live DB bridge or similar runtime implementation should stay in the target project, a dedicated tooling repository, or an experimental branch until the user deliberately decides Openboost should ship and maintain it.

When an experiment teaches something reusable:

```text
experimental branch
      ↓
extract proven rules / hazards / architecture
      ↓
update Openboost skills/docs from a clean branch based on main
      ↓
leave runtime implementation in the experiment branch
```

Read `docs/OPENBOOST_REPOSITORY_BOUNDARY.md` for the full rule and promotion gate.

## Skills

- `skills/opencart-project-analysis/SKILL.md` — where to look and how to trace an existing OpenCart project.
- `skills/opencart-architecture-map/SKILL.md` — living `ARCHITECTURE.md`, Core/Services/module/data ownership and runtime navigation map.
- `skills/opencart-module-development/SKILL.md` — module architecture, install/update/uninstall, migrations, permissions/events, services/models/views.
- `skills/opencart-ui-ux/SKILL.md` — mobile-first frontend, modern admin UI, theme presets/custom themes and design tokens.
- `skills/opencart-i18n/SKILL.md` — admin/catalog language files, UA/RU, `language_id`, multilingual entity data and SEO.
- `skills/opencart-ocmod/SKILL.md` — safe OCMOD design, refresh, runtime debugging and conflicts.
- `skills/opencart-deployment/SKILL.md` — deployment architecture/safety, canonical OCMOD updates, version-specific refresh, cache/rollback/health-check rules.
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

## Deployment knowledge

Openboost keeps the reusable deployment rules even when the concrete prototype implementation stays experimental.

Important rules include:

- deploy exact/traceable commits when practical;
- prefer incremental and reversible updates;
- back up before destructive changes;
- OCMOD ownership `<code>` remains stable across versions;
- release version belongs in `<version>` / package / Git tag;
- legacy versioned OCMOD rows are removed only through explicit owned patterns;
- OpenCart 2.3 OCMOD refresh is a full generated-tree rebuild, not deletion of one generated file;
- cache invalidation is project/version-specific and explicit;
- privileged deployment endpoints require strong authentication, replay protection, allowlists and auditability;
- health checks must pass before recording a deployment as successful.

The concrete runtime implementation may remain in an experimental branch while these proven rules live in the Bootstrap.

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
= instructions + OpenCart knowledge + reusable development rules

Your store/module/tooling repository
= code that should actually be analyzed, executed and changed
```

Do not implement a customer's store feature or an experimental operational service inside Openboost `main` unless that scope is explicitly promoted.
