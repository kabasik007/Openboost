# Openboost Development Roadmap

This roadmap is intentionally architecture-neutral until real application code is added.

AI agents should update it from repository evidence and the active product task rather than inventing milestones.

## Phase 0 — Resolve blockers

**Objective:** establish only the unknowns that materially affect implementation.

Check:

- runtime/framework compatibility;
- target platforms/environments;
- existing implementation of the requested feature;
- potentially destructive data/API changes;
- external dependencies or credentials.

**Exit gate:** implementation can proceed without guessing critical constraints.

## Phase 1 — Confirm repository truth

**Objective:** understand the actual project before changing it.

Deliverables:

- current `docs/PROJECT_MAP.md`;
- confirmed source structure;
- confirmed entry points;
- confirmed test/build/deploy commands;
- relevant existing components located by search.

**Exit gate:** affected architecture and integration points are known.

## Phase 2 — Define change contract

**Objective:** translate the requested work into observable behavior.

Document as applicable:

- current behavior;
- desired behavior;
- inputs/outputs;
- UI/API/data contract changes;
- compatibility requirements;
- non-goals.

**Exit gate:** the implementation target is unambiguous enough to build and verify.

## Phase 3 — Prepare implementation boundaries

**Objective:** choose the smallest coherent integration path.

Identify:

- files/modules to extend;
- interfaces/contracts to preserve;
- data migration requirements;
- tests to update/add;
- rollback concerns.

**Exit gate:** no unnecessary parallel subsystem is being introduced.

## Phase 4 — Implement

**Objective:** implement the requested behavior using the existing architecture.

Rules:

- reuse before creating;
- modify the narrowest correct layer;
- preserve compatibility unless intentionally changed;
- keep unrelated refactors out of scope.

**Exit gate:** requested behavior is implemented end-to-end.

## Phase 5 — Integrate

**Objective:** wire the implementation into all required lifecycle points.

Examples where applicable:

- routes/handlers;
- UI/templates;
- configuration;
- permissions/auth;
- install/update/uninstall hooks;
- jobs/workers;
- external adapters.

**Exit gate:** functionality is reachable through the real application path.

## Phase 6 — Data and backward compatibility

**Objective:** make schema/config/state changes safe.

Validate as applicable:

- migrations/upgrades;
- existing data behavior;
- defaults for old installations;
- rollback/uninstall behavior;
- API/config compatibility.

**Exit gate:** upgrade path is explicit and safe enough for the target environment.

## Phase 7 — Verification and regression

**Objective:** prove the focused change and important surrounding behavior.

Run available project checks:

- build;
- tests;
- syntax/lint/type/static analysis;
- migration validation;
- targeted manual/integration verification.

Also review the final diff for unrelated changes.

**Exit gate:** relevant checks pass or unverified areas are explicitly documented.

## Phase 8 — Documentation and handoff

**Objective:** leave the repository understandable for the next developer/AI session.

Update when applicable:

- `README.md`;
- `docs/PROJECT_MAP.md`;
- setup/config docs;
- API/data docs;
- release/migration notes.

Handoff must state:

- what existed before;
- what changed;
- what was reused;
- verification performed;
- remaining risks/TODOs.

## Definition of Done

A feature/change is complete when:

- [ ] repository and relevant existing code were inspected first;
- [ ] duplicate implementation was avoided;
- [ ] real entry/integration points were used;
- [ ] compatibility impact was considered;
- [ ] migrations/config changes are explicit where relevant;
- [ ] relevant verification was run;
- [ ] unrelated edits were excluded;
- [ ] documentation/project map was updated where needed;
- [ ] unresolved risks are recorded.
