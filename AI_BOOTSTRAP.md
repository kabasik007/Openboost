# AI_BOOTSTRAP.md — OpenCart Session Bootstrap

Use this file when Openboost is supplied to an AI agent together with a real OpenCart project/task.

The user should not have to explain where OpenCart controllers, languages, models, services, OCMOD, translations, themes, or architecture live. Openboost provides that operating model; the agent must still inspect the real target repository before making assumptions.

## Phase 0 — Identify repositories and boundaries correctly

When both are available:

```text
Openboost = instructions / reusable OpenCart knowledge
Target repository = application/store/module/tooling to inspect and modify
```

Do not accidentally implement the requested store feature inside Openboost.

Read:

1. `AGENTS.md`
2. `docs/OPENBOOST_REPOSITORY_BOUNDARY.md`
3. `skills/README.md`
4. `skills/opencart-project-analysis/SKILL.md`
5. `skills/opencart-architecture-map/SKILL.md` for substantial target projects
6. other task-relevant skill files
7. relevant architecture references
8. `docs/OPEN_CART_LIVING_KNOWLEDGE.md`

Then inspect the target repository.

### Bootstrap knowledge vs runtime implementation

When another branch/project contains useful OpenCart work, classify it before merging anything into Openboost:

```text
reusable knowledge
→ architecture rule
→ compatibility finding
→ OCMOD/lifecycle rule
→ checklist / safe contract
→ port through a clean Openboost branch based on current main

experimental runtime
→ Python/PHP/Node agent
→ deploy watcher/daemon
→ privileged bridge/API
→ live DB/deployment implementation
→ project-specific CI/service code
→ leave in target/tooling/experimental branch unless explicitly promoted
```

Do **not** merge a long-running bot/experiment branch wholesale just because part of its documentation is useful.

An active experimental branch may remain unmerged by design. Only promote runtime tooling into Openboost `main` when the user explicitly wants Openboost to ship/maintain it and the promotion gates in `docs/OPENBOOST_REPOSITORY_BOUNDARY.md` are satisfied.

## Phase 1 — Tell the user what will be analyzed

For a non-trivial task, send a short concrete update before editing.

Default OpenCart wording can be adapted from:

> Спочатку аналізую сам OpenCart-проєкт: визначаю точну версію OpenCart і PHP, структуру admin/catalog/system, перевіряю наявну карту архітектури, Core/Services і спільні абстракції, активні мови та спосіб перекладів, тему/TPL/Twig, існуючий модуль або реалізацію, OCMOD/events і таблиці/міграції. Якщо документа архітектури немає — створю його по фактичному коду. Після цього визначу найменшу правильну точку зміни й тільки тоді редагуватиму код.

This is not ceremonial. Perform the inspection described.

## Phase 2 — Confirm platform truth

Determine from evidence:

```text
OpenCart version
PHP runtime/minimum
admin route conventions
catalog route conventions
token vs user_token
template engine(s)
active/custom theme
language folder codes
language_id strategy
multistore usage
OCMOD source + generated runtime state
events
custom DB tables/settings
install/update/uninstall lifecycle
cron/jobs
shared Core/Services/libraries
existing architecture documentation
```

Use `skills/opencart-project-analysis/SKILL.md`.

### PHP policy

Openboost default for new code is PHP 7.1+.

If target evidence says:

- PHP >= 7.1 → continue within confirmed minimum;
- PHP >= 7.4/8.x → higher syntax may be allowed only if project compatibility confirms it;
- PHP 5.6 → treat as a legacy compatibility conflict; do not silently write 7.1-only code and do not downgrade Openboost's general standard unless the task explicitly targets that legacy project.

## Phase 3 — Establish the living architecture map

Use `skills/opencart-architecture-map/SKILL.md`.

Search before creating documentation:

```text
docs/ARCHITECTURE.md
ARCHITECTURE.md
existing clearly equivalent architecture/system map docs
```

Behavior:

```text
existing equivalent found
→ read it
→ validate task-relevant sections against code
→ repair stale facts
→ use it as navigation

no suitable architecture document
→ inspect project structure and major modules
→ create docs/ARCHITECTURE.md
→ populate only evidence-backed architecture
→ deepen it as real work touches more subsystems
```

Do not create duplicate overlapping architecture documents.

The architecture map should make it fast to answer:

```text
Where is Core?
Where are Services?
Which controller owns this route?
Which service owns this behavior?
Which model/table owns this data?
Which module already does something similar?
Which event/OCMOD changes this runtime path?
Which theme/template renders it?
Where does translation live?
Which cron/integration calls it?
Where should a new feature be added without duplication?
```

### PROJECT_MAP vs ARCHITECTURE

If the target project has both:

```text
PROJECT_MAP.md
→ short project passport / facts / versions / paths

ARCHITECTURE.md
→ relationships / ownership / flows / Core / Services / modules / data / hooks
```

Do not fill both with the same content.

## Phase 4 — Find existing implementation

Search before creating:

- route/controller names;
- module codes;
- models/services/libraries;
- shared Core/Services abstractions;
- config keys;
- language keys;
- DB tables/columns;
- templates/JS/CSS;
- OCMOD codes/targets;
- events;
- old/disabled implementations;
- Journal3/theme/SEO compatibility code.

Use the architecture document's `Where to look first` section as a shortcut when available, then verify with repository search.

Trace the complete path, not only the first matching file.

For example:

```text
Admin UI
→ route/controller
→ access/modify permission
→ language route
→ service/model
→ DB/settings
→ template/assets
→ OCMOD/menu injection
```

or:

```text
Catalog request
→ route/controller
→ active theme
→ service
→ model/query
→ OCMOD/events
→ module core
→ template
→ JS/CSS
```

## Phase 5 — Load specialized skills

### Module work

Read:

`skills/opencart-module-development/SKILL.md`

before creating a module or changing its architecture/install/update lifecycle.

If the change introduces/moves Core, Services, tables, adapters, hooks, cron, or subsystem boundaries, update the target architecture document in the same task.

### UI/theme work

Read:

`skills/opencart-ui-ux/SKILL.md`

for visible frontend/admin UI, buttons, colors, theme presets, custom themes, responsive/mobile-first work, tables, forms, modals, drawers, or meaningful module styling.

### Translation work

Read:

`skills/opencart-i18n/SKILL.md`

before changing UI text, language tabs, description tables, `language_id`, or multilingual SEO.

### OCMOD work

Read:

`skills/opencart-ocmod/SKILL.md`

before creating/editing modification XML or debugging runtime modified files.

For OCMOD deployment/update lifecycle, also read `skills/opencart-deployment/SKILL.md`.

### Git/GitHub work

Read:

`skills/git-github-workflow/SKILL.md`

for branch/PR/version/changelog/tag/release work. Do not merge active experimental branches simply to clean up branch lists.

## Phase 6 — Produce the implementation map

Before substantial code changes, establish internally:

```text
Current behavior:
Existing implementation:
Architecture document status:
Relevant Core/Services:
OpenCart/PHP constraints:
Affected admin files:
Affected catalog files:
Affected system/library files:
Languages affected:
DB/settings affected:
OCMOD/events affected:
Theme/Journal integration affected:
Install/update/uninstall impact:
Smallest coherent change:
Verification path:
```

If the target project maintains `docs/PROJECT_MAP.md`, update it with confirmed compact project facts.

If architecture-significant facts are learned, update `docs/ARCHITECTURE.md` or the existing equivalent.

## Phase 7 — Implement by extension, not duplication

Preference order:

1. configure/reuse existing behavior;
2. fix existing behavior;
3. extend existing Core/service/controller/model;
4. add a narrow missing module layer;
5. create a new subsystem only when evidence shows one is needed.

For new module architecture, favor:

```text
thin OpenCart integration
→ module-owned Core / Services / model
→ DB/OpenCart APIs/adapters
→ view data
→ template
```

Do not create a second service/repository/helper when the architecture map and repository show an established abstraction already owns that responsibility.

## Phase 8 — Handle translations correctly

If any user-visible text changes:

- detect actual admin/catalog language folders;
- add keys to every required active language;
- avoid hard-coded UI strings;
- keep translated entity content in `language_id`-keyed data structures/tables when appropriate;
- verify new-language behavior if the module owns multilingual entities.

For UA/RU projects, verify both language packs explicitly.

## Phase 9 — Handle UI/theme architecture correctly

For visible module UI:

- frontend is mobile-first;
- admin UI should be modern and responsive where practical;
- reuse existing project components/frameworks;
- meaningful colors/buttons should use centralized theme tokens;
- support theme presets and usually `Custom` when the module has its own visual identity;
- avoid styling behavior based on color values;
- document architecture-significant shared UI/theme layers in `ARCHITECTURE.md`.

## Phase 10 — Handle OCMOD safely

If a core/third-party path must be intercepted:

1. check whether an event/API can do it;
2. inspect all modifications targeting the same file;
3. verify the exact search anchor;
4. keep XML injected logic thin;
5. delegate to module-owned methods;
6. keep a stable canonical `<code>` across normal releases and put release identity in `<version>`/package/tag;
7. refresh modifications using the actual target-version lifecycle;
8. for OpenCart 2.3, use a full generated-tree rebuild rather than deleting one generated file;
9. inspect generated runtime output;
10. verify legacy owned versioned modifications do not leave duplicate injections;
11. test conflict-sensitive routes;
12. record architecture-significant interception points in the project architecture document.

Do not assume original OpenCart source equals runtime source.

## Phase 11 — Handle install/update/uninstall as separate behavior

Verify as relevant:

```text
permissions
events
schema
migrations
default settings
OCMOD state
old-file cleanup
cron/startup hooks
uninstall disable/cleanup
```

Upgrade must preserve data by default.

Uninstall should not purge business data unless explicitly designed to do so.

Update architecture data ownership/lifecycle sections when these responsibilities materially change.

## Phase 12 — Verify

Run/perform the relevant subset:

- PHP syntax under minimum version;
- admin route + permissions;
- save/validation;
- active languages;
- catalog render;
- active theme/Journal integration;
- responsive/mobile behavior;
- theme preset/custom-theme behavior when applicable;
- AJAX/API responses;
- clean install;
- upgrade/migration;
- repeated install/check idempotency;
- OCMOD parse/refresh/generated result;
- query totals/pagination/cache consistency;
- multistore behavior;
- uninstall/disable path;
- architecture document still matches changed structure;
- final diff review.

State exactly what could not be executed.

## Phase 13 — Capture reusable OpenCart learning

At the end of substantial work, read `docs/OPEN_CART_LIVING_KNOWLEDGE.md` and determine whether Openboost should learn something new.

If Openboost is writable, update the correct skill/reference.

If it is not writable, include a short `Openboost knowledge update` recommendation in the handoff.

Project-specific architecture belongs in the target project's architecture document. Reusable architecture rules belong in Openboost.

If the lesson came from an experimental runtime implementation:

```text
extract the proven lesson
→ port only the reusable knowledge to a clean Openboost branch
→ keep the runtime implementation in its target/tooling/experiment branch
```

## Minimal user invocation

A user can simply send something like:

```text
Use https://github.com/kabasik007/Openboost

Ось OpenCart-проєкт / repo. Треба додати ...
```

The agent should automatically read Openboost, route to the correct skills, analyze the target project, create/validate the living architecture map, and continue without asking the user to restate standard OpenCart conventions.

## Handoff format

At completion, report concisely:

- what the target project actually uses;
- architecture document created/updated/validated;
- relevant Core/Services/subsystems found;
- what existing implementation was found;
- what changed;
- what was reused rather than rewritten;
- translations/UI/OCMOD/schema/lifecycle impact;
- GitHub branch/version/release impact when relevant;
- whether any experimental runtime work was intentionally kept out of Openboost `main`;
- verification performed;
- remaining risks;
- reusable Openboost knowledge added or proposed.
