# Openboost

**Openboost is a reusable AI bootstrap for OpenCart development.**

The goal is simple: give an AI agent this repository plus the real OpenCart project/task, and the agent should already know how to inspect OpenCart architecture, modules, languages, translations, OCMOD, install/update lifecycle, and where to look before changing code.

## Minimal usage

```text
Use https://github.com/kabasik007/Openboost

Ось OpenCart-проєкт. Треба зробити ...
```

The agent should then automatically:

1. read `AGENTS.md` and `AI_BOOTSTRAP.md`;
2. load relevant OpenCart skills;
3. analyze the target project's real OpenCart/PHP version;
4. detect languages, template engine/theme, modules, OCMOD/events, DB/install lifecycle;
5. find the existing implementation first;
6. explain briefly what it is analyzing;
7. make the smallest correct change;
8. verify it;
9. capture reusable OpenCart knowledge back into Openboost when appropriate.

## Default compatibility

```text
PHP minimum for new development: 7.1+
PHP 5.6: legacy-only, explicit requirement
OpenCart version: always detect from target project
Template engine: always detect from target project
Languages: detect actual folder codes and language_id behavior
```

OpenCart 2.3-style module architecture remains a first-class target, but Openboost must not assume every project is 2.3.

## Skills

- `skills/opencart-project-analysis/SKILL.md` — where to look and how to trace an existing OpenCart project.
- `skills/opencart-module-development/SKILL.md` — module architecture, install/update/uninstall, migrations, permissions/events, services/models/views.
- `skills/opencart-i18n/SKILL.md` — admin/catalog language files, UA/RU, `language_id`, multilingual entity data and SEO.
- `skills/opencart-ocmod/SKILL.md` — safe OCMOD design, refresh, runtime debugging and conflicts.

See `skills/README.md` for automatic routing rules.

## Golden architecture reference

The first reference analysis is based on a user-provided OCFilter 4.8.2 package. Its reusable architecture lessons are documented at:

`skills/references/ocfilter-4.8.2-architecture.md`

The third-party module itself is not committed here.

Openboost adopts the strong patterns from that module — separation, migrations, language-aware data, permissions/events, thin integrations — while intentionally avoiding obfuscation, PHP 5.x-era compatibility as a default, giant controllers, and unnecessary third-party patches.

## Living bootstrap

Openboost is expected to improve continuously during real OpenCart development.

Read:

`docs/OPEN_CART_LIVING_KNOWLEDGE.md`

Reusable discoveries should update the appropriate skill/reference. One-store hacks should stay in that store's own documentation.

## Important distinction

```text
Openboost
= instructions + OpenCart knowledge

Your store/module repository
= code that should actually be analyzed and changed
```

Do not implement a customer's store feature inside Openboost.