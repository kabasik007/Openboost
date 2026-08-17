---
name: git-github-workflow
description: Git/GitHub development workflow for Openboost-assisted projects: task branches, intentional commits, pull requests, SemVer, changelog, tags, GitHub Releases, release artifacts, and immutable version history.
---

# Git / GitHub Workflow, Versioning and Releases

Use this skill whenever the target project is stored in Git and has a GitHub remote, or when the user asks to publish, version, release, tag, or maintain project history.

The goal is traceability: a developer should be able to answer **what changed, why it changed, in which branch/PR, and in which released version** without reconstructing history from random commits.

## Core policy

When write access to a GitHub repository is available:

1. Inspect repository status, default branch, existing branch conventions, tags/releases, CI and version files.
2. **Do not begin normal implementation directly on `main` / `master` / the default branch.**
3. Create or reuse one task branch for the coherent change.
4. Make intentional commits with meaningful messages.
5. Run relevant validation.
6. Push the task branch.
7. Open a pull request before integration into the default branch.
8. Update version/changelog when the change belongs to a release.
9. Create an immutable version tag from the release commit.
10. Create a GitHub Release with readable release notes and required distributable artifacts.

Direct writes to the default branch are an exception, not the normal workflow. Use them only when the repository explicitly follows that model or the user explicitly requests an emergency/direct change.

## Start-of-task Git inspection

Before editing a Git-backed target project, determine:

```text
repository remote
GitHub owner/repo
default branch
current branch
working tree state
existing task branch/PR for this work
branch naming convention
latest tags/releases
version source of truth
CHANGELOG presence
CI/checks
release/package workflow
```

Do not create a second branch if the current branch is already the intended task branch.

Do not discard, stage, or overwrite unrelated user changes.

## Branch-per-task rule

For a new coherent task starting from the default branch, create a branch first.

Recommended naming when the project has no stronger convention:

```text
feat/<short-topic>
fix/<short-topic>
refactor/<short-topic>
docs/<short-topic>
chore/<short-topic>
hotfix/<short-topic>
release/v1.2.3
```

For agent-created branches, `agent/<short-topic>` is also acceptable when that convention is already being used.

Examples:

```text
feat/marketplace-seller-reviews
fix/ocmod-language-routing
refactor/import-core-services
chore/php71-compatibility
release/v1.4.0
```

Branch names must describe the change, not the person or date alone.

### One branch = one coherent scope

Do not mix unrelated work such as:

```text
seller reviews
+ image importer
+ unrelated footer redesign
+ random dependency upgrade
```

inside one branch/PR unless they are truly one release-level change.

## Commit history

Commits should communicate intent.

Preferred style when the repository has no existing convention:

```text
feat: add seller review replies
fix: preserve language_id in SEO aliases
refactor: move importer logic into services
ui: add mobile-first marketplace filters
docs: update architecture map
chore: prepare v1.4.0 release
test: cover price import fallback
```

Small related edits may be one commit. Larger work should use several logical commits when that improves reviewability.

Avoid messages such as:

```text
update
fix
changes
final
final2
working
```

Do not rewrite public/shared history casually. Once a commit/tag/release is published and depended on, prefer a new corrective commit/version.

## Pull request policy

A task branch should normally become a PR before merge.

PR description should state:

- what changed;
- why;
- existing implementation reused;
- architecture/data/OCMOD/i18n/UI implications where relevant;
- migration or compatibility notes;
- tests/checks performed;
- known risks or unverified areas;
- screenshots or before/after notes for meaningful UI changes when available.

Draft PR is appropriate while work is incomplete. Mark ready only after the implementation and relevant checks are complete.

Before merge:

```text
branch is based reasonably close to current default
relevant checks pass
review comments addressed
architecture docs updated if needed
CHANGELOG/version updated if this is a release boundary
no unrelated files in diff
```

Follow the repository's configured merge strategy. If none is established, squash merge is a reasonable default for small task branches; preserve multiple commits when their history is intentionally useful.

## Version source of truth

Before inventing a new version mechanism, search for an existing one:

```text
VERSION
composer.json
package.json
module constants
install.xml / extension metadata
README badges/docs
release scripts
build config
```

Prefer **one canonical version source** and synchronize duplicate metadata only where the platform/package format requires it.

For a standalone OpenCart module repository with no version mechanism, a root `VERSION` file is a simple acceptable canonical source.

Do not allow these to drift silently:

```text
VERSION = 1.4.0
module constant = 1.3.2
package filename = 1.2.8
Git tag = v1.5.0
```

## Semantic Versioning default

When the project has no stronger versioning policy, use Semantic Versioning:

```text
MAJOR.MINOR.PATCH
```

### PATCH

Bug fix / compatibility fix / small non-breaking correction.

```text
1.4.2 → 1.4.3
```

### MINOR

Backward-compatible feature or meaningful new capability.

```text
1.4.3 → 1.5.0
```

### MAJOR

Breaking change, incompatible API/data/config contract, or intentionally unsupported upgrade path.

```text
1.5.0 → 2.0.0
```

### Pre-releases

Use explicit pre-release versions when testing before stable release:

```text
2.0.0-alpha.1
2.0.0-beta.1
2.0.0-rc.1
```

Do not call unstable builds stable merely to simplify numbering.

## CHANGELOG policy

For projects/modules that are distributed, deployed, or maintained over time, keep a root `CHANGELOG.md` unless an equivalent release-history document already exists.

Preferred structure:

```text
# Changelog

## [Unreleased]
### Added
### Changed
### Fixed
### Removed
### Security

## [1.4.0] - 2026-08-17
### Added
- ...
```

The changelog should describe **user/developer-visible changes**, not every internal line edit.

Good:

```text
- Added custom color themes for the seller widget.
- Fixed Ukrainian SEO URL lookup by language_id.
- Migrated importer execution to the shared service layer without changing existing settings.
```

Bad:

```text
- Changed controller.php.
- Edited 14 lines.
```

Update `[Unreleased]` during development when the change is meaningful. At release time, move the relevant entries under the released version/date.

If the project already uses another changelog convention, preserve it rather than creating a duplicate.

## Release readiness

Do not create a GitHub Release merely because a commit exists.

Create a release when there is a coherent deployable/distributable version, for example:

- a completed module feature/fix intended for installation;
- a production deployment milestone;
- a bugfix that users need to download/update;
- a meaningful Openboost version;
- an agreed product milestone.

For a module repository, a small completed bugfix may reasonably produce a PATCH release.

For a large store monorepo with frequent commits, release cadence may follow deployment milestones rather than every PR. Detect and preserve the project's real release model.

## Release sequence

Preferred release sequence:

```text
feature/fix branches
        ↓
PR(s)
        ↓
merge to default branch
        ↓
release preparation if needed
        ↓
VERSION + CHANGELOG finalized
        ↓
checks/build/package
        ↓
tag vX.Y.Z on the release commit
        ↓
GitHub Release vX.Y.Z
        ↓
attach installable artifacts
```

Never tag an unverified random work-in-progress commit as a stable release.

## Tags are immutable history

Stable tags use:

```text
v1.0.0
v1.1.0
v1.1.1
```

Do not silently move an already published tag to a different commit.

If `v1.2.0` contains a mistake after publication:

```text
fix it
→ release v1.2.1
```

not:

```text
delete/move v1.2.0 and pretend history changed
```

This makes rollback, debugging and customer support reliable.

## GitHub Release notes

A GitHub Release should summarize the version in readable form.

Include as applicable:

```text
Highlights
Added
Changed
Fixed
Upgrade / migration notes
Compatibility
Known issues
Artifacts
```

For breaking releases, put migration/compatibility warnings near the top.

Release notes may be generated from PRs/commits, but AI must clean them into useful product-facing notes instead of dumping raw commit messages.

## OpenCart module release artifacts

For a distributable OpenCart module, produce a predictable package name such as:

```text
<module>-v1.4.0.ocmod.zip
<module>-v1.4.0.zip
```

Use the package format expected by the target OpenCart generation/module installer.

Before attaching the artifact:

- build/package from the release commit;
- ensure temporary/dev files are excluded;
- ensure secrets/config credentials are excluded;
- validate archive structure;
- verify install/upgrade path when possible;
- ensure version metadata inside the package matches the Git tag/release.

If the repository already has GitHub Actions packaging, reuse it.

## Release automation

Automation is encouraged for repetitive release mechanics.

Useful automation:

```text
push tag v*.*.*
→ verify VERSION matches tag
→ run checks
→ build/package
→ create GitHub Release
→ upload artifacts
```

Do not automate away product decisions such as whether a breaking change deserves a MAJOR version.

A reusable minimal release workflow may live in the project or be adapted from an Openboost template.

## Hotfix workflow

For an urgent released bug:

```text
latest supported release/default branch
→ hotfix/<topic>
→ fix + focused validation
→ PR
→ merge
→ PATCH version bump
→ changelog
→ tag
→ release
```

Do not patch the release artifact manually without committing the corresponding source change.

## Architecture and release history

If a release materially changes project architecture:

- update `docs/ARCHITECTURE.md` in the same development scope;
- mention important migration/ownership changes in release notes;
- keep the release tag tied to the architecture docs that describe that code state.

This makes historical architecture recoverable by checking out a tag.

## GitHub unavailable or read-only

If GitHub write access is unavailable:

- do not claim a branch/PR/release was created;
- still prepare the code and exact proposed branch/version/changelog/release steps when useful;
- report the limitation clearly in the handoff.

If Git is available locally but GitHub is not, commits/branches may still be prepared locally according to the user's environment and permissions.

## Handoff requirements

For GitHub-backed work, final handoff should state the relevant subset of:

```text
branch:
commits:
PR:
base branch:
validation:
version before → after:
CHANGELOG updated:
tag:
GitHub Release:
release artifact(s):
remaining release/deploy action:
```

Never say “released” when only a branch or PR exists.

## Definition of done for a released change

A release is done when:

- [ ] work did not bypass the normal branch/PR workflow without a reason;
- [ ] intended changes are merged into the release source branch;
- [ ] validation is complete enough for the target project;
- [ ] canonical version is correct;
- [ ] duplicate version metadata is synchronized;
- [ ] changelog/release notes describe the real changes;
- [ ] architecture docs reflect material structural changes;
- [ ] immutable tag points at the intended release commit;
- [ ] GitHub Release exists for the tag when the project uses releases;
- [ ] required installable/build artifacts are attached and version-matched;
- [ ] known migration/compatibility risks are documented.
