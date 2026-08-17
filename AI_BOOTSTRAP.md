# AI_BOOTSTRAP.md

Use this bootstrap at the beginning of a new AI-assisted development session in Openboost.

## Phase 0 — Read before changing code

Read:

1. `AGENTS.md`
2. `README.md`
3. `docs/PROJECT_MAP.md`
4. `docs/ROADMAP.md`
5. all task-specific documentation found by search

Then inspect the repository itself. Documentation may be incomplete or stale; code/config is the final source of truth.

## Phase 1 — Repository discovery

Build an evidence-backed view of the project.

Determine, where applicable:

- languages and runtime versions;
- framework/platform and version;
- dependency/package manager;
- source layout;
- application entry points;
- routes/controllers/handlers/commands;
- domain/services/models;
- persistence and migrations;
- templates/components/assets;
- translations/locales;
- background jobs/cron/workers;
- build/test/lint/typecheck commands;
- Docker/deployment/CI flow;
- configuration and environment handling;
- integrations and external APIs.

Do not guess missing facts.

## Phase 2 — Search for existing implementation

Before creating anything for the requested feature, search for:

- feature/product terminology;
- likely class/function/module names;
- routes and endpoint fragments;
- database table/field names;
- UI labels/translations;
- config keys;
- tests;
- TODO/FIXME notes;
- older/deprecated implementations.

Answer these questions internally before coding:

1. Does the feature already exist fully or partially?
2. Is there an abstraction that should be extended?
3. Is there compatibility or legacy behavior that must remain?
4. Which files are true integration points?
5. What is the smallest coherent change?

## Phase 3 — Update project memory

Update `docs/PROJECT_MAP.md` with confirmed facts if the repository has changed or the map is incomplete.

Do not fill the map with assumptions merely to make it look complete.

## Phase 4 — Plan the change

For non-trivial tasks, update `docs/ROADMAP.md` or create a task-specific plan under `docs/plans/`.

Record:

- requested outcome;
- current implementation found;
- affected areas;
- compatibility constraints;
- implementation sequence;
- validation sequence;
- migration/rollback concerns.

## Phase 5 — Implement by extension, not duplication

Prefer:

1. configuring existing functionality;
2. fixing/extending existing functionality;
3. adding a narrow missing layer;
4. creating a new subsystem only when repository evidence shows one does not already exist.

Match the project's existing coding and architectural conventions unless there is a concrete reason not to.

## Phase 6 — Verify

Use the repository's own commands first.

Validate as applicable:

- syntax;
- build;
- tests;
- lint/type checks;
- migrations;
- API behavior;
- UI behavior;
- responsive states;
- backward compatibility;
- installation/update/uninstall lifecycle;
- generated artifacts.

Review the final diff and remove unrelated changes.

## Phase 7 — Handoff

At completion, report concisely:

- what was found before implementation;
- what was changed;
- what was reused instead of rewritten;
- tests/checks run and results;
- any unverified areas or known risks;
- documentation/migration actions required.

## AI session starter prompt

A human can start an AI session with:

> Read `AGENTS.md` and `AI_BOOTSTRAP.md`. First inspect the repository and search for an existing implementation of my request. Do not rewrite or duplicate functionality that already exists. Update the project map with evidence-backed facts if needed, make a focused implementation plan, then implement and verify the change using the repository's own tooling.
