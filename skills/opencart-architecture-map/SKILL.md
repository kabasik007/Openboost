---
name: opencart-architecture-map
description: Living architecture documentation standard for OpenCart projects. Creates or maintains a code-backed ARCHITECTURE.md that maps layers, modules, runtime flows, data ownership, integrations, and where developers/AI should look first.
---

# OpenCart Architecture Map

Use this skill for every substantial OpenCart project, especially when the repository has multiple custom modules, shared libraries, services, integrations, OCMODs, themes, cron jobs, or non-trivial data flows.

The purpose is speed: an AI or developer should be able to read one architecture document and immediately understand the project's major layers, ownership boundaries, and the correct place to inspect or change a feature.

## Mandatory rule

For the **target project** (not Openboost itself):

1. Search for an existing architecture document before creating one.
2. Prefer, in order:
   - `docs/ARCHITECTURE.md`
   - `ARCHITECTURE.md`
   - an existing clearly equivalent project architecture document.
3. If a suitable document exists, validate it against code and **update it instead of creating a duplicate**.
4. If no suitable document exists and the project is non-trivial, create `docs/ARCHITECTURE.md`.
5. Read this document early in later sessions, but never trust it blindly: verify task-relevant sections against current code before implementation.
6. When structural changes are made, update the architecture document in the same task.

Do not create multiple overlapping files such as `ARCHITECTURE.md`, `PROJECT_ARCHITECTURE.md`, `SYSTEM_MAP.md`, and `CODE_MAP.md` unless the project already has an intentional documentation hierarchy.

## Difference from PROJECT_MAP

`PROJECT_MAP.md` is a compact project passport:

- OpenCart/PHP version;
- languages;
- theme/template engine;
- important paths;
- build/test/runtime facts;
- high-level integrations.

`ARCHITECTURE.md` is the **relationship and navigation map**:

- what the layers are;
- what belongs to each layer;
- which modules/services own which responsibilities;
- how requests/data flow through the system;
- where settings/data/translations live;
- which events/OCMODs intercept flows;
- where to look first for a given feature.

Do not duplicate every PROJECT_MAP fact. Cross-link when appropriate.

## Architecture must be evidence-backed

Do not invent a clean architecture that the code does not actually have.

Document reality first.

Use labels when useful:

- `confirmed` — directly verified from code/config;
- `inferred` — strongly implied;
- `legacy` — still present but not preferred for new work;
- `target` — intended direction, clearly separated from current state.

If a project has no `Core` or `Services` layer, do not pretend it does. Record the current structure and recommend a migration path separately if needed.

## Required document sections

A useful `docs/ARCHITECTURE.md` should normally contain the following sections, adapted to the project.

### 1. Architecture summary

A compact 10–30 line overview answering:

- what kind of OpenCart project this is;
- major custom subsystems;
- primary architectural pattern;
- where shared business logic lives;
- major frontend/admin/theme integrations;
- major persistence/integration boundaries.

Example:

```text
OpenCart HTTP route/controller
        ↓
Module integration layer
        ↓
Core / Services
        ↓
Models / Repositories / Adapters
        ↓
DB / OpenCart APIs / third-party APIs
        ↓
View data
        ↓
TPL/Twig + JS/CSS
```

Only show layers that really exist.

### 2. Directory and layer map

Map important directories to responsibilities rather than dumping the entire file tree.

Example:

```text
admin/controller/extension/module/foo/
  → admin routes and request orchestration

admin/model/extension/module/foo/
  → OpenCart persistence/data access

system/library/foo/core/
  → module domain/core behavior

system/library/foo/services/
  → reusable application services

system/library/foo/integrations/
  → external/OpenCart adapters

catalog/controller/extension/module/foo/
  → frontend entry points

catalog/view/theme/.../foo/
  → frontend rendering
```

For each major directory, state what **should** and **should not** live there if the project has established conventions.

### 3. Core and services map

This is mandatory when the project has module-owned libraries/services.

Create a concise table/list such as:

```text
Core
├── Bootstrap/Core object
├── Settings
├── Cache
└── Shared helpers

Services
├── ProductSyncService
│   └── synchronizes supplier data into OpenCart products
├── PriceService
│   └── owns price calculations
└── NotificationService
    └── owns customer/admin notifications
```

For every important service/class record:

- responsibility;
- public entry points/methods if useful;
- direct dependencies;
- data it owns or mutates;
- main callers.

Avoid documenting every private helper method. The document is a navigation tool, not generated API docs.

### 4. Module/subsystem ownership map

For each important custom module/subsystem, document:

```text
Name:
Purpose:
Admin entry points:
Catalog entry points:
Core/services:
Models/data:
Templates/assets:
Languages:
Settings/config:
Tables:
Events:
OCMOD:
Cron/jobs:
External integrations:
Known compatibility layer:
```

This is especially important when multiple modules touch products/orders/customers or the same frontend screen.

### 5. Runtime flows

Document the most important request/data flows as short arrows.

Examples:

```text
Admin save
route
→ controller
→ validate modify permission
→ service/model
→ settings/table
→ cache invalidation
→ redirect/success
```

```text
Catalog widget
layout/module assignment
→ controller
→ service
→ model/query
→ active theme template
→ JS behavior
```

```text
Supplier import
cron/CLI/admin action
→ importer service
→ parser/adapter
→ product matcher
→ DB writes
→ image queue
→ log/report
```

```text
Core extension
OpenCart route
→ event/OCMOD
→ module adapter
→ service
→ original flow continues
```

Prefer the flows that developers repeatedly need to trace.

### 6. Data ownership

Map custom tables/settings to the subsystem that owns them.

Example:

```text
oc_foo_item
  owner: Foo module
  key: foo_item_id
  multilingual: oc_foo_item_description(language_id)
  store relation: oc_foo_item_to_store(store_id)
  written by: FooItemService / admin model
  read by: catalog Foo controller/model
```

Also document important OpenCart core tables when custom code relies on them in unusual ways.

Include:

- `language_id` dimensions;
- `store_id` dimensions;
- indexes/unique keys that matter to behavior;
- migrations/upgrade owner.

### 7. Configuration and settings ownership

Document important config prefixes and where they are read/written.

Example:

```text
module_foo_*
  → general module settings

foo_theme_*
  → visual theme tokens/preset

foo_cron_*
  → cron/import settings
```

If configuration is split between OpenCart `setting` storage and module tables, explain why.

### 8. Languages and views

Record:

- actual admin/catalog language folders;
- how interface language files are loaded;
- multilingual entity tables;
- active template engine;
- active frontend theme / Journal3 integration;
- module template fallback rules.

Do not repeat every translation key.

### 9. Events, OCMOD and interception points

Maintain a compact list of architecture-significant hooks:

```text
Event code
→ trigger
→ handler
→ purpose

OCMOD code
→ target file
→ insertion point
→ delegated module method
```

This section is critical because original OpenCart source may not represent runtime behavior.

### 10. Integrations and adapters

Map third-party/OpenCart integrations:

```text
Nova Poshta
→ NovaPoshtaAdapter
→ OrderDeliveryService

LiqPay
→ LiqPayClient
→ PaymentService
```

Keep vendor API details in dedicated docs if large; architecture should show ownership and direction of dependency.

### 11. Background work

Document:

- cron endpoints;
- CLI jobs;
- scheduled imports;
- queues/workers if present;
- locks/idempotency strategy;
- logs/report locations.

### 12. UI architecture

When substantial custom UI exists, document:

- frontend module wrapper/components;
- admin screen groups;
- shared JS/CSS namespaces;
- theme token/preset storage;
- responsive/mobile-first rules;
- Journal3/custom-theme adapters.

Load `../opencart-ui-ux/SKILL.md` for detailed UI rules.

### 13. Security and permissions

Document architecture-significant security boundaries:

- admin access/modify routes;
- token/user_token handling;
- AJAX mutation checks;
- cron secret/auth pattern;
- external webhook verification;
- sensitive config ownership.

Do not put secrets in architecture docs.

### 14. Where to look first

This section is strongly recommended because it saves the most AI/developer time.

Example:

```text
Product import issue
→ system/library/importer/
→ admin/model/extension/module/importer/
→ cron controller
→ importer tables/logs

Frontend seller card missing
→ catalog controller
→ seller service/model
→ Journal3 block integration
→ active theme template
→ relevant OCMOD

Translation missing
→ active language folder
→ language route
→ description table language_id
```

Keep this as an index of common project concerns.

### 15. Architectural hazards / do-not-duplicate list

Record important traps such as:

- two similarly named legacy modules;
- runtime OCMOD replacing a core path;
- a shared service that must be reused;
- a custom URL alias layer;
- a table that looks module-specific but is shared;
- Journal3 override that bypasses default controller/template flow.

Also list established abstractions that new code should reuse instead of creating parallel implementations.

### 16. Architecture verification metadata

At the top or bottom record something like:

```text
Last architecture verification: 2026-08-17
Verified against commit: <sha if available>
Scope: full / partial
Known stale/unknown areas: ...
```

Do not claim `full` if only one module was inspected.

## First-project bootstrap workflow

When entering an undocumented OpenCart project:

1. Load `opencart-project-analysis`.
2. Search for existing architecture docs.
3. Scan platform/version/languages/theme/OCMOD/events/data layout.
4. Identify major custom modules and shared libraries.
5. Trace at least the important flows relevant to the current task.
6. Create/update `docs/ARCHITECTURE.md` with confirmed structure.
7. Continue implementation using the architecture map.
8. Update the map if implementation changes structure.

Do not spend a whole task exhaustively documenting unrelated legacy corners. Start with a strong high-level map and deepen sections as real work touches them.

## Subsequent-session workflow

At the beginning of later substantial tasks:

1. Read `docs/ARCHITECTURE.md` early.
2. Use it to identify likely files/services.
3. Verify those task-relevant paths against code.
4. Repair stale documentation immediately when discovered.
5. Use the updated architecture as the basis for planning.

The architecture document accelerates discovery; it never replaces repository evidence.

## Update triggers

Update `ARCHITECTURE.md` when a change introduces or materially changes:

- module/subsystem boundaries;
- `Core`, `Service`, `Repository`, `Adapter` layers;
- routes/controllers that become public entry points;
- custom tables or ownership;
- events/OCMOD interception points;
- cron/background processes;
- external integrations;
- shared UI/theme architecture;
- language/store data strategy;
- install/update lifecycle;
- important dependencies between modules.

Do **not** rewrite the architecture document for a typo or isolated CSS adjustment with no structural impact.

## Architecture change discipline

When code and architecture disagree:

- code/config/runtime evidence wins for the current state;
- update stale docs;
- if code violates an intentional documented target architecture, explicitly mark the mismatch and decide whether the task includes fixing it.

Never change production code only to make an outdated architecture document look correct.

## Definition of done

For a substantial project/module task, architecture documentation is healthy when:

- an existing equivalent doc was searched before creating a new one;
- major layers and module ownership are understandable;
- Core/Services/Models/Adapters are mapped when present;
- important runtime flows are documented;
- data/config/OCMOD/event ownership is clear;
- there is a practical `Where to look first` section;
- task-relevant sections were verified against current code;
- structural changes made by the task are reflected in the document;
- the next AI/developer can orient without rescanning the entire repository from zero.
