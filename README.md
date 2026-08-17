# Openboost

Openboost repository bootstrap.

This repository is prepared for AI-assisted development with a **repository-first** workflow: inspect what already exists, identify the real stack and entry points, reuse existing components, and only then implement changes.

## Start here

For any AI agent or coding assistant:

1. Read [`AGENTS.md`](./AGENTS.md).
2. Follow [`AI_BOOTSTRAP.md`](./AI_BOOTSTRAP.md) before implementing a task.
3. Keep [`docs/PROJECT_MAP.md`](./docs/PROJECT_MAP.md) aligned with the real repository structure.
4. Keep [`docs/ROADMAP.md`](./docs/ROADMAP.md) aligned with current work.
5. Treat code and configuration in the repository as the source of truth. Do not invent framework versions, entry points, database structure, deployment assumptions, or existing features.

## Current state

The repository was empty when this bootstrap was created. No application stack has been assumed yet.

When project files are added, the first AI session must scan them and replace the `unknown`/bootstrap-only state in the project map with evidence-backed facts.
