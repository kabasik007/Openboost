---
name: opencart-ocmod
description: Rules for analyzing, creating, updating, and debugging OpenCart OCMOD modifications without fragile or unnecessary core patches.
---

# OpenCart OCMOD

Use this skill whenever an OpenCart task touches modification XML, extension installation, generated modified files, core integration points, or behavior that may have been injected into another file.

## First principle

**OCMOD is an integration tool, not the first place to put module business logic.**

Preferred order:

1. existing module API/hook;
2. OpenCart event;
3. module-owned adapter/wrapper;
4. OCMOD against a stable integration anchor;
5. direct core edit only in an intentionally maintained fork.

If an event can solve the problem cleanly, prefer it over an OCMOD patch.

## Before writing XML

Determine:

- exact OpenCart version;
- how this project installs modifications;
- whether modifications live in DB, `system/*.ocmod.xml`, package `install.xml`, or another project convention;
- whether a modification with the same code already exists;
- whether the target file is itself replaced/modified by Journal3, SEO, checkout, theme, or another extension;
- whether the desired behavior already exists in generated modification output.

Do not copy another extension's install strategy blindly.

## Basic modification identity

Use stable module-owned metadata:

```xml
<modification>
  <name>...</name>
  <code>vendor_module_feature</code>
  <version>...</version>
  <author>...</author>
  ...
</modification>
```

The `<code>` must be unique and stable enough to identify ownership during update/removal.

Do not delete or overwrite broad modification patterns belonging to other extensions.

## File targets

Use the narrowest reliable file target.

Prefer explicit paths for project-specific modules.

Pattern/wildcard targets may be useful for multi-version or multi-theme distribution, but their exact support and semantics depend on the OCMOD implementation. Use them only after confirming that the target project/reference already supports the syntax.

The OCFilter reference uses broad multi-target patterns to support many OpenCart/theme variants. Openboost should not make that complexity the default for one known project.

## Search anchors

A good `<search>` anchor is:

- stable across the exact target version;
- specific enough to match once or the intended number of times;
- semantically related to the insertion point;
- not dependent on whitespace that frequently changes;
- not a huge block of unrelated code.

Before committing an operation, search the target repository and confirm the anchor exists in the exact supported version.

Do not write an XML patch against remembered OpenCart source.

## Add positions

Prefer additive changes:

```text
before
after
```

Use `replace` only when replacement is truly required.

Replacement is more fragile because it assumes ownership of the original expression/block and can conflict with other extensions.

When using `replace`, keep the replacement scope as small as possible.

## Mark injected code

For non-trivial additions, bracket injected code with module-specific comments when the target language permits it:

```text
// <Module> start
...
// <Module> end
```

or equivalent HTML comments.

This makes generated modification output auditable and helps identify duplicate injection.

## Keep business logic out of XML

Bad OCMOD:

- hundreds of lines of business logic injected into core model/controller files;
- duplicated SQL in multiple operations;
- copied service/API logic inside CDATA;
- complicated UI rendering embedded in XML.

Better OCMOD:

- load a module model/service;
- call one well-named method;
- add one view data value;
- redirect a controller/module route;
- register an early module bootstrap only when needed.

The reference OCFilter often injects short calls into OpenCart product/category paths while keeping most filtering/SEO logic in module-owned classes. That separation is worth preserving.

## Core model query modifications

Patching `catalog/model/catalog/product.php` or equivalent is high risk because many filters/search/themes also modify product SQL.

Before changing a product query:

1. inspect every OCMOD targeting that model;
2. inspect Journal3/custom model replacements;
3. trace both list and total/count queries;
4. ensure the same filter constraints apply to count and result queries;
5. verify cache keys or SQL-based caching are invalidated/segmented correctly;
6. test search/category/manufacturer/special routes that share the model.

A query patch that changes results but not totals/pagination is incomplete.

## Template modifications

When OCMOD injects markup into templates:

- target the active template engine;
- check default and active theme paths;
- avoid inserting the same block twice through both controller and template hooks;
- preserve valid HTML structure;
- test empty/no-products states;
- test responsive behavior.

For distribution across TPL/Twig, maintain equivalent operations separately and test both.

## Menu/admin modifications

For an admin menu item, also verify:

- route exists;
- access permission exists;
- language file is loaded;
- token key is correct for the target OpenCart version;
- menu patch anchor exists;
- active admin theme/custom menu does not replace the target.

Do not treat successful XML refresh as proof the route is usable.

## Events and OCMOD lifecycle

An extension may use both events and OCMOD. Treat them as separate lifecycle components.

Install/update should be able to establish:

```text
files present
+ modification registered/refreshed
+ generated modification valid
+ events registered
+ permissions registered
+ schema installed/migrated
```

If any one is missing, the module may appear partially installed.

## Refresh behavior

Know the target version's modification refresh route/API.

OpenCart 2.x and 3.x route/token conventions differ. Do not hard-code `token`/`user_token` logic across every call; use a version adapter in multi-version modules.

Do not automatically self-request the modification refresh endpoint via cURL unless the project needs that behavior and session/error handling is understood. A simpler explicit refresh/update path is preferable when adequate.

## Runtime truth

When debugging OCMOD, original source is not necessarily runtime source.

Trace:

```text
XML definition
  ↓
modification registration/source
  ↓
refresh result
  ↓
generated modified file
  ↓
actual route execution
```

If `system/modification/` is available, inspect the generated file directly.

If generated files are not part of the repository, use the platform's modification logs/cache and reproduce refresh locally where possible.

## Conflicts

Typical conflict causes:

- two modifications search/replace the same expression;
- one extension replaces an anchor another extension expects;
- operation order changes the generated source;
- active theme/controller differs from the target path;
- XML anchor matches zero or multiple times;
- stale modification cache remains after update;
- old modification code is still in DB alongside a file-based modification.

When diagnosing a conflict, list all modifications targeting the same file before rewriting anchors.

## Compatibility packs

Third-party patches should be isolated conceptually from the module's core integration.

Examples:

```text
base OpenCart integration
Journal3 compatibility
SEO module compatibility
custom theme compatibility
search module compatibility
```

Do not add every known compatibility patch to a project-specific extension. Only include installed or explicitly supported systems.

## Update/removal

When replacing your own modification:

- use a unique code prefix;
- remove only codes/files owned by the extension;
- preserve unrelated modifications;
- refresh once after the set of changes where possible;
- verify old generated injections are gone.

Never execute broad deletion such as removing arbitrary modification rows because their code merely contains a common generic word.

## Package strategy

OpenCart extension packaging conventions vary by version and installer workflow.

Before building `.ocmod.zip`, inspect a working extension for the exact target OpenCart version and project. Typical packages may use an `upload/` tree and an install modification file, while some mature modules ship a module-owned `system/<module>.ocmod.xml` and manage refresh through their own lifecycle.

The OCFilter 4.8.2 reference uses the latter style. Treat that as one proven strategy, not the universal package format.

## Validation checklist

For every OCMOD change, verify:

- XML parses;
- modification code is unique;
- every target file exists for the supported version;
- every search anchor matches the intended location;
- refresh completes without modification errors;
- generated code contains one expected injection;
- generated PHP syntax is valid;
- admin/catalog request runs without fatal errors;
- affected totals/pagination/cache remain consistent;
- active theme works;
- relevant third-party integration works;
- uninstall/update removes or replaces the extension's own injection cleanly.

## Reference lessons from OCFilter 4.8.2

Useful patterns:

- use short injected calls while keeping core logic under `system/library/ocfilter/`;
- handle route/token/template differences in an adapter;
- check whether runtime bootstrap actually exists after refresh;
- isolate menu, product model, category rendering, SEO, and theme compatibility operations;
- pair filtering changes with totals/pagination behavior;
- mark injected sections for diagnosis.

Patterns not to generalize automatically:

- very broad compatibility targets for many unrelated third-party extensions;
- large numbers of fragile search anchors in a project-specific module;
- automatic deletion of old modifications unless ownership is certain;
- self-refresh through HTTP as the default installation mechanism.