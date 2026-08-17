# Openboost Skills

Openboost is an OpenCart-focused AI development bootstrap. AI agents should load the narrowest relevant skill automatically; the user should not need to name skill files manually.

## Skill router

### Analyze an existing OpenCart project

Read:

`skills/opencart-project-analysis/SKILL.md`

Use for:

- first look at a repository;
- debugging unknown architecture;
- finding where a feature is implemented;
- determining OpenCart/PHP/template/language structure;
- tracing admin/catalog/runtime behavior.

For any substantial existing project also read:

`skills/opencart-architecture-map/SKILL.md`

The architecture skill is responsible for finding, validating, creating, and maintaining the target project's living `docs/ARCHITECTURE.md` (or existing equivalent). The user should not need to ask for this separately.

### Living project architecture map

Read:

`skills/opencart-architecture-map/SKILL.md`

Use for:

- initial orientation in a non-trivial OpenCart project;
- documenting `Core`, `Services`, models/repositories/adapters and ownership boundaries;
- mapping modules/subsystems and their entry points;
- documenting runtime request/data flows;
- mapping tables/settings/events/OCMOD/cron/integrations;
- maintaining a practical `Where to look first` index;
- validating an existing architecture document against current code;
- updating architecture docs after structural changes.

Mandatory behavior:

```text
search existing architecture doc
        ↓
if equivalent exists → validate + update it
        ↓
if none exists → create docs/ARCHITECTURE.md
        ↓
use it as navigation for future work
        ↓
verify task-relevant facts against code before editing
```

Do not create duplicate architecture documents when the target project already has a suitable equivalent.

### Create or structurally edit a module

Read:

`skills/opencart-module-development/SKILL.md`

Use for:

- new modules;
- install/update/uninstall architecture;
- controller/model/library separation;
- migrations;
- permissions/events;
- packaging/layout decisions.

Load `opencart-architecture-map` too when the change creates/moves services, modules, tables, integrations, hooks, jobs, or other architecture-significant pieces.

### UI, themes and responsive module design

Read:

`skills/opencart-ui-ux/SKILL.md`

Use for:

- any visible frontend module UI;
- buttons, colors, badges, cards, forms, tabs, modals or drawers;
- theme presets and custom themes;
- design tokens/CSS variables;
- mobile-first frontend work;
- modern responsive admin screens;
- searchable/select-heavy admin workflows;
- responsive tables, bulk actions and long settings forms;
- accessibility, loading, empty and error states;
- Journal3/custom-theme UI compatibility.

If a module has its own meaningful buttons/colors/visual surface, load this skill automatically even when the user did not explicitly ask for “design”.

### Languages and translations

Read:

`skills/opencart-i18n/SKILL.md`

Use for:

- Ukrainian/Russian/other language files;
- admin/catalog UI text;
- language tabs;
- `language_id` description tables;
- multilingual SEO/content;
- new-language lifecycle.

### OCMOD

Read:

`skills/opencart-ocmod/SKILL.md`

Use for:

- modification XML;
- core integration;
- generated modification debugging;
- conflicts;
- modification refresh/update;
- deciding between events and OCMOD.

## Architecture references

Concrete module analyses live under:

`skills/references/`

Current reference:

- `skills/references/ocfilter-4.8.2-architecture.md` — derived architectural lessons from the user-provided OCFilter 4.8.2 package.

References are evidence and pattern sources. They are not code templates to copy line-for-line.

A reusable target-project architecture document template is available at:

`templates/ARCHITECTURE.md`

Use it only when the target project does not already have a suitable architecture document.

## Default compatibility

```text
New PHP code: PHP 7.1+ minimum
PHP 5.6: explicit legacy support only
OpenCart version: detect from the target repository
Template engine: detect from the target repository
Languages: detect actual project folder codes + language_id behavior
Frontend UI: mobile-first by default
Module colors: centralize as theme tokens when meaningful
Project architecture: maintain a living code-backed architecture map
```

## Automatic loading rule

If a task spans multiple concerns, load multiple skills.

Examples:

```text
"Ось новий великий OpenCart проект, треба розібратися"
→ project-analysis + architecture-map

"Додай мультимовний модуль"
→ project-analysis + architecture-map + module-development + i18n
→ + ui-ux when it has visible frontend/admin UI

"OCMOD не застосувався"
→ project-analysis + ocmod
→ + architecture-map when the interception is architecture-significant or the project map is missing/stale

"Додай поле в адмінку існуючого модуля"
→ project-analysis + module-development
→ + ui-ux if the screen/layout/control changes visibly
→ + i18n if user-visible text is added
→ + ocmod if the field must be injected into core/third-party screens
→ + architecture-map if data ownership or module boundaries change

"Зроби плаваючу кнопку на фронті з вибором кольору"
→ project-analysis + module-development + ui-ux
→ + i18n for labels/help text

"Перероби панель продавця під телефон"
→ project-analysis + ui-ux
→ + module-development if routes/models/settings also change

"Винеси імпорт у Core/Services і додай cron"
→ project-analysis + architecture-map + module-development
```

The user should be able to provide only the Openboost repository link plus the task. The agent is responsible for routing itself to the correct skills.

## Living knowledge

Read `docs/OPEN_CART_LIVING_KNOWLEDGE.md` for the rule that Openboost should improve after substantial OpenCart work.

Do not add one-off project hacks as universal rules. Reusable lessons belong in skills; project-specific facts belong in the target project's project map/architecture/docs.