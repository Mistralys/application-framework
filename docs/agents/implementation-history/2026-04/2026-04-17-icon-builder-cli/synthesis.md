# Project Synthesis Report — Icon Builder CLI

**Plan:** `2026-04-17-icon-builder-cli`
**Date:** 2026-04-17
**Status:** COMPLETE
**Work Packages:** 16 / 16 COMPLETE
**Total Pipeline Stages Passed:** 60

---

## Executive Summary

This session delivered a complete, automated **icon-method code-generation pipeline** for the Application Framework and the HCP Editor. The feature replaces what was previously a manual (or migration-script-based) process for keeping the `UI_Icon.php`, `icon.js`, `CustomIcon.php`, and `custom-icon.js` accessor-method regions in sync with the `icons.json` / `custom-icons.json` source files.

The implementation spans two repositories and two namespaces:

- **Runtime layer** (`UI\Icons`): `IconInfo` value object + `IconCollection` singleton registry. Used at application runtime for icon lookup.
- **Build-time layer** (`Application\Composer\IconBuilder`): `IconDefinition`, `IconsReader`, `AbstractLanguageRenderer`, `PHPRenderer`, `JSRenderer`, and the `IconBuilder` orchestrator. Used exclusively during `composer rebuild-icons` / `composer build`.
- **Integration**: Both the Application Framework and HCP Editor `ComposerScripts` classes were wired up with a `rebuildIcons()` method, and standalone `composer rebuild-icons` scripts were registered in all relevant `composer.json` files.
- **Verification**: A full integration test (WP-014) confirmed idempotency across both builds; a PHPStan pass (WP-015) confirmed zero new static-analysis errors across 2,097 files.

---

## Deliverables

### New Classes

| Class | Namespace | File |
|---|---|---|
| `IconInfo` | `UI\Icons` | `src/classes/UI/Icons/IconInfo.php` |
| `IconCollection` | `UI\Icons` | `src/classes/UI/Icons/IconCollection.php` |
| `IconDefinition` | `Application\Composer\IconBuilder` | `src/classes/Application/Composer/IconBuilder/IconDefinition.php` |
| `IconsReader` | `Application\Composer\IconBuilder` | `src/classes/Application/Composer/IconBuilder/IconsReader.php` |
| `AbstractLanguageRenderer` | `Application\Composer\IconBuilder` | `src/classes/Application/Composer/IconBuilder/AbstractLanguageRenderer.php` |
| `PHPRenderer` | `Application\Composer\IconBuilder` | `src/classes/Application/Composer/IconBuilder/PHPRenderer.php` |
| `JSRenderer` | `Application\Composer\IconBuilder` | `src/classes/Application/Composer/IconBuilder/JSRenderer.php` |
| `IconBuilder` | `Application\Composer\IconBuilder` | `src/classes/Application/Composer/IconBuilder/IconBuilder.php` |

### New Tests

| File | Tests | Assertions |
|---|---|---|
| `tests/AppFrameworkTests/Composer/IconBuilder/IconBuilderTest.php` | 11 | 33 |
| `tests/AppFrameworkTests/UI/IconCollectionTest.php` | 14 | 234 |
| **Total** | **25** | **267** |

### Modified Files (Integration)

| File | Change |
|---|---|
| `src/classes/Application/Composer/ComposerScripts.php` | Added `rebuildIcons()` / `doRebuildIcons()` + call in `build()` |
| `composer.json` (framework) | Added `rebuild-icons` script entry |
| `src/classes/Application/Composer/README.md` | Full IconBuilder subpackage documentation added |
| `src/classes/UI/Icons/README.md` | New module README |
| `src/classes/UI/README.md` | Icons submodule row added |
| `AGENTS.md` (framework) | Build Quick Reference updated |
| HCP Editor: `assets/classes/Maileditor/Composer/ComposerScripts.php` | Added `rebuildIcons()` + call in `build()` / `buildDEV()` |
| HCP Editor: `composer.json` + `composer/composer-prod.json` | Added `rebuild-icons` script entry (both variants) |
| HCP Editor: `assets/classes/Maileditor/CustomIcon.php` | Added `/* START METHODS */` / `/* END METHODS */` markers + Code Generation docblock |
| HCP Editor: `themes/default/js/ui/custom-icon.js` | Added markers + code generation JSDoc |
| HCP Editor: `AGENTS.md` | Build Scripts table updated; Manifest Maintenance Rules row added |

---

## Metrics

| Metric | Value |
|---|---|
| Work packages completed | 16 / 16 |
| New test files | 2 |
| New tests | 25 |
| New assertions | 267 |
| Tests failed | 0 |
| PHPStan files analyzed | 2,097 |
| PHPStan new errors | 0 |
| Pre-existing PHPStan errors | 1 (DBHelper/FetchMany.php — unrelated) |
| WPs requiring rework | 2 (WP-007, WP-014) |
| Total rework cycles | 4 |

### Rework Summary

| WP | Cause | Resolution |
|---|---|---|
| WP-007 | Code review FAIL: `file_put_contents()` return value discarded — silent write failure produced false SUCCESS | Added `ERROR_WRITE_FAILED = 82304`, `@file_put_contents()` result check, and a new unit test for write failure via `chmod(0444)` fixture |
| WP-014 (QA pass 1) | WP-010/WP-011 marker changes never committed to the HCP Editor branch | QA agent committed the missing markers (commit `785475e62`) |
| WP-014 (review pass 1) | WP-008 framework integration (`rebuildIcons()`, `doRebuildIcons()`, `composer.json` entry) absent from codebase despite being PASS in WP-008 | Developer re-applied the WP-008 integration; QA re-verified AC-2 with actual execution |

---

## Reviewer-Applied Fixes

| WP | Fix |
|---|---|
| WP-006 | `renderSetTypeArgs()` duplicated identically in `PHPRenderer` and `JSRenderer` — extracted to `AbstractLanguageRenderer` as `protected` |
| WP-007 | Inline comment added on `@file_put_contents()` explaining the suppression rationale |
| WP-008 | `(string)realpath(...)` replaced with `rtrim(FolderInfo::factory(...)->getPath(), '/')` for consistency with existing class pattern |
| WP-014 | `IconCollection::getByID()` docblock corrected — erroneous `{@see self::normaliseID()}` reference (private method, inaccessible to callers) replaced with explicit `str_replace()` example |

---

## Strategic Recommendations

### Gold Nuggets

1. **`normaliseID()` is duplicated across `IconCollection` and `IconsReader`.**
   Both classes independently implement `str_replace(array('-',' '), '_', $id)`. At two sites this is acceptable (extracting it would couple unrelated namespaces). If a third consumer appears, a shared `IconIDNormaliser` utility or a trait should be created.

2. **`IconCollection::getByID()` throws bare `\RuntimeException` — not the project's `ApplicationException`.**
   This was flagged in WP-003, WP-014, and WP-015. The deviation is intentional for programmer-error scenarios, but it is undocumented. Module-level CTX documentation should note the exception type, the message format, and the recommendation to call `idExists()` before `getByID()` to avoid it.

3. **`ERROR_START_MARKER_NOT_FOUND` is reused for the missing-end-marker case.**
   The error message text correctly identifies the absent marker, but the constant name is misleading. Renaming to `ERROR_MARKER_NOT_FOUND` (or adding a separate `ERROR_END_MARKER_NOT_FOUND`) would make error trapping unambiguous. This is a public API change requiring a test update — defer to a dedicated `IconBuilder` cleanup WP.

4. **`IconCollection` singleton has no reset mechanism.**
   The private `self::$instance` cannot be reset between test runs, preventing isolation tests for edge cases (missing `custom-icons.json`, alternative APP_ROOT paths). A `@internal`-scoped `static resetInstance()` method would eliminate this gap without affecting production callers.

5. **Pre-existing PHPStan `method.childReturnType` in `DBHelper/FetchMany.php`.**
   This error predates this feature (commit `bec3f1a6`) and was not worsened. It should be resolved in a dedicated `DBHelper` cleanup WP to keep the codebase PHPStan-clean.

6. **`composer.json` / `composer/composer-prod.json` sync requirement is now documented.**
   WP-013 surfaced this previously undocumented convention; the AGENTS.md Manifest Maintenance Rules table was updated. Agents should be aware that any new Composer script must be added to both variants.

---

## Test Coverage Gaps (Open)

The following test files were noted as missing and should be created in follow-up WPs:

| File | Covering | Priority |
|---|---|---|
| `tests/AppFrameworkTests/UI/IconInfoTest.php` | `IconInfo` value object (camelCase conversion, createIcon factory) | Low |
| `tests/AppFrameworkTests/Application/Composer/IconsReaderTest.php` | `IconsReader` (count, spinner exclusion, ID normalisation, sort, missing file) | Medium |
| `tests/AppFrameworkTests/Composer/AbstractLanguageRendererTest.php` | `AbstractLanguageRenderer::render()` template method, `toPascalCase()` | Low |

---

## Next Steps

1. **Commit and push** all feature changes on the `feature-icon-builder` branch in both repositories (framework + HCP Editor).
2. **Run `composer rebuild-icons`** once post-merge on both sides to confirm the baseline state is stable and generate any final ID normalisation changes (e.g. the `Icon type methods` → `Icon methods` region comment rename in committed files).
3. **Create a follow-up WP** for the `DBHelper/FetchMany.php` PHPStan fix (pre-existing, isolated).
4. **Create a follow-up WP** to add `IconsReaderTest.php` (medium priority coverage gap).
5. **Update CTX documentation** for the `UI\Icons` module once `composer build` regenerates `.context/` — verify that `IconCollection::getByID()` exception behaviour is described.
6. **Consider a follow-up WP** for the `ERROR_START_MARKER_NOT_FOUND` → `ERROR_MARKER_NOT_FOUND` rename if API consumers need clear error discrimination.
