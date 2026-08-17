# Project Architecture

> This is a living architecture document for the target OpenCart project.
> Keep it aligned with repository evidence. Do not document an idealized architecture as if it already exists.

## Verification

- Last architecture verification: `YYYY-MM-DD`
- Verified against commit: `<sha / branch / unknown>`
- Scope: `full | partial`
- Known stale/unknown areas: `...`

## 1. Architecture summary

Describe the project in 10–30 lines.

```text
OpenCart request / admin action
        ↓
Controller / integration layer
        ↓
Core / Services
        ↓
Models / Repositories / Adapters
        ↓
DB / OpenCart APIs / external APIs
        ↓
View data
        ↓
TPL/Twig + JS/CSS
```

Remove layers that do not exist and add real project-specific layers.

## 2. Directory and layer map

```text
admin/controller/...
  →

admin/model/...
  →

catalog/controller/...
  →

catalog/model/...
  →

system/library/...
  →

catalog/view/...
  →
```

Document responsibility, not every directory.

## 3. Core and services

```text
Core
├── ...
└── ...

Services
├── ...Service
│   ├── responsibility:
│   ├── main callers:
│   └── dependencies:
└── ...Service
```

If there is no Core/Services layer, state the current real structure instead of inventing one.

## 4. Modules and subsystem ownership

### `<module/subsystem>`

- Purpose:
- Admin entry points:
- Catalog entry points:
- Core/services:
- Models/data:
- Templates/assets:
- Languages:
- Settings/config:
- Tables:
- Events:
- OCMOD:
- Cron/jobs:
- External integrations:
- Compatibility layer:

Repeat only for architecture-significant modules.

## 5. Runtime flows

### `<flow name>`

```text
entry point
→ controller
→ service/model
→ storage/integration
→ response/view
```

Document the flows developers repeatedly need to trace.

## 6. Data ownership

### `<table/settings group>`

- Owner:
- Primary key / relation:
- `language_id` behavior:
- `store_id` behavior:
- Written by:
- Read by:
- Migration owner:
- Important indexes/constraints:

## 7. Configuration and settings

```text
<config prefix>
  → owner / meaning / read-write paths
```

## 8. Languages and views

- Admin language folders:
- Catalog language folders:
- Interface translation loading:
- Multilingual data tables:
- Template engine:
- Active theme:
- Journal3/custom-theme integration:
- Fallback rules:

## 9. Events and OCMOD

### Events

```text
<event code>
→ trigger
→ handler
→ purpose
```

### OCMOD

```text
<modification code>
→ target
→ integration point
→ delegated module method
```

## 10. Integrations and adapters

```text
<provider/platform>
→ <adapter/client>
→ <owning service>
```

## 11. Background work

- Cron:
- CLI:
- Workers/queues:
- Locks/idempotency:
- Logs/reports:

## 12. UI architecture

- Frontend modules/components:
- Admin screen groups:
- Shared JS namespace:
- Shared CSS namespace:
- Theme presets/tokens:
- Mobile-first rules:
- Journal3/theme adapters:

## 13. Security and permissions

- Admin access routes:
- Admin modify routes:
- Token/user_token handling:
- AJAX mutation checks:
- Cron auth:
- Webhook verification:
- Sensitive config ownership:

Do not store secrets here.

## 14. Where to look first

```text
<problem/feature>
→ first path
→ second path
→ relevant service/model
→ OCMOD/event/theme layer if applicable
```

Examples:

```text
Missing frontend block
→ catalog controller
→ module service/model
→ active theme template
→ layout assignment
→ relevant OCMOD/Journal integration

Missing translation
→ active language folder
→ loaded language route
→ language key
→ language_id-backed description data if applicable
```

## 15. Architectural hazards / do-not-duplicate

- `...` — existing shared abstraction; reuse it.
- `...` — legacy path still present; do not extend unless required.
- `...` — OCMOD/runtime replacement changes the apparent core flow.

## 16. Current architecture TODOs

Keep architectural debt separate from confirmed current state.

- [ ] ...
