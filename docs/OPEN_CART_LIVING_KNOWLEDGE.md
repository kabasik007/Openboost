# OpenCart Living Knowledge

Openboost is a **living OpenCart development bootstrap**, not a frozen document set.

The goal is that a user can give an AI agent this repository plus an OpenCart task and the agent immediately understands how to inspect the project, where OpenCart concerns live, which compatibility rules apply, and how to avoid repeating mistakes already solved in previous work.

## Continuous-learning rule

At the end of every substantial OpenCart development/debugging task, perform a short reusable-knowledge review.

Ask internally:

1. Did we learn a new fact about OpenCart architecture that is broadly reusable?
2. Did we find a better module structure or lifecycle pattern?
3. Did we discover an OCMOD failure mode or safer anchor strategy?
4. Did we learn a language/translation/multistore edge case?
5. Did we discover a version/PHP compatibility boundary?
6. Did we find a recurring Journal3/theme/SEO integration rule?
7. Did an existing Openboost instruction prove wrong, incomplete, or ambiguous?

If yes, update the relevant skill/reference when Openboost is writable.

If Openboost is not writable in the current environment, report the proposed reusable update explicitly in the handoff instead of losing it.

## Do not pollute global knowledge with one-off hacks

A finding belongs in Openboost only when it is reusable.

Project-specific details such as:

- one store's table prefix;
- one site's custom route name;
- one client's business rule;
- one temporary database repair;
- one theme-specific selector;

belong in that target project's `PROJECT_MAP`, task plan, or local docs unless they reveal a general pattern.

When a rule is specific to a known platform/version/theme, label its scope.

## Knowledge hierarchy

Prefer updating the narrowest correct location:

```text
skills/opencart-project-analysis/SKILL.md
  → where to look / how to trace a project

skills/opencart-module-development/SKILL.md
  → architecture / install / update / module coding

skills/opencart-i18n/SKILL.md
  → language files / language_id / multilingual data

skills/opencart-ocmod/SKILL.md
  → modification design / refresh / conflicts / debugging

skills/references/*.md
  → lessons from concrete high-quality modules or cases

AGENTS.md
  → only top-level policies that every task must obey
```

Do not make `AGENTS.md` enormous by copying every detailed lesson into it.

## Current compatibility policy

As of 2026-08-17:

- Openboost default minimum for new PHP code: **PHP 7.1**.
- PHP 5.6 is considered legacy and is supported only when a task explicitly requires it.
- Exact OpenCart version is always detected from the target project.
- OpenCart 2.3-style extension architecture remains an important target, but must not be assumed for every repository.

If this policy changes, update:

- `AGENTS.md`;
- relevant skills;
- this document;
- any reference docs that state the old baseline.

## Initial architecture source

The first OpenCart golden-reference analysis was based on user-provided OCFilter 4.8.2.

Reusable findings are recorded in:

`skills/references/ocfilter-4.8.2-architecture.md`

The original third-party module archive is intentionally not committed to Openboost.

## Learning entry format

When a new major reusable lesson is discovered, add a concise dated entry here only if it helps track policy evolution.

### 2026-08-17 — OCFilter 4.8.2 reference analysis

Established initial OpenCart skills for:

- repository/project analysis;
- module architecture;
- language/i18n handling;
- OCMOD design/debugging.

Adopted PHP 7.1+ as the new-module baseline and downgraded PHP 5.6 to explicit legacy support.

Key architectural standard: thin OpenCart integration layer + module-owned reusable core/services + explicit install/events/permissions/migrations + language_id-aware data + thin OCMOD integrations.