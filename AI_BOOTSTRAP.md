# AI_BOOTSTRAP.md — OpenCart Session Bootstrap

Use this file when Openboost is supplied to an AI agent together with a real OpenCart project/task.

The user should not have to explain where OpenCart controllers, languages, models, OCMOD, or translations live. Openboost provides that operating model; the agent must still inspect the real target repository before making assumptions.

## Phase 0 — Identify the two repositories correctly

When both are available:

```text
Openboost = instructions / reusable OpenCart knowledge
Target repository = application/store/module to inspect and modify
```

Do not accidentally implement the requested store feature inside Openboost.

Read:

1. `AGENTS.md`
2. `skills/README.md`
3. task-relevant skill files
4. relevant architecture references
5. `docs/OPEN_CART_LIVING_KNOWLEDGE.md`

Then inspect the target repository.

## Phase 1 — Tell the user what will be analyzed

For a non-trivial task, send a short concrete update before editing.

Default OpenCart wording can be adapted from:

> Спочатку аналізую сам OpenCart-проєкт: визначаю точну версію OpenCart і PHP, структуру admin/catalog/system, активні мови та спосіб перекладів, тему/TPL/Twig, існуючий модуль або реалізацію, OCMOD/events і таблиці/міграції. Після цього визначу найменшу правильну точку зміни й тільки тоді редагуватиму код.

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
```

Use `skills/opencart-project-analysis/SKILL.md`.

### PHP policy

Openboost default for new code is PHP 7.1+.

If target evidence says:

- PHP >= 7.1 → continue within confirmed minimum;
- PHP >= 7.4/8.x → higher syntax may be allowed only if project compatibility confirms it;
- PHP 5.6 → treat as a legacy compatibility conflict; do not silently write 7.1-only code and do not downgrade Openboost's general standard unless the task explicitly targets that legacy project.

## Phase 3 — Find existing implementation

Search before creating:

- route/controller names;
- module codes;
- models/services/libraries;
- config keys;
- language keys;
- DB tables/columns;
- templates/JS/CSS;
- OCMOD codes/targets;
- events;
- old/disabled implementations;
- Journal3/theme/SEO compatibility code.

Trace the complete path, not only the first matching file.

For example:

```text
Admin UI
→ route/controller
→ access/modify permission
→ language route
→ model
→ DB/settings
→ template/assets
→ OCMOD/menu injection
```

or:

```text
Catalog request
→ route/controller
→ active theme
→ model/query
→ OCMOD/events
→ module core/service
→ template
→ JS/CSS
```

## Phase 4 — Load specialized skills

### Module work

Read:

`skills/opencart-module-development/SKILL.md`

before creating a module or changing its architecture/install/update lifecycle.

### Translation work

Read:

`skills/opencart-i18n/SKILL.md`

before changing UI text, language tabs, description tables, `language_id`, or multilingual SEO.

### OCMOD work

Read:

`skills/opencart-ocmod/SKILL.md`

before creating/editing modification XML or debugging runtime modified files.

## Phase 5 — Produce the implementation map

Before substantial code changes, establish internally:

```text
Current behavior:
Existing implementation:
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

If the target project maintains its own `docs/PROJECT_MAP.md`, update it with confirmed reusable facts.

## Phase 6 — Implement by extension, not duplication

Preference order:

1. configure/reuse existing behavior;
2. fix existing behavior;
3. extend existing controller/model/service;
4. add a narrow missing module layer;
5. create a new subsystem only when evidence shows one is needed.

For new module architecture, favor:

```text
thin OpenCart integration
→ module-owned model/service/library
→ DB/OpenCart APIs
→ view data
→ template
```

## Phase 7 — Handle translations correctly

If any user-visible text changes:

- detect actual admin/catalog language folders;
- add keys to every required active language;
- avoid hard-coded UI strings;
- keep translated entity content in `language_id`-keyed data structures/tables when appropriate;
- verify new-language behavior if the module owns multilingual entities.

For UA/RU projects, verify both language packs explicitly.

## Phase 8 — Handle OCMOD safely

If a core/third-party path must be intercepted:

1. check whether an event/API can do it;
2. inspect all modifications targeting the same file;
3. verify the exact search anchor;
4. keep XML injected logic thin;
5. delegate to module-owned methods;
6. refresh modifications;
7. inspect generated runtime output;
8. test conflict-sensitive routes.

Do not assume original OpenCart source equals runtime source.

## Phase 9 — Handle install/update/uninstall as separate behavior

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

## Phase 10 — Verify

Run/perform the relevant subset:

- PHP syntax under minimum version;
- admin route + permissions;
- save/validation;
- active languages;
- catalog render;
- active theme/Journal integration;
- AJAX/API responses;
- clean install;
- upgrade/migration;
- repeated install/check idempotency;
- OCMOD parse/refresh/generated result;
- query totals/pagination/cache consistency;
- multistore behavior;
- uninstall/disable path;
- final diff review.

State exactly what could not be executed.

## Phase 11 — Capture reusable OpenCart learning

At the end of substantial work, read `docs/OPEN_CART_LIVING_KNOWLEDGE.md` and determine whether Openboost should learn something new.

If Openboost is writable, update the correct skill/reference.

If it is not writable, include a short `Openboost knowledge update` recommendation in the handoff.

Project-specific hacks do not belong in the global bootstrap unless generalized and scoped.

## Minimal user invocation

A user can simply send something like:

```text
Use https://github.com/kabasik007/Openboost

Ось OpenCart-проєкт / repo. Треба додати ...
```

The agent should automatically read Openboost, route to the correct skills, analyze the target project, and continue without asking the user to restate standard OpenCart conventions.

## Handoff format

At completion, report concisely:

- what the target project actually uses;
- what existing implementation was found;
- what changed;
- what was reused rather than rewritten;
- translations/OCMOD/schema/lifecycle impact;
- verification performed;
- remaining risks;
- reusable Openboost knowledge added or proposed.