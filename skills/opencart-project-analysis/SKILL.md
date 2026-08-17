---
name: opencart-project-analysis
description: Mandatory first-pass analysis for an existing OpenCart repository before any implementation, debugging, refactor, or module change.
---

# OpenCart Project Analysis

Use this skill whenever the target repository is OpenCart or appears to contain an OpenCart installation.

For substantial projects, load `../opencart-architecture-map/SKILL.md` together with this skill.

## Mandatory first response

Before changing code, tell the user in one short paragraph what you are about to inspect. The message should be concrete, for example:

> Спочатку аналізую структуру OpenCart-проєкту: визначаю версію OpenCart і PHP, admin/catalog/system, перевіряю наявну карту архітектури, Core/Services і спільні абстракції, активні мови та їх коди, тему/template engine, існуючі модулі й OCMOD-модифікації, таблиці/міграції та точки інтеграції потрібної функції. Якщо документа архітектури немає — створю його по фактичному коду. Потім покажу, що вже є, куди саме треба вносити зміни, і тільки після цього буду редагувати код.

Do not claim that analysis is complete until repository evidence has actually been inspected.

## Compatibility baseline

Openboost's default compatibility floor for new OpenCart development is **PHP 7.1+**.

- Do not design new modules around PHP 5.6 unless the task explicitly requires legacy PHP 5.6 support.
- Do not silently use PHP 7.4/8.x-only syntax when the target is PHP 7.1.
- If repository evidence shows PHP < 7.1, flag the conflict instead of pretending the Openboost baseline still runs there.
- A project may require a higher PHP version; repository evidence wins.

## Scan order

### 1. Confirm that this is OpenCart

Inspect the root and typical platform paths before assuming anything:

- `index.php`
- `admin/index.php`
- `config.php`
- `admin/config.php`
- `system/`
- `catalog/`
- `admin/`

Look for the declared `VERSION` constant first. Version heuristics are fallback evidence only.

Useful secondary indicators:

- OpenCart 2.3 commonly has `system/framework.php` and routes under `extension/module/...`.
- OpenCart 3.x commonly has the newer router structure and Twig templates.
- Older OpenCart releases may use `controller/module/...` instead of `controller/extension/module/...`.

Never infer an exact version from folder shape if the version can be read directly.

### 2. Find existing architecture documentation

Before creating any new project documentation, search for:

```text
docs/ARCHITECTURE.md
ARCHITECTURE.md
docs/PROJECT_MAP.md
other clearly equivalent system/architecture maps
```

If a real architecture document already exists:

- read it early;
- use it to locate likely Core/Services/modules;
- validate task-relevant paths against current code;
- repair stale facts;
- do not create a duplicate document.

If no suitable architecture document exists and the project is non-trivial, create `docs/ARCHITECTURE.md` after enough discovery to document reality.

Use `../opencart-architecture-map/SKILL.md`.

### 3. Determine PHP compatibility

Search, in order:

- Composer constraints if present;
- deployment/container configuration;
- hosting/runtime docs;
- CI matrix;
- syntax already used in first-party code;
- explicit project notes.

Record both:

- confirmed runtime/version;
- minimum compatibility target for code being changed.

Do not equate the server's current PHP version with the module's required minimum without evidence.

### 4. Map the OpenCart structure

Inspect at least:

```text
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
```

Then locate any custom extension roots and vendor/theme-specific areas.

Also identify architecture-significant shared layers such as:

```text
system/library/<module>/core/
system/library/<module>/services/
system/library/<module>/repositories/
system/library/<module>/adapters/
custom service containers / registries / loaders
```

Do not assume these layers exist; discover the real structure.

For the requested feature, answer:

- Which route/controller owns it?
- Which Core/service/shared library owns reusable behavior?
- Which model reads/writes its data?
- Which templates render it?
- Which language file provides UI strings?
- Which DB tables/settings store data?
- Which events/OCMOD modifications intercept the path?
- Which assets are loaded?

### 5. Detect the admin and catalog template engines

Do not assume TPL or Twig from OpenCart version alone.

Search the real neighboring feature for:

- `.tpl`
- `.twig`
- theme-specific render wrappers
- Journal/Journal3 integration
- custom loaders

If both formats exist, determine whether they are:

- cross-version compatibility copies;
- separate admin/catalog implementations;
- theme compatibility layers;
- stale legacy files.

### 6. Detect languages correctly

Inspect both:

```text
admin/language/
catalog/language/
```

Search project code/config for actual language codes and `language_id` use.

Do not assume Ukrainian is always `ua`, `uk`, `uk-ua`, `ua-uk`, or `ukrainian`; legacy OpenCart projects use different folder conventions. Use the folders/configuration that the project actually loads.

For multilingual entities, also search DB/model code for `*_description` tables keyed by `language_id`.

Load `../opencart-i18n/SKILL.md` for any translation or multilingual-data work.

### 7. Inspect modules before creating a new one

Search for:

- the requested feature name;
- related routes;
- config keys;
- table names;
- UI labels;
- module codes;
- service/class names;
- events;
- OCMOD `<code>` values;
- old/disabled versions.

A feature that looks absent in the original file may already be injected by OCMOD or owned by a shared service.

Load `../opencart-module-development/SKILL.md` before creating or structurally changing a module.

### 8. Inspect OCMOD and runtime modifications

Search for:

- `*.ocmod.xml`
- `install.xml`
- modification XML embedded in extension packages
- code that writes to the `modification` table
- `system/modification/` when present in the working copy
- modification refresh/update logic

For every core file you plan to edit or reason about, search whether an OCMOD operation targets it. Runtime behavior may differ from the repository's original source.

Load `../opencart-ocmod/SKILL.md` before adding or editing a modification.

### 9. Inspect events and lifecycle hooks

Search for:

- `addEvent`
- `deleteEvent`
- `addEventCode` or project wrappers
- `install()`
- `uninstall()`
- upgrade/migration methods
- cron endpoints/scripts

Determine whether the requested integration can use an event before adding an OCMOD patch.

Record architecture-significant event/cron ownership in the project architecture document.

### 10. Inspect database ownership

For custom tables/settings:

- find creation SQL;
- find migration/upgrade SQL;
- find all readers/writers;
- identify the owning module/service;
- identify `DB_PREFIX` use;
- inspect indexes and composite keys;
- inspect per-store and per-language relations;
- inspect uninstall/purge behavior.

Never invent schema from UI assumptions.

Material custom table/settings ownership belongs in `ARCHITECTURE.md`.

### 11. Detect theme, UI and third-party integration

Search for the active theme and common integration layers, especially when they affect the requested path:

- Journal / Journal3
- custom checkout/search/filter modules
- SEO URL modules
- custom menu/layout loaders
- multistore overrides
- shared CSS/JS namespaces
- module theme presets/design tokens

Do not patch every known third-party extension preemptively. Add compatibility only for software actually present or explicitly targeted for distribution.

Load `../opencart-ui-ux/SKILL.md` when visible frontend/admin UI is involved.

## Architecture map output

For a substantial project, the analysis should leave the repository easier to understand than before.

If no suitable architecture document exists, create:

`docs/ARCHITECTURE.md`

If one exists, update it rather than duplicating it.

The document should grow into a practical map of:

```text
Architecture summary
Directory/layer map
Core and Services
Module/subsystem ownership
Runtime flows
Data/settings ownership
Languages/views
Events/OCMOD
Integrations/adapters
Cron/background work
UI architecture
Security/permissions
Where to look first
Architectural hazards / do-not-duplicate
```

Do not attempt exhaustive documentation of every legacy file in one session. Build a correct high-level map and deepen task-relevant areas as development continues.

## What to produce before implementation

For a non-trivial task, the internal analysis must establish:

```text
OpenCart version: confirmed / inferred / unknown
PHP target: confirmed / inferred / conflict
Architecture doc: found/current | found/stale | created | not applicable
Relevant Core/Services:
Template engine: confirmed / mixed / unknown
Languages: actual folder codes + language_id strategy
Target route/controller:
Target service/core layer:
Target model/data layer:
Target templates/assets:
Related OCMOD/events:
Existing implementation found:
DB impact:
Compatibility risks:
Smallest coherent change:
Validation plan:
```

Update `docs/PROJECT_MAP.md` when reusable compact project facts are learned.

Update `docs/ARCHITECTURE.md` when architecture relationships/ownership are learned or changed.

## Debugging: where to look first

Use symptom-driven tracing:

- Admin page missing: route → permission → controller → language → service/model → view → menu OCMOD/event.
- Text key shown literally/empty: correct language route → active language folder → key definition → `$this->load->language(...)` → view data.
- Frontend block missing: layout/module assignment → controller → status/config → service/model → active theme template → OCMOD/event → cache.
- Saving fails: `modify` permission → validate method → POST names → service/model → SQL/settings → redirect/session errors.
- SQL error: install/migration schema → DB_PREFIX → column/table existence → upgrade path → owner service/model → query escaping/casting.
- OCMOD appears ignored: XML path/search anchor → modification registration → refresh → generated modification cache → conflicting modification.
- Works in default theme but not Journal/custom theme: template path and theme loader → OCMOD target → Journal/custom controller/model replacements.
- Works in one language only: language folder aliases → language_id-indexed data → SEO URL per language/store → fallback logic.
- Duplicate logic appearing: architecture `do-not-duplicate` index → shared Core/Services → callers → legacy implementation.

Promote frequently recurring project-specific traces into the target architecture document's `Where to look first` section.

## Analysis completion rule

Do not start implementation merely because one matching file was found. Trace the complete request path from entry point through Core/Services/data/rendering and identify modification/event interception first.

For a substantial undocumented project, analysis is not considered fully bootstrapped until a usable architecture map exists or an existing equivalent has been validated.