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

## Default compatibility

```text
New PHP code: PHP 7.1+ minimum
PHP 5.6: explicit legacy support only
OpenCart version: detect from the target repository
Template engine: detect from the target repository
Languages: detect actual project folder codes + language_id behavior
```

## Automatic loading rule

If a task spans multiple concerns, load multiple skills.

Examples:

```text
"Додай мультимовний модуль"
→ project-analysis + module-development + i18n

"OCMOD не застосувався"
→ project-analysis + ocmod

"Додай поле в адмінку існуючого модуля"
→ project-analysis + module-development
→ + i18n if user-visible text is added
→ + ocmod if the field must be injected into core/third-party screens
```

The user should be able to provide only the Openboost repository link plus the task. The agent is responsible for routing itself to the correct skills.

## Living knowledge

Read `docs/OPEN_CART_LIVING_KNOWLEDGE.md` for the rule that Openboost should improve after substantial OpenCart work.

Do not add one-off project hacks as universal rules. Reusable lessons belong in skills; project-specific facts belong in the target project's project map/docs.