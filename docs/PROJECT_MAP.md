# Openboost Project Map

Status legend:

- **confirmed** — verified from repository/user requirement
- **inferred** — strongly implied but not directly declared
- **unknown** — not established

## Repository

- Repository: `kabasik007/Openboost` — **confirmed**
- Default branch: `main` — **confirmed**
- Visibility: public — **confirmed**

## Product purpose

- Reusable AI bootstrap/knowledge base for **OpenCart development** — **confirmed**.
- Intended usage: user supplies Openboost together with a real OpenCart project/task; AI reads Openboost and automatically understands the standard analysis/development workflow — **confirmed**.
- Openboost itself is not the target store/application — **confirmed**.

## Compatibility policy

- Default PHP minimum for newly developed OpenCart code: **PHP 7.1+** — **confirmed**.
- PHP 5.6: legacy-only and must be explicitly required — **confirmed**.
- Exact OpenCart version: must be detected from the target project — **confirmed**.
- OpenCart 2.3-style architecture: important first-class target, but not universal — **confirmed**.
- Template engine: detect from target project; do not assume TPL/Twig — **confirmed**.
- Language folder aliases: detect from target project — **confirmed**.

## Knowledge structure

```text
AGENTS.md
  → top-level rules for every AI session

AI_BOOTSTRAP.md
  → complete OpenCart session workflow

skills/README.md
  → automatic skill router

skills/opencart-project-analysis/SKILL.md
  → repository analysis and debugging path

skills/opencart-module-development/SKILL.md
  → module architecture and lifecycle

skills/opencart-i18n/SKILL.md
  → languages/translations/language_id

skills/opencart-ocmod/SKILL.md
  → OCMOD design/debugging/conflicts

skills/references/
  → derived architecture lessons from concrete modules

docs/OPEN_CART_LIVING_KNOWLEDGE.md
  → continuous-learning policy
```

## Initial architecture reference

- Source analyzed: user-provided OCFilter 4.8.2 package — **confirmed**.
- Original third-party source archive is not committed to Openboost — **confirmed**.
- Derived reference: `skills/references/ocfilter-4.8.2-architecture.md` — **confirmed**.

Important reusable OCFilter lessons captured:

- thin OpenCart integration + module-owned core/library;
- separated admin controllers/models;
- version compatibility adapter;
- explicit install permissions/events/database steps;
- idempotent repair/update checks;
- schema/data migration instead of reset;
- UI language files vs `language_id` content tables;
- new-language lifecycle;
- TPL/Twig compatibility only when needed;
- thin OCMOD delegation and runtime modification verification;
- isolated third-party/theme/SEO compatibility.

## Knowledge-growth policy

- Openboost is a living bootstrap — **confirmed**.
- After substantial OpenCart tasks, reusable lessons should update the narrow relevant skill/reference when Openboost is writable — **confirmed**.
- Project-specific hacks must not be promoted to global rules without generalization/scope — **confirmed**.

## Future project-map behavior

This file maps **Openboost itself**.

When Openboost is used with a target OpenCart repository, the target project's architecture should be recorded in that target repository's own project map/docs where available. Do not overwrite this file with one customer's store-specific paths.

## Open questions / future knowledge packs

The following are not yet dedicated skills and may be added as repeated real-world work justifies them:

- Journal3-specific integration patterns;
- OpenCart 2.3 deep compatibility pack;
- OpenCart 3.x/4.x compatibility packs;
- extension packaging/release workflow;
- cron/background processing patterns;
- multistore deep-dive;
- SEO URL compatibility matrix;
- migration/versioning standard for SiteZilla/Openboost modules;
- automated module quality/audit checklist.
