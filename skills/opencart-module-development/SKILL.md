---
name: opencart-module-development
description: Architecture and implementation standard for creating, extending, or refactoring OpenCart modules. Derived from analysis of OCFilter 4.8.2, but cleaned up for PHP 7.1+ projects.
---

# OpenCart Module Development

Use this skill when creating a new OpenCart module or making structural changes to an existing one.

The architectural reference behind this skill is OCFilter 4.8.2. Treat it as a **pattern source**, not as code to copy blindly.

## Compatibility contract

Default for Openboost-created modules:

- PHP **7.1+** minimum;
- exact OpenCart version must be detected from the target project;
- exact template engine must be detected from adjacent code;
- new PHP syntax must remain valid on PHP 7.1 unless a higher project minimum is confirmed;
- PHP 5.6 support is legacy and must be explicitly requested.

Avoid PHP 7.4/8.x-only constructs under the default floor, including typed properties, arrow functions, null-coalescing assignment, constructor property promotion, attributes, enums, union/intersection types, and match expressions.

## Architectural principle

Keep OpenCart integration thin and put reusable domain behavior in module-owned classes/models.

Good flow:

```text
OpenCart route/controller
        ↓
validation / request normalization
        ↓
module service or model
        ↓
DB / OpenCart APIs / integrations
        ↓
view data
        ↓
template
```

Do not build a 2,000-line controller if behavior can be separated into module-owned classes.

## Recommended module tree

For OpenCart 2.3-style extension routes, a mature module may look like:

```text
upload/
├── admin/
│   ├── controller/extension/module/<module>.php
│   ├── controller/extension/module/<module>/
│   │   ├── setting.php
│   │   ├── item.php
│   │   └── cron.php
│   ├── model/extension/module/<module>/
│   │   ├── setting.php
│   │   └── item.php
│   ├── language/<language>/extension/module/<module>.php
│   ├── language/<language>/extension/module/<module>/...
│   └── view/
│       ├── template/extension/module/<module>/...
│       ├── javascript/<module>/...
│       └── stylesheet/<module>/...
├── catalog/
│   ├── controller/extension/module/<module>.php
│   ├── model/extension/module/<module>.php
│   ├── language/<language>/extension/module/<module>.php
│   └── view/
│       ├── javascript/<module>/...
│       └── theme/default/
│           ├── template/extension/module/<module>/...
│           └── stylesheet/<module>/...
└── system/
    ├── library/<module>/
    │   ├── core.php
    │   ├── setting.php
    │   ├── helper.php
    │   └── ...
    └── <module>.ocmod.xml   # only when needed by the project's install strategy
```

This is a reference shape, not a requirement to create empty layers. Only add layers that have a real responsibility.

For another OpenCart version, adapt routes/paths to the target platform instead of forcing this tree.

## Controller responsibilities

Controllers should normally own:

- loading language/model dependencies;
- reading request parameters;
- permission/validation checks;
- normalizing data for the model/service;
- assembling template data;
- selecting redirect/JSON/view response.

Controllers should not normally own:

- large SQL migrations;
- complicated filtering/query construction;
- reusable domain calculations;
- third-party API clients;
- cache engines;
- SEO engines;
- giant transformation pipelines.

If one admin feature grows, split it into subcontrollers/routes such as:

```text
extension/module/<module>
extension/module/<module>/setting
extension/module/<module>/item
extension/module/<module>/page
```

The OCFilter reference uses this decomposition successfully for settings, filters, pages, cron, and catalog behavior.

## Model responsibilities

Models should encapsulate persistence and OpenCart-owned data operations.

Good model behavior:

- use `DB_PREFIX` consistently;
- cast numeric IDs;
- escape strings with `$this->db->escape()`;
- keep table ownership obvious;
- return stable arrays expected by controllers;
- isolate schema helpers/migrations from ordinary request queries;
- support language/store dimensions explicitly when the entity requires them.

Before adding a new query, search whether an existing model method already does the job.

## System library / module core

For modules with substantial logic, use a module-owned library under `system/library/<module>/` instead of spreading logic across controllers.

Strong patterns from the OCFilter reference:

- one central module object registered in the OpenCart registry;
- separate classes for settings, cache, helpers, filtering, SEO, placement, API, admin behavior;
- an OpenCart compatibility adapter that hides route/template/token differences;
- lazy or context-aware initialization for admin vs catalog.

A cleaner Openboost version should preserve separation but avoid unnecessary global state or opaque factory magic.

Prefer explicit class responsibilities and understandable names.

## Bootstrapping a module-owned core

If the module needs a registry-level core/service:

1. Find the least invasive supported startup hook for the target OpenCart version.
2. Prefer a native event/startup mechanism when it can initialize the service reliably.
3. Use OCMOD only if the required hook is unavailable.
4. Register one predictable key in the OpenCart registry, e.g. `<module>`.
5. Make startup idempotent; a second call must not duplicate listeners, queries, assets, or state.

Do not copy OCFilter's router/front OCMOD injection automatically into every module. It is justified only when the module genuinely requires early global initialization.

## Admin installation lifecycle

A robust installation path should be decomposed instead of hiding everything inside one method.

Recommended shape:

```text
install()
  ├── installPermissions()
  ├── installEvents()
  ├── installSchemaOrMigrations()
  └── installDefaults()       # if required
```

Also support a safe update/check path for already-installed modules.

### Permissions

If the module exposes additional admin routes, explicitly add both access and modify permissions for them when appropriate.

Example conceptual routes:

```text
extension/module/<module>
extension/module/<module>/item
extension/module/<module>/page
```

Do not assume permission to the parent route grants permission to child routes.

### Events

Register events with stable, module-prefixed codes. On uninstall, remove only events owned by the module.

Do not create duplicate events every time install/update is opened. Check ownership/existence or use an API that replaces by code.

### Database

Schema installation and upgrade must be idempotent.

Prefer:

- `CREATE TABLE IF NOT EXISTS` for genuinely new tables;
- `isColumnExists`/equivalent before `ALTER TABLE ADD`;
- explicit data migration from old schema to new schema;
- stable indexes/keys;
- preserving existing data during upgrades.

Avoid:

- dropping user data during a normal upgrade;
- schema changes in hot catalog requests;
- unconditional `ALTER` on every admin page load;
- hard-coding storage engine/collation without checking project standards.

The OCFilter reference is valuable because it migrates old filter/page structures into new tables instead of simply deleting previous data.

## Uninstall policy

Uninstall and purge are not the same thing.

Default safe uninstall should normally:

- disable module status;
- remove module-owned events;
- remove extension-specific permissions only when appropriate;
- stop cron/startup hooks;
- leave business/user data intact unless the product explicitly defines uninstall as destructive.

If a destructive purge is desired, implement it as a separate explicit action or document it clearly.

## Upgrade compatibility

When replacing an older module version:

1. Detect the old schema/files/settings.
2. Migrate data forward.
3. Remove obsolete files only when their ownership is certain.
4. Never delete broad patterns that might belong to another extension.
5. Preserve config keys where possible or migrate them explicitly.
6. Keep the upgrade repeatable after partial failure.

OCFilter has a dedicated old-file cleanup path; Openboost modules may use the same idea, but the cleanup list must be scoped to files definitely owned by the module.

## Templates

Use the project's actual template format.

- OpenCart 2.3 projects commonly use `.tpl`, but custom themes/loaders may differ.
- OpenCart 3.x commonly uses `.twig`.
- Distribution packages spanning multiple versions may ship parallel `.tpl` and `.twig` implementations when justified.

If both are maintained, keep their DOM structure, field names, validation display, and behavior equivalent.

Do not create Twig and TPL copies merely because the reference module has both. Target compatibility determines this.

## Assets

Keep assets namespaced:

```text
admin/view/javascript/<module>/
admin/view/stylesheet/<module>/
catalog/view/javascript/<module>/
catalog/view/theme/default/stylesheet/<module>/
```

Load assets only on routes/pages that need them where practical.

Do not bundle an entire editor/framework if OpenCart or the active theme already provides a compatible one.

## Settings

Before inventing a custom settings storage layer, inspect existing project conventions.

Use OpenCart settings for simple module configuration when sufficient. Use module-owned tables only for structured/high-volume entities or behavior that does not fit the setting store.

Keep config keys module-prefixed.

For cross-version distribution, isolate naming differences such as `ocfilter_*` vs `module_ocfilter_*` behind a small adapter rather than scattering `version_compare()` throughout business logic.

## Routes and tokens

Detect route/token conventions from the target version.

Typical differences include:

```text
OpenCart 2.x: token
OpenCart 3.x: user_token
```

Do not sprinkle conditional token logic in every controller. Prefer a small helper/adapter for multi-version modules.

## JSON/AJAX endpoints

For AJAX actions:

- permission-check admin mutations;
- validate request inputs;
- return correct content type;
- keep response shape stable;
- close/avoid session locking for legitimately long background requests when the project pattern supports it;
- do not expose destructive endpoints without CSRF/token protection.

## Multistore

If an entity is store-specific, model this explicitly with store relation tables or OpenCart settings scoped by `store_id`.

Never assume store 0 is the only target on a multistore project.

## Multilingual entities

UI translation files and entity translations are different concerns.

- UI strings belong in language PHP files.
- User/content data belongs in description tables keyed by `language_id`.

Load `../opencart-i18n/SKILL.md` whenever a module has translatable names, headings, descriptions, metadata, labels, or URLs.

## OCMOD vs events vs direct edits

Preferred order for integrations:

1. existing extension API/hook;
2. OpenCart event;
3. extension-owned wrapper/adapter;
4. OCMOD against a stable anchor;
5. direct core modification only when the repository intentionally maintains a fork.

Load `../opencart-ocmod/SKILL.md` before any modification XML work.

## Third-party compatibility

Keep compatibility adapters isolated from the core module.

Do not mix Journal3, SEO module, theme-specific, and custom search hacks into the primary domain logic.

If multiple third-party integrations are required, group them conceptually as compatibility packs and make each one detectable/removable.

## Validation checklist

Before declaring an OpenCart module change complete, verify as applicable:

- PHP syntax under the declared minimum;
- admin route opens;
- access/modify permissions work;
- install succeeds on clean schema;
- upgrade succeeds on previous supported schema;
- re-running install/check does not duplicate data/events;
- settings persist;
- all active languages render correctly;
- catalog module renders in active theme;
- AJAX routes return expected JSON;
- OCMOD refresh succeeds;
- generated modification contains the expected patch;
- multistore behavior is correct;
- uninstall disables/removes hooks safely;
- no unrelated core files were changed.

## Golden-reference lessons to adopt

From OCFilter 4.8.2, adopt these principles:

- split admin responsibilities by route/submodule;
- keep substantial reusable logic under `system/library/<module>/`;
- isolate OpenCart-version differences;
- separate install permissions/events/database steps;
- migrate legacy data forward;
- model translations by `language_id`;
- keep module assets and views namespaced;
- check runtime modification state instead of assuming XML has been applied;
- support theme/version compatibility deliberately, not accidentally.

## Do not blindly copy from the reference

Do **not** make these Openboost defaults:

- obfuscated controller code;
- PHP 5.4/5.6 compatibility scaffolding;
- one huge controller containing every setting;
- dozens of third-party OCMOD patches in the base module;
- hard-coded MyISAM/collation as a universal database choice;
- duplicated language alias folders unless distribution compatibility truly requires them;
- automatic self-HTTP modification refresh unless there is no simpler reliable lifecycle.

Openboost should keep the reference's architectural strengths while making the implementation clearer and easier to maintain.