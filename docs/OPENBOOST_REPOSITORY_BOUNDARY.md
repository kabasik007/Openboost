# Openboost Repository Boundary

Openboost `main` is a reusable AI bootstrap and OpenCart knowledge base. It is **not** the default home for experimental runtime tooling.

## What belongs in `main`

Prefer keeping the default branch focused on reusable, inspectable knowledge:

- `AGENTS.md` / bootstrap instructions;
- OpenCart analysis and architecture skills;
- module-development, i18n, OCMOD, UI/UX, Git/GitHub and deployment **rules**;
- architecture/reference documents;
- Markdown checklists and document templates;
- lightweight declarative examples when they explain a reusable contract without becoming an operational service.

## What does not move into `main` automatically

Do **not** merge experimental operational implementations merely because they produced useful lessons. Examples include:

- Python/Node/PHP deployment agents or daemons;
- FTP/SFTP watchers;
- privileged server bridges/endpoints;
- direct database bridge code;
- installers/updaters that execute against a live store;
- compiled executables or service wrappers;
- experimental CI/deployment automation tied to one prototype;
- credentials, environment-specific paths or production configuration.

Those belong in the target project, a dedicated tooling repository, or an explicitly named experimental/feature branch until the user deliberately promotes them.

## Knowledge extraction rule

When an experimental branch contains useful work, separate **knowledge** from **implementation**:

```text
experimental branch / real project
        ↓
inspect what actually worked
        ↓
extract reusable rules, hazards and contracts
        ↓
update Openboost skills/docs from a clean branch based on main
        ↓
leave operational runtime code in its own branch/repository
```

Do not merge a long-running experiment branch into `main` only to obtain its documentation.

If the branch also contains code changes, create a clean bootstrap branch from current `main` and port only the reusable documentation/instruction changes.

## Promotion gate for runtime tooling

Runtime tooling may enter Openboost `main` only when the user explicitly wants Openboost to ship and maintain that tool, and the promotion is deliberate.

Before promotion, establish at least:

- clear ownership and maintenance scope;
- supported OpenCart/PHP/platform versions;
- threat model and secret handling for privileged tooling;
- tests/CI appropriate to the implementation;
- rollback/failure behavior;
- documentation and configuration contract;
- versioning/release policy;
- evidence that the tool is reusable beyond one target project.

Without that gate, keep the implementation experimental and extract only the reusable knowledge.

## Branch handling

A feature branch may intentionally remain unmerged while work continues.

Do not delete or merge an active experiment simply to make the branch list tidy.

Before cleanup:

1. compare each branch with `main`;
2. identify whether it contains unique implementation work;
3. extract reusable bootstrap knowledge separately when useful;
4. merge only the clean bootstrap/documentation contribution;
5. delete only branches whose unique work is already merged or explicitly abandoned.

Published release tags remain immutable history even if later releases remove experimental files from the default branch.

## OpenCart deployment example

A deployment prototype can teach Openboost reusable rules such as:

- stable canonical OCMOD `<code>` across releases;
- release version belongs in `<version>` / package / Git tag;
- OpenCart 2.3 OCMOD refresh is a full generated-tree rebuild;
- legacy OCMOD cleanup must target explicitly owned codes;
- deployment should be incremental, reversible and health-checked;
- privileged bridge designs require authentication, replay protection and strict allowlists.

Those rules belong in Openboost skills.

A concrete Python deploy agent or PHP server bridge does **not** belong in `main` automatically; it can stay in its experiment branch until intentionally promoted.