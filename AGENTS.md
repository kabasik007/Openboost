# AGENTS.md — Openboost AI Development Rules

This file is the primary instruction entry point for AI coding agents working in this repository.

## Core rule

**Do not rewrite, duplicate, or replace functionality before checking whether it already exists.**

Before any implementation:

1. Inspect the repository structure.
2. Search for existing code related to the requested task.
3. Identify the real language, framework, versions, entry points, configuration, persistence layer, UI layer, tests, build/deploy flow, and conventions from repository evidence.
4. Read existing documentation and comments.
5. Reuse or extend existing abstractions whenever reasonable.
6. Ask only for critical information that cannot be determined from the repository and that materially blocks a correct implementation.

Repository code and configuration are the source of truth. Never guess a version or architecture when it can be verified.

## Mandatory pre-implementation scan

For every non-trivial task, inspect at least:

- repository root;
- README and project docs;
- package/dependency manifests;
- runtime/config files;
- source directories;
- application entry points;
- routes/controllers/handlers/commands where applicable;
- models/services/repositories/data access where applicable;
- templates/components/assets where applicable;
- migrations/schema/install scripts where applicable;
- tests and CI workflows;
- existing feature names, classes, functions, database tables, API endpoints, UI components, and config keys related to the requested change.

Use search before creating new concepts.

## Evidence-first project memory

Keep `docs/PROJECT_MAP.md` current. Facts added there must be backed by repository evidence.

Use these states:

- `confirmed` — verified from code/config/docs;
- `inferred` — strongly implied but not directly declared;
- `unknown` — not yet established.

Do not convert `unknown` into a guessed fact.

## Implementation behavior

Prefer the smallest coherent change that integrates with the existing system.

Do not:

- create a second implementation of an existing subsystem;
- introduce a new framework/library without checking whether the project already has an equivalent;
- rename or move public interfaces casually;
- replace working code just to make it stylistically different;
- perform broad refactors unrelated to the requested change;
- add speculative compatibility code for environments not evidenced by the repository;
- silently change persisted data contracts, API contracts, routes, configuration keys, or user-visible behavior.

When a task requires touching existing behavior, identify the current path first and preserve compatibility unless the task explicitly requires a breaking change.

## Questions policy

Do not ask questions that the repository can answer.

Good question conditions:

- a required compatibility target is genuinely absent;
- multiple materially different product behaviors are possible and code cannot establish intent;
- a destructive migration/deletion requires product confirmation;
- a secret, external credential, provider choice, or business rule cannot be inferred safely.

If work can proceed safely with a narrow assumption, document the assumption and continue instead of blocking unnecessarily.

## Planning

For substantial work, update `docs/ROADMAP.md` before or during implementation.

A useful task plan should include:

- objective;
- existing implementation found;
- files/subsystems likely affected;
- compatibility constraints;
- implementation steps;
- validation steps;
- migration/rollback concerns when relevant.

Do not make a roadmap that simply says “write code” and “test”.

## Validation

Before calling work complete:

1. Run the most relevant available tests/checks.
2. Verify syntax/build/type/lint checks if the repository provides them.
3. Check integration points affected by the change.
4. Check backward compatibility where relevant.
5. Review the diff for accidental unrelated edits.
6. Update documentation when architecture, configuration, setup, or public behavior changed.

If a validation step cannot be run, state exactly what was not verified.

## Entry-point discovery

Do not assume entry points. Find them from evidence such as:

- package scripts;
- `main`, `bin`, CLI definitions;
- web server/bootstrap files;
- framework routing configuration;
- application factories;
- service registration;
- Docker/Compose commands;
- CI invocation;
- platform-specific extension hooks.

Record confirmed entry points in `docs/PROJECT_MAP.md`.

## Database and migrations

Before changing persistence:

- inspect current schema/migrations/install/upgrade code;
- search all reads/writes of the affected field/table/entity;
- check indexes, foreign-key-like relations, compatibility code, and uninstall paths;
- prefer an explicit migration/upgrade mechanism over runtime schema mutation in hot request paths.

Never invent table/column names without repository evidence.

## UI work

Before creating UI:

- identify the existing template/component system;
- find adjacent screens/components;
- reuse design tokens, utilities, components, translations, and patterns;
- inspect responsive/mobile behavior when relevant.

Do not introduce a parallel design system for one feature.

## API/integration work

Before creating or changing an API/integration:

- inspect existing client/server abstractions;
- locate authentication/error/retry/logging conventions;
- search for existing provider adapters;
- preserve response/request contracts unless intentionally changing them.

## Documentation hierarchy

Read in this order when present:

1. `AGENTS.md`
2. `AI_BOOTSTRAP.md`
3. task-specific docs
4. `docs/PROJECT_MAP.md`
5. `docs/ROADMAP.md`
6. README/setup docs
7. code/config as final source of truth when documentation is stale

## Definition of done

A change is done only when:

- existing implementation was inspected first;
- duplicate functionality was avoided;
- the change is integrated into the real architecture;
- relevant validation was performed;
- compatibility/migration implications were handled;
- project documentation is updated when needed;
- unresolved risks are explicitly stated.
