# AGENTS.md — Openboost OpenCart AI Development Rules

This file is the primary instruction entry point for AI coding agents using Openboost.

Openboost is **not an application repository**. It is a reusable OpenCart development bootstrap/knowledge base that should be supplied alongside the real target project.

## One-link mode

The user should be able to provide this repository plus an OpenCart task without explaining OpenCart architecture every time.

When Openboost is present:

1. Read this file.
2. Read `AI_BOOTSTRAP.md`.
3. Read `docs/OPENBOOST_REPOSITORY_BOUNDARY.md` before moving implementation/tooling code into Openboost itself.
4. Load task-relevant skills from `skills/README.md` automatically.
5. Inspect the **target OpenCart project**, not only Openboost.
6. Search for an existing project architecture document and validate it; create `docs/ARCHITECTURE.md` when a non-trivial target project has no suitable equivalent.
7. Tell the user briefly what you are about to analyze before implementation.
8. Find existing functionality before creating anything new.
9. Use the architecture document as a navigation map, but verify task-relevant facts against code.
10. Implement only after the real integration path is understood.
11. Update architecture documentation when the task changes project structure or ownership boundaries.

Do not ask the user which Openboost skill to use.

## Openboost repository boundary — mandatory

Treat Openboost `main` as a **bootstrap/knowledge product**, not a catch-all tooling repository.

Before bringing changes from another branch/project into Openboost, classify them:

```text
A. reusable bootstrap knowledge
   → rules
   → architecture patterns
   → compatibility findings
   → checklists
   → documentation/templates that describe contracts
   → safe to port through a clean branch based on current main

B. experimental/runtime implementation
   → Python/PHP/Node agents or daemons
   → FTP/SFTP watchers
   → privileged server bridges/endpoints
   → live DB/deployment tooling
   → compiled executables/service wrappers
   → project-specific CI/deploy automation
   → keep in the target/tooling/experimental branch by default
```

**Do not merge a long-running experimental branch wholesale merely to obtain useful documentation or lessons.**

Preferred learning flow:

```text
real project / experimental branch
        ↓
verify what actually worked
        ↓
extract reusable rule / hazard / contract
        ↓
create a clean Openboost branch from current main
        ↓
port only the reusable bootstrap knowledge
        ↓
leave operational runtime code in its own branch/repository
```

Runtime tooling may enter Openboost `main` only when the user explicitly decides Openboost should ship and maintain it and the promotion gate in `docs/OPENBOOST_REPOSITORY_BOUNDARY.md` has been evaluated.

An active experiment branch may intentionally remain unmerged. Do not delete or merge it simply to make the branch list tidy.

Published tags/releases are immutable history; correct mistakes with a new version rather than rewriting old tags.

## Default compatibility policy

For new OpenCart development through Openboost:

- **PHP 7.1+ is the default minimum.**
- PHP 5.6 is legacy support and must be explicitly required by the task/project.
- Never assume the exact OpenCart version; detect it from repository evidence.
- Never assume TPL/Twig; detect the active template engine from the target project.
- Never assume language folder codes; inspect the project's actual languages.
- Frontend module UI is mobile-first by default.
- Meaningful module colors/buttons should use centralized theme tokens/presets rather than scattered hard-coded styles.

If the target project proves incompatible with the baseline, report the conflict instead of silently guessing.

## Core rule

**Do not rewrite, duplicate, or replace functionality before checking whether it already exists.**

Repository code/configuration is the source of truth.

Before implementation:

1. inspect the relevant OpenCart structure;
2. search for existing routes/controllers/models/services/settings/tables/templates/language keys;
3. inspect OCMOD/events that may alter the same runtime path;
4. identify active languages/theme/template engine;
5. identify existing Core/Services/shared abstractions;
6. read and validate the target architecture document when present;
7. identify the smallest coherent extension point;
8. preserve compatibility and existing data unless the requested change intentionally breaks them.

## Mandatory OpenCart skill routing

Read `skills/README.md` and load relevant skill files.

At minimum:

- any existing-project analysis/debugging → `skills/opencart-project-analysis/SKILL.md`;
- substantial existing project / architecture orientation → `skills/opencart-architecture-map/SKILL.md`;
- module creation/structural changes → `skills/opencart-module-development/SKILL.md`;
- visible frontend/admin UI, buttons/colors/themes/responsive work → `skills/opencart-ui-ux/SKILL.md`;
- translations/multilingual data → `skills/opencart-i18n/SKILL.md`;
- OCMOD/modification work → `skills/opencart-ocmod/SKILL.md`;
- Git-to-server/deployment lifecycle → `skills/opencart-deployment/SKILL.md`;
- GitHub branches/PR/version/release work → `skills/git-github-workflow/SKILL.md`.

A task can require more than one skill.

## Mandatory opening behavior for non-trivial work

Before editing, give the user a concise statement of what is being analyzed.

For OpenCart, it should normally cover the relevant subset of:

- OpenCart version;
- PHP target;
- `admin/catalog/system` structure;
- existing architecture document and whether it is current;
- route/controller/model/service path;
- Core/Services/shared libraries;
- languages and `language_id` behavior;
- template/theme engine;
- existing module implementation;
- OCMOD/events;
- database/install/update lifecycle.

Do not pretend this inspection has already happened when it has not.

## OpenCart repository discovery

For a non-trivial task, inspect as applicable:

```text
index.php
admin/index.php
config.php
admin/config.php
admin/controller/
admin/model/
admin/language/
admin/view/
catalog/controller/
catalog/model/
catalog/language/
catalog/view/
system/library/
system/engine/
system/config/
*.ocmod.xml
install.xml
docs/ARCHITECTURE.md
ARCHITECTURE.md
```

Also locate:

- active theme/Journal3/custom theme integration;
- SEO URL extensions;
- custom search/filter/checkout modules;
- cron/events;
- install/update/uninstall code;
- schema/migrations;
- generated/runtime modification output when available.

## Living project architecture document

For substantial target projects, maintain one authoritative architecture document.

Preferred path:

`docs/ARCHITECTURE.md`

Before creating it, search for an existing equivalent and update that instead of duplicating documentation.

`PROJECT_MAP.md` and `ARCHITECTURE.md` have different jobs:

```text
PROJECT_MAP.md
→ quick project passport: versions, languages, theme, important paths, commands

ARCHITECTURE.md
→ relationships and navigation: Core, Services, modules, flows, data ownership,
  OCMOD/events, integrations, cron, UI layers, and where to look first
```

The architecture document should normally map:

- major directories/layers;
- Core/shared libraries;
- Services and their responsibilities;
- controllers/models/repositories/adapters;
- important modules/subsystems;
- runtime flows;
- tables/settings ownership;
- languages/views/theme integration;
- events/OCMOD interception points;
- cron/background jobs;
- external integrations;
- UI/theme architecture;
- security/permissions boundaries;
- a practical `Where to look first` index;
- architectural hazards and existing abstractions that must not be duplicated.

Use `skills/opencart-architecture-map/SKILL.md` and `templates/ARCHITECTURE.md`.

Read architecture early to accelerate work, but **verify task-relevant sections against current code before relying on them**. Code/config/runtime evidence wins when docs are stale.

Update architecture documentation in the same task when structural boundaries, services, tables, hooks, integrations, jobs, or shared UI architecture materially change.

## Architecture standard

Prefer:

```text
thin OpenCart controller/integration layer
        ↓
module Core / Services / model layer
        ↓
data / OpenCart APIs / integrations
        ↓
view data
        ↓
template
```

For substantial modules, a module-owned `system/library/<module>/` layer is encouraged when it clarifies shared behavior.

Keep responsibilities readable and explicit.

Do not use obfuscation as an architecture pattern.

Do not invent `Core` or `Services` layers in documentation when the existing project does not have them; document reality first and keep desired refactors separate as target architecture/TODOs.

## OCMOD policy

OCMOD is not the default home for business logic.

Preferred integration order:

1. existing module/platform API;
2. OpenCart event;
3. module-owned adapter/wrapper;
4. thin OCMOD patch;
5. direct core edit only for an intentional maintained fork.

When OCMOD is used:

- inspect all modifications touching the target file;
- verify the exact search anchor exists;
- keep injected code small;
- delegate to module-owned code;
- inspect generated runtime modification output;
- test refresh/update/conflict behavior;
- reflect architecture-significant interception points in the target `ARCHITECTURE.md`;
- keep a stable canonical `<code>` across normal releases;
- keep release identity in `<version>`, package metadata and Git release history;
- remove old versioned codes only when ownership is explicit;
- for OpenCart 2.3, treat refresh as a full generated-tree rebuild rather than deletion of one generated file.

## Language policy

Treat interface translations and multilingual entity data separately.

Interface strings:

```text
admin/language/<actual-language>/...
catalog/language/<actual-language>/...
```

Entity/content translations:

```text
*_description tables keyed by language_id
```

Do not hard-code user-facing UA/RU strings in controllers/models/templates/JS when a language mechanism should be used.

Do not assume Ukrainian/Russian folder aliases. Detect them from the target project.

## Database and migrations

Before changing persistence:

- inspect existing schema/install/upgrade code;
- search every read/write of the affected entity;
- inspect `language_id`, `store_id`, indexes, and relations;
- use `DB_PREFIX`;
- cast IDs and escape strings;
- prefer explicit, idempotent migration/update logic;
- preserve data during upgrades;
- update architecture data-ownership sections for material schema/ownership changes.

Do not make destructive reset/reinstall the normal update strategy.

Uninstall should not automatically mean purge unless product requirements say so.

## Permissions and lifecycle

For new admin routes, inspect/add access and modify permissions deliberately.

For events:

- use stable module-prefixed codes;
- avoid duplicates;
- remove only owned events on uninstall/update.

For install/update:

- permissions;
- events;
- schema/migrations;
- defaults;
- OCMOD state;

are separate concerns and should be verifiable independently.

## Template, UI and theme policy

Use the target project's real view system.

Do not create both TPL and Twig versions unless compatibility requirements justify both.

Before frontend/admin UI work, inspect:

- active theme;
- default theme fallback;
- Journal3/custom controller/model overrides;
- existing CSS/JS conventions;
- existing design tokens/components;
- OCMOD template injections.

Frontend custom module UI is mobile-first. Admin module screens should be modern, responsive, and usable on narrow widths where practical.

When a module exposes meaningful colors/buttons/cards/badges or other visual surfaces, prefer:

```text
theme preset(s)
+ Custom theme
+ centralized design tokens / CSS variables
```

Do not scatter hard-coded color values throughout templates and JavaScript.

## Existing implementation first

Before creating a new module, field, table, route, helper, service, or JavaScript component, search for:

- feature/product terminology;
- likely route/class/function names;
- existing Core/Services abstractions;
- language keys;
- config keys;
- table/column names;
- OCMOD codes;
- disabled/legacy versions;
- theme compatibility code.

Extend existing abstractions when reasonable.

Use the architecture document's `Where to look first` and `do-not-duplicate` sections as shortcuts, then confirm against code.

## Questions policy

Do not ask questions the target repository can answer.

Ask only when a material decision cannot be established, such as:

- truly unknown compatibility target;
- destructive data behavior;
- external credentials/provider choice;
- multiple product behaviors with no repository evidence.

If a safe narrow implementation can proceed, do it and document the assumption instead of blocking.

## Validation

Before completion, verify the relevant subset of:

- PHP syntax under the declared minimum;
- admin route/access/modify permissions;
- install on clean schema;
- update/migration from supported prior state;
- no duplicated events/settings/modifications;
- active language files and translated DB data;
- catalog render in active theme;
- responsive/mobile behavior for visible UI changes;
- theme preset/custom-theme behavior when applicable;
- AJAX response behavior;
- OCMOD XML parse + refresh + generated patch;
- totals/pagination/cache when product queries change;
- multistore behavior;
- uninstall/disable behavior;
- architecture document reflects structural changes;
- final diff contains no unrelated edits.

State what could not be verified.

## Living knowledge rule

Openboost must improve as OpenCart work continues.

Read `docs/OPEN_CART_LIVING_KNOWLEDGE.md`.

After substantial OpenCart work, determine whether a newly discovered pattern is reusable. If it is:

- update the narrow relevant skill/reference when Openboost is writable;
- otherwise include the proposed Openboost update in the handoff.

Do not turn one site's custom hack into a universal rule.

Project-specific architecture belongs in the target project's `docs/ARCHITECTURE.md`; reusable architectural rules belong in Openboost.

When the reusable lesson came from experimental runtime code, port the lesson through a clean Openboost branch; do not automatically port the runtime implementation.

## Golden reference

The initial architecture reference is derived from user-provided OCFilter 4.8.2 and documented at:

`skills/references/ocfilter-4.8.2-architecture.md`

Adopt its strong architectural patterns, especially separation, migrations, language-aware data, permissions/events, and thin OCMOD integration.

Do **not** blindly copy its obfuscation, legacy PHP compatibility, giant controllers, broad third-party patches, or storage assumptions.

## Definition of done

A change is done only when:

- target project was analyzed first;
- relevant Openboost skills were loaded;
- existing architecture documentation was searched and task-relevant sections validated;
- a missing architecture document was created for a substantial undocumented project when appropriate;
- existing implementation/shared abstractions were searched;
- real OpenCart version/PHP/template/language constraints were established;
- duplicate functionality was avoided;
- change uses the real architecture/integration path;
- lifecycle/data/translation/OCMOD/UI impacts were handled where relevant;
- structural changes were reflected in the target architecture document;
- relevant validation was performed;
- reusable new OpenCart knowledge was captured or proposed;
- Openboost repository-boundary rules were respected when importing lessons/tooling from another branch/project;
- unresolved risks are explicit.
