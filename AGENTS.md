# AGENTS.md — Openboost OpenCart AI Development Rules

This file is the primary instruction entry point for AI coding agents using Openboost.

Openboost is **not an application repository**. It is a reusable OpenCart development bootstrap/knowledge base that should be supplied alongside the real target project.

## One-link mode

The user should be able to provide this repository plus an OpenCart task without explaining OpenCart architecture every time.

When Openboost is present:

1. Read this file.
2. Read `AI_BOOTSTRAP.md`.
3. Load task-relevant skills from `skills/README.md` automatically.
4. Inspect the **target OpenCart project**, not only Openboost.
5. Tell the user briefly what you are about to analyze before implementation.
6. Find existing functionality before creating anything new.
7. Implement only after the real integration path is understood.

Do not ask the user which Openboost skill to use.

## Default compatibility policy

For new OpenCart development through Openboost:

- **PHP 7.1+ is the default minimum.**
- PHP 5.6 is legacy support and must be explicitly required by the task/project.
- Never assume the exact OpenCart version; detect it from repository evidence.
- Never assume TPL/Twig; detect the active template engine from the target project.
- Never assume language folder codes; inspect the project's actual languages.

If the target project proves incompatible with the baseline, report the conflict instead of silently guessing.

## Core rule

**Do not rewrite, duplicate, or replace functionality before checking whether it already exists.**

Repository code/configuration is the source of truth.

Before implementation:

1. inspect the relevant OpenCart structure;
2. search for existing routes/controllers/models/services/settings/tables/templates/language keys;
3. inspect OCMOD/events that may alter the same runtime path;
4. identify active languages/theme/template engine;
5. identify the smallest coherent extension point;
6. preserve compatibility and existing data unless the requested change intentionally breaks them.

## Mandatory OpenCart skill routing

Read `skills/README.md` and load relevant skill files.

At minimum:

- any existing-project analysis/debugging → `skills/opencart-project-analysis/SKILL.md`;
- module creation/structural changes → `skills/opencart-module-development/SKILL.md`;
- translations/multilingual data → `skills/opencart-i18n/SKILL.md`;
- OCMOD/modification work → `skills/opencart-ocmod/SKILL.md`.

A task can require more than one skill.

## Mandatory opening behavior for non-trivial work

Before editing, give the user a concise statement of what is being analyzed.

For OpenCart, it should normally cover the relevant subset of:

- OpenCart version;
- PHP target;
- `admin/catalog/system` structure;
- route/controller/model path;
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
```

Also locate:

- active theme/Journal3/custom theme integration;
- SEO URL extensions;
- custom search/filter/checkout modules;
- cron/events;
- install/update/uninstall code;
- schema/migrations;
- generated/runtime modification output when available.

## Architecture standard

Prefer:

```text
thin OpenCart controller/integration layer
        ↓
module model/service/library
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
- test refresh/update/conflict behavior.

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
- preserve data during upgrades.

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

## Template and theme policy

Use the target project's real view system.

Do not create both TPL and Twig versions unless compatibility requirements justify both.

Before frontend work, inspect:

- active theme;
- default theme fallback;
- Journal3/custom controller/model overrides;
- existing CSS/JS conventions;
- OCMOD template injections.

## Existing implementation first

Before creating a new module, field, table, route, helper, or JavaScript component, search for:

- feature/product terminology;
- likely route/class/function names;
- language keys;
- config keys;
- table/column names;
- OCMOD codes;
- disabled/legacy versions;
- theme compatibility code.

Extend existing abstractions when reasonable.

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
- AJAX response behavior;
- OCMOD XML parse + refresh + generated patch;
- totals/pagination/cache when product queries change;
- multistore behavior;
- uninstall/disable behavior;
- final diff contains no unrelated edits.

State what could not be verified.

## Living knowledge rule

Openboost must improve as OpenCart work continues.

Read `docs/OPEN_CART_LIVING_KNOWLEDGE.md`.

After substantial OpenCart work, determine whether a newly discovered pattern is reusable. If it is:

- update the narrow relevant skill/reference when Openboost is writable;
- otherwise include the proposed Openboost update in the handoff.

Do not turn one site's custom hack into a universal rule.

## Golden reference

The initial architecture reference is derived from user-provided OCFilter 4.8.2 and documented at:

`skills/references/ocfilter-4.8.2-architecture.md`

Adopt its strong architectural patterns, especially separation, migrations, language-aware data, permissions/events, and thin OCMOD integration.

Do **not** blindly copy its obfuscation, legacy PHP compatibility, giant controllers, broad third-party patches, or storage assumptions.

## Definition of done

A change is done only when:

- target project was analyzed first;
- relevant Openboost skills were loaded;
- existing implementation was searched;
- real OpenCart version/PHP/template/language constraints were established;
- duplicate functionality was avoided;
- change uses the real architecture/integration path;
- lifecycle/data/translation/OCMOD impacts were handled where relevant;
- relevant validation was performed;
- reusable new OpenCart knowledge was captured or proposed;
- unresolved risks are explicit.