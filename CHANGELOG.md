# Changelog

All notable Openboost changes are tracked here so developers and AI agents can understand what changed between versions without reconstructing history from individual commits.

Openboost uses Semantic Versioning unless a future documented policy replaces it.

## [Unreleased]

### Added
- Git/GitHub workflow skill requiring task branches, intentional commits and pull requests for normal GitHub-backed development.
- Semantic Versioning, immutable version tags and GitHub Release guidance.
- Release artifact rules for distributable OpenCart modules.
- Reusable changelog template and automated tag-to-release workflow for Openboost.

### Changed
- GitHub-backed development is now treated as a traceable lifecycle rather than direct edits to the default branch.

## Planned first release: 0.1.0

The first stable Openboost release will consolidate the initial OpenCart AI bootstrap, including:

- evidence-first OpenCart project analysis;
- living project architecture documentation (`Core`, `Services`, data ownership and runtime flows);
- OpenCart module development architecture;
- multilingual/i18n rules;
- OCMOD analysis and development rules;
- mobile-first frontend and modern responsive admin UI guidance;
- theme presets/custom themes/design-token guidance;
- living reusable OpenCart knowledge;
- Git/GitHub branch, PR, versioning and release workflow.

At release time, move the relevant `[Unreleased]` entries under `## [0.1.0] - YYYY-MM-DD`, ensure `VERSION` contains `0.1.0`, create tag `v0.1.0`, and create the GitHub Release from that tag.
