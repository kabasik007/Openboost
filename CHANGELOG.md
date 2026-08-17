# Changelog

All notable Openboost changes are tracked here so developers and AI agents can understand what changed between versions without reconstructing history from individual commits.

Openboost uses Semantic Versioning unless a future documented policy replaces it.

## [Unreleased]

### Added
- OpenCart deployment skill covering Git branch monitoring, incremental deployment, backups, health checks and rollback.
- Reusable local Python deployment agent with Git diff tracking and FTP/FTPS upload of `upload/` contents to the OpenCart root.
- HMAC-authenticated PHP server bridge for canonical OCMOD upserts, controlled cache actions, audit logging and optional OPcache reset.
- OpenCart 2.3 OCMOD refresh adapter that rebuilds generated modification files from base, file-based and enabled DB modifications.
- Reusable per-project deployment configuration and setup documentation.

### Changed
- OCMOD deployment guidance now requires a stable canonical `<code>` across versions; release version belongs in `<version>` and release metadata instead of changing ownership identity.
- Legacy versioned OCMOD rows may be cleaned only through explicit anchored owned-code patterns.
- OpenCart 2.3 deployment guidance treats modification refresh as a full generated-tree rebuild rather than deleting one generated file.
- Cache invalidation during deploy is modeled as explicit allowlisted profiles instead of blindly deleting every cache directory.
- GitHub/OpenCart deployment tasks are now routed through the deployment skill in addition to the existing Git/GitHub and OCMOD skills.

## [0.1.0] - 2026-08-17

### Added
- Evidence-first OpenCart project analysis and repository orientation.
- Living project architecture documentation with `Core`, `Services`, subsystem ownership, runtime flows, data ownership, OCMOD/events and `Where to look first` guidance.
- OpenCart module-development standard derived from OCFilter 4.8.2 architectural patterns and cleaned up for PHP 7.1+ projects.
- Multilingual/i18n guidance for admin/catalog language files, `language_id` data and multilingual SEO/content.
- OCMOD analysis, creation, refresh and conflict-debugging rules.
- Mobile-first frontend and modern responsive admin UI guidance.
- Theme presets, custom themes and centralized design-token/CSS-variable guidance for module UI.
- Living reusable OpenCart knowledge rules so Openboost improves during real development.
- Git/GitHub workflow skill requiring task branches, intentional commits and pull requests for normal GitHub-backed development.
- Semantic Versioning, immutable version tags and GitHub Release guidance.
- Release artifact rules for distributable OpenCart modules.
- Reusable changelog and release-checklist templates.
- Tag-driven GitHub Actions workflow for Openboost releases.

### Changed
- PHP 7.1+ is the default minimum for new Openboost-guided development; PHP 5.6 is legacy-only unless explicitly required.
- GitHub-backed development is treated as a traceable branch → PR → merge → version → tag → release lifecycle instead of direct edits to the default branch.
- Project architecture is treated as living code-backed documentation rather than one-time notes.

### Reference architecture
- Initial golden-reference analysis is based on user-provided OCFilter 4.8.2; the third-party package itself is not stored in Openboost.
