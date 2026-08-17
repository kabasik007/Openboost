# OCFilter 4.8.2 — Architecture Reference

This document records reusable architectural lessons extracted from the user-provided `ocfilter.4.8.2.ocmod` package.

The package itself is **not** stored in Openboost. Openboost stores only derived architecture guidance.

## Reference snapshot

Analyzed package:

- Product: OCFilter
- Version: 4.8.2
- Release constant in source: 2024-02-29
- Archive files inspected: 294
- Reference supports multiple OpenCart generations and legacy PHP; Openboost does not inherit all of those compatibility requirements.

## High-level package shape

The package separates responsibilities across:

```text
admin/controller/extension/module/ocfilter.php
admin/controller/extension/module/ocfilter/{filter,page,setting,cron}.php
admin/model/extension/module/ocfilter/{filter,page,setting}.php
admin/language/<many aliases>/extension/module/ocfilter...
admin/view/template/extension/module/ocfilter/...
admin/view/javascript/ocfilter/...
admin/view/stylesheet/ocfilter/...

catalog/controller/extension/module/ocfilter.php
catalog/controller/extension/feed/ocfilter_sitemap.php
catalog/model/extension/module/ocfilter.php
catalog/language/<many aliases>/extension/module/ocfilter.php
catalog/view/javascript/ocfilter48/...
catalog/view/theme/default/template/extension/module/ocfilter48/...
catalog/view/theme/default/stylesheet/ocfilter/...

system/library/ocfilter/{admin,api,cache,core,factory,filter,helper,init,opencart,params,placement,seo,setting}.php
system/ocfilter.ocmod.xml
```

The important lesson is not the exact names. The lesson is **separation**: OpenCart-facing routes remain separate from a module-owned core/library.

## Central registry service

The reference initializes an `OCFilter\Core` object, injects OpenCart's registry into an adapter, initializes module services, and registers the result as `ocfilter` in the registry.

Conceptually:

```text
OpenCart registry
      ↓
module init
      ↓
module Core
      ├── OpenCart adapter
      ├── settings
      ├── cache
      ├── helpers
      ├── params
      ├── SEO
      ├── placement
      ├── filtering
      ├── API
      └── admin helpers
```

This is a strong pattern for large modules because controllers do not need to own all shared behavior.

Openboost refinement: keep the same separation, but prefer explicit dependencies and readable classes over opaque factory indirection.

## OpenCart-version adapter

The reference has a dedicated `OpenCart` adapter that detects platform generation and hides differences such as:

- admin vs catalog context;
- template resolution;
- TPL vs newer view handling;
- theme file discovery;
- response helpers;
- version-dependent token/routes elsewhere in the module.

This is one of the strongest reusable patterns in the reference.

Openboost rule: if a module intentionally supports multiple OpenCart versions, isolate platform differences in one compatibility layer rather than scattering `version_compare()` across business logic.

For a single known project version, do not build unnecessary compatibility abstractions.

## Admin decomposition

The reference splits major admin domains into separate controllers and models:

- filter management;
- SEO/filter pages;
- settings;
- cron/copy work.

This is substantially better than placing every action inside one module controller.

Openboost refinement: split even earlier when a controller becomes hard to understand. The reference settings controller is very large and partly obfuscated; that is not a style to copy.

## Installation lifecycle

The main admin controller separates installation into distinct responsibilities:

```text
install()
  → clearOldFiles()
  → installPermission()
  → installEvent()
  → installDB()
```

It also performs a runtime `checkInstall()` that verifies:

- modification state;
- database tables;
- events;
- permissions.

This is a valuable idea: installation is not a one-bit state. A module can be partially installed and should be able to detect/repair missing owned pieces.

Openboost refinement: make repair/update paths explicit and avoid excessive side effects merely from opening a settings page.

## Permission handling

The reference grants explicit `access` and `modify` permissions to subroutes such as filter/page management.

Reusable lesson: new admin routes need their own permission strategy; parent module access is not sufficient by assumption.

## Event handling

The reference registers events for:

- an API/view integration hook;
- reacting when a new OpenCart language is added.

It uses different OpenCart event models depending on platform version and removes owned events on uninstall.

Reusable lesson: stable module-prefixed event codes + idempotent install + owned cleanup.

## New-language behavior

When OpenCart adds a language, the reference copies existing default-language rows into the new `language_id` for filter descriptions, value descriptions, and SEO page descriptions.

This is a sophisticated multilingual lifecycle detail often missing from custom modules.

Openboost refinement: do not always clone content. Choose clone/empty/fallback behavior according to product requirements, but define it intentionally.

## Database migration strategy

The reference contains substantial upgrade logic:

- checks whether tables/columns exist;
- creates new schema structures;
- adds temporary migration columns;
- maps old filter IDs/sources to new entities;
- copies old descriptions/relations into new tables;
- migrates page/SEO data;
- drops old structures only after migration steps.

Reusable lesson: **upgrade existing data instead of resetting the module**.

Openboost refinements:

- keep migrations versioned and readable;
- make each migration idempotent or safely detectable;
- avoid hard-coded MyISAM/collation defaults unless the target project requires them;
- avoid giant migration methods when smaller versioned migrations are practical.

## Translation architecture

The reference supports many legacy language folder aliases and uses `language_id`-based description tables for data.

Useful distinction:

```text
language PHP files = interface text
DB *_description tables = merchant/content translations
```

This distinction is part of the Openboost i18n standard.

The reference's many aliases are appropriate for broad extension distribution, but are overkill for one known store. Project-specific modules should detect actual language folder codes.

## TPL + Twig strategy

The reference ships parallel TPL and Twig templates for admin and catalog features.

Reusable lesson: when one package intentionally spans OpenCart generations, maintain equivalent templates per supported engine.

Openboost refinement: if the target project has one confirmed template engine, use it. Do not double maintenance without a compatibility requirement.

## OCMOD integration strategy

The reference modification file touches:

- admin product and menu integration;
- module/layout compatibility;
- catalog product/category/search/manufacturer/special flows;
- product model queries/totals;
- category/product templates;
- early module initialization;
- document links;
- Journal3 and other themes/modules;
- multiple SEO URL implementations;
- search/filter extensions.

This shows what a mature distribution extension sometimes needs, but it also demonstrates OCMOD fragility: many third-party anchors must stay compatible.

The strongest pattern is that injected snippets often delegate to the module's own core/model rather than implementing the full behavior inline.

Openboost rule: keep OCMOD thin and isolate compatibility packs.

## Runtime modification verification

The reference does not assume that placing XML on disk means it is active. It checks whether its registry/core has actually appeared after modification refresh and can trigger repair behavior.

Reusable lesson: during diagnosis, inspect generated runtime modification output, not only XML/original source.

## Old-file cleanup

The reference has a long explicit list of known files from older module layouts and removes them during upgrade.

Reusable lesson: explicit owned-file cleanup is safer than broad wildcard deletion.

Openboost refinement: keep cleanup scoped to files unquestionably owned by the module and document why each legacy path exists.

## Asset namespacing

The reference keeps admin/catalog CSS/JS under module-specific directories. This limits collisions and makes ownership clear.

Openboost adopts this as a default.

## What Openboost adopts as the golden standard

1. Analyze the real OpenCart version before coding.
2. Thin OpenCart-facing controllers.
3. Separate admin domains/routes.
4. Put substantial reusable behavior in a module-owned library/service layer.
5. Isolate OpenCart-version compatibility.
6. Separate installation into permissions/events/schema/defaults.
7. Support idempotent repair/update behavior.
8. Migrate old data rather than resetting it.
9. Treat UI translations and DB content translations separately.
10. Define new-language behavior.
11. Keep OCMOD integrations thin and auditable.
12. Verify runtime/generated modification output.
13. Namespace assets and routes.
14. Isolate theme/SEO/third-party compatibility.
15. Validate install, upgrade, runtime, and uninstall separately.

## What Openboost intentionally does not adopt

- PHP 5.4/5.6 as a new-module target;
- obfuscated code;
- unnecessarily large controllers;
- universal duplicated language aliases;
- universal dual TPL/Twig packaging;
- hard-coded database engine/collation assumptions;
- broad third-party compatibility patches when the target project does not use those systems;
- OCMOD when a native event/API is sufficient;
- automatic self-HTTP refresh as a default lifecycle mechanism.

## Default Openboost compatibility position after this analysis

For new work:

```text
PHP minimum: 7.1
OpenCart version: detect from target repository
OpenCart 2.3.x: first-class legacy/current target where project uses it
OpenCart 3.x+: supported by detected project conventions, not guessed compatibility
PHP 5.6: legacy-only, explicit request required
```

This reference must be updated when later OpenCart work reveals a better reusable pattern or a correction to these rules.