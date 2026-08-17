# Openboost Roadmap

Openboost is a living OpenCart AI development bootstrap. This roadmap tracks the knowledge system itself, not a customer/store implementation.

## Phase 0 — Core bootstrap

Status: **done**

- `AGENTS.md` entry rules;
- `AI_BOOTSTRAP.md` session workflow;
- evidence-first analysis;
- existing-implementation-first policy;
- project map and roadmap foundation.

## Phase 1 — OpenCart core knowledge pack

Status: **done — initial version**

Deliverables:

- OpenCart project-analysis skill;
- module-development architecture skill;
- language/i18n skill;
- OCMOD skill;
- PHP 7.1+ default compatibility policy;
- automatic skill routing;
- required first analysis update to the user.

Reference source: OCFilter 4.8.2 architecture analysis.

## Phase 2 — Continuous refinement from real projects

Status: **active / ongoing**

After substantial OpenCart work:

1. identify reusable lessons;
2. distinguish universal vs project-specific behavior;
3. update the narrow skill/reference;
4. correct stale instructions;
5. record major policy evolution in `docs/OPEN_CART_LIVING_KNOWLEDGE.md`.

Exit gate: never final — this is the permanent improvement loop.

## Phase 3 — OpenCart 2.3 deep pack

Status: **planned when evidence accumulates**

Potential topics:

- exact 2.3 route/controller/model/view conventions;
- token and permission helpers;
- extension installer/modification behavior;
- TPL + custom Twig loaders;
- event differences;
- `url_alias`/SEO patterns;
- safe PHP 7.1 modernization for 2.3 projects;
- common Journal3 integration points.

Do not fill this pack from memory; build it from inspected real projects/reference source.

## Phase 4 — Journal3 compatibility skill

Status: **planned**

Candidate scope:

- Journal3 controller/model replacements;
- template/view behavior on OpenCart 2.3;
- module positions/layout integration;
- filters/search/checkout interception;
- asset loading;
- OCMOD conflicts;
- theme-safe UI work.

Create only after enough repeatable evidence exists.

## Phase 5 — Module packaging and release standard

Status: **planned**

Candidate deliverables:

- `.ocmod.zip` package layouts by OpenCart generation;
- version metadata;
- install/update/uninstall lifecycle;
- upgrade migrations;
- release notes;
- clean-install and upgrade test matrix;
- GitHub Actions/static syntax checks under PHP compatibility targets.

## Phase 6 — Module audit skill

Status: **planned**

A reusable audit should inspect:

- architecture separation;
- permissions;
- language completeness;
- schema/migrations;
- SQL safety;
- OCMOD fragility;
- events;
- multistore;
- template/theme compatibility;
- PHP minimum compatibility;
- install/update/uninstall safety;
- stale files/legacy code;
- duplicate functionality.

## Phase 7 — Compatibility packs

Status: **future**

Potential scoped packs:

- OpenCart 3.x;
- OpenCart 4.x;
- SEO modules;
- multistore;
- common checkout/search/filter modules.

Compatibility must be evidence-based and scoped. Openboost should not become a pile of speculative patches for software the user does not run.

## Definition of Done for a new Openboost knowledge update

- [ ] based on inspected code or confirmed project behavior;
- [ ] scope/version/theme is clear;
- [ ] does not contradict PHP 7.1+ baseline without explicitly changing policy;
- [ ] separates general rule from one-site workaround;
- [ ] added to the narrowest appropriate skill/reference;
- [ ] linked from the skill router if a new skill was created;
- [ ] no proprietary third-party source code copied into Openboost unnecessarily;
- [ ] instruction is actionable for the next AI agent;
- [ ] stale/conflicting guidance updated at the same time.
