# Release Checklist

Use this checklist for a project that follows the Openboost Git/GitHub workflow. Adapt it to the target repository instead of copying irrelevant steps blindly.

## Scope

- [ ] Release scope is coherent and understood.
- [ ] All intended feature/fix PRs are merged or intentionally included.
- [ ] No unrelated work is being pulled into the release.

## Version

- [ ] Existing version source of truth was identified.
- [ ] New version follows the project's versioning policy (SemVer by default).
- [ ] Duplicate module/package version metadata matches the canonical version.
- [ ] Published version/tag is not being reused for different code.

## Documentation

- [ ] `CHANGELOG.md` / equivalent describes meaningful changes.
- [ ] `[Unreleased]` entries were moved into the release version/date where applicable.
- [ ] Upgrade or migration notes are documented.
- [ ] `docs/ARCHITECTURE.md` reflects architecture-significant changes.
- [ ] Compatibility requirements are clear (OpenCart/PHP/theme/languages where relevant).

## Validation

- [ ] Relevant automated checks pass.
- [ ] PHP syntax is valid under the supported minimum.
- [ ] Clean install path was checked when applicable.
- [ ] Upgrade/migration path was checked when applicable.
- [ ] OCMOD/event integration was refreshed/verified when applicable.
- [ ] Active languages were checked when applicable.
- [ ] Frontend/mobile/admin UI was checked when applicable.
- [ ] Final diff contains no unrelated or secret files.

## Packaging

- [ ] Package is built from the intended release commit.
- [ ] Archive structure matches the target OpenCart installer/version.
- [ ] Development/temp files are excluded.
- [ ] Secrets and environment-specific credentials are excluded.
- [ ] Package filename contains the correct version.
- [ ] Version inside the package matches the Git tag.

## Git / GitHub

- [ ] Release source is merged into the intended default/release branch.
- [ ] Release commit SHA is known.
- [ ] Tag is `vX.Y.Z` (or the project's established equivalent).
- [ ] Tag points to the intended release commit.
- [ ] GitHub Release exists for the immutable tag.
- [ ] Release notes are readable and include migration/compatibility warnings when needed.
- [ ] Required installable artifacts are attached.

## Handoff

Record:

```text
Version:
Tag:
Release commit:
GitHub Release:
Artifacts:
Compatibility:
Migration notes:
Known issues:
Rollback/previous stable version:
```
