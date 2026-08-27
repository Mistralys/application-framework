# Project Synthesis — Module Info Parser Audit

**Plan:** `2026-04-20-module-info-parser-audit`
**Date:** 2026-04-20
**Status:** COMPLETE — all 8 work packages passed all pipeline stages

---

## Executive Summary

This session delivered a focused audit and refactoring of the `ModulesOverview` and `KeywordGlossary` subsystems across both the Application Framework and HCP Editor. The work spanned five code changes (WP-001 through WP-007) plus an integration validation pass (WP-008), and was driven by three goals:

1. **Harden diagnostic output** — `ModuleInfoParser` was silently echoing and re-throwing exceptions. All error paths are now routed through `BuildMessages`, returning `null` on failure.
2. **Eliminate duplication** — `ReadmeOverviewParser` was duplicated in HCP Editor; `KeywordGlossaryGenerator` was re-implementing YAML parsing that `ModuleInfoParser` already handles; `ModuleJsonExportGenerator` in HCP Editor contained ~180 lines of logic that now lives in a framework base class.
3. **Improve architecture** — `KeywordGlossaryBuilder` was extracted from `KeywordGlossaryGenerator` (SRP), and a new framework `ModuleJsonExportGenerator` with a Template Method hook design now serves as the base for the HCP Editor thin subclass.

All changes were validated against full build runs in both projects. No content regressions were found. The HCP Editor now ships six previously undocumented framework modules in its `modules-overview.md`, and `modules.json` now includes `glossarySections` data from `TenantListProvider` via `DecorateGlossaryEvent` — functional for the first time.

---

## Files Changed

| File | WP | Change |
|---|---|---|
| `src/classes/Application/Composer/ModulesOverview/ModuleInfoParser.php` | WP-001 | echo + throw → BuildMessages::addError/addWarning + return null |
| `src/classes/Application/Composer/ModulesOverview/ReadmeOverviewParser.php` | WP-002 | New (moved from HCP Editor; now `final`) |
| `tests/AppFrameworkTests/Composer/ModulesOverview/ReadmeOverviewParserTest.php` | WP-002 | New (moved from HCP Editor; adapted to ApplicationTestCase) |
| `src/classes/Application/Composer/KeywordGlossary/KeywordGlossaryGenerator.php` | WP-004, WP-005 | Delegates YAML parsing to ModuleInfoParser; delegates glossary build to KeywordGlossaryBuilder |
| `src/classes/Application/Composer/KeywordGlossary/KeywordGlossaryBuilder.php` | WP-005 | New — keyword deduplication, casing, sorting, conflict-warning logic extracted from generator |
| `src/classes/Application/Composer/ModulesOverview/ModuleJsonExportGenerator.php` | WP-006 | New — framework base class with Template Method hook design |
| `assets/classes/Maileditor/Composer/ModuleJsonExport/ModuleJsonExportGenerator.php` | WP-003, WP-007 | Dead method removed; reduced from ~180 lines to ~35-line thin subclass |
| `docs/agents/project-manifest/constraints.md` | WP-004 | New section: module-context.yaml required fields and silent-skip behaviour |
| `src/classes/Application/Composer/README.md` | WP-001, WP-002, WP-006 | Added ModuleInfoParser, ReadmeOverviewParser, ModuleJsonExportGenerator documentation |
| `.context/modules/composer/*.md` | WP-001–WP-007 | CTX documentation regenerated to reflect all changes |

---

## Metrics

| WP | Tests Passed | Tests Failed | Notes |
|---|---|---|---|
| WP-001 | 14 | 0 | ModulesOverviewGeneratorTest + ModulesOverviewRendererTest |
| WP-002 | 7 | 0 | ReadmeOverviewParserTest (7 tests, 11 assertions) |
| WP-003 | — | — | Static verification only (dead code removal) |
| WP-004 | 45 | 0 | 34 framework + 11 HCP Editor KeywordGlossary tests |
| WP-005 | 34 | 0 | KeywordGlossaryGeneratorTest (indirect coverage of builder) |
| WP-006 | 59 | 0 | ModulesOverview (25) + KeywordGlossary (34) |
| WP-007 | 26 | 0 | ModuleInfo (2) + ModulesOverview (13) + KeywordGlossary (11) |
| WP-008 (integration) | 152 | 0 | Full Composer-module suite + HCP Editor relevant tests |
| **Total** | **337** | **0** | |

PHPStan: zero errors introduced. One pre-existing error in `DBHelper/FetchMany.php` (return type incompatibility) confirmed unrelated to this plan.

---

## Architecture Accomplishments

### Template Method Design — ModuleJsonExportGenerator

The new framework `ModuleJsonExportGenerator` establishes a clean extension point via two protected hook methods:

- `resolveModuleSource(ModuleInfo): string` — default returns composer package; HCP Editor overrides to classify `mistralys/*` as `'framework'` vs `'hcp-editor'`.
- `resolveModuleBrief(ModuleInfo, string): ?string` — default looks for `README-Brief.md`; override for custom brief resolution.

The HCP Editor subclass is now ~35 lines. This pattern should be used as the reference for any future generator that needs application-level customisation.

### SRP Extraction — KeywordGlossaryBuilder

The keyword deduplication, first-seen casing preservation, module ID merging, and conflict-warning logic has been extracted into `KeywordGlossaryBuilder`. The immutable `addModuleId()` update pattern, static `usort` closure, and `@var` generic annotation used here are good reference patterns for this module.

### Consistent Diagnostics — BuildMessages

`ModuleInfoParser::parseFile()` is now the canonical framework point for YAML-to-`ModuleInfo` conversion. It emits `BuildMessages::addError()` for YAML parse failures and `BuildMessages::addWarning()` for missing/incomplete `moduleMetaData`. All callers (`KeywordGlossaryGenerator`, `ModulesOverviewGenerator`, and the new `ModuleJsonExportGenerator`) benefit from this consistently — no direct `Yaml::parseFile()` calls remain in any of these generators.

---

## Strategic Recommendations (Gold Nuggets)

These are the highest-value follow-up items surfaced during the session, ranked by impact:

### 1. Add three missing unit tests (medium priority)

Three test gaps were flagged consistently across multiple WPs and confirmed as carry-forward items in WP-008:

| Class | Missing coverage | Recommended test file |
|---|---|---|
| `KeywordGlossaryBuilder` | Conflict-warning path (same keyword, different context) | `tests/AppFrameworkTests/Composer/KeywordGlossary/KeywordGlossaryBuilderTest.php` |
| Framework `ModuleJsonExportGenerator` | `generate()` end-to-end, `$includeAll` behaviour, hook override | `tests/AppFrameworkTests/Composer/ModulesOverview/ModuleJsonExportGeneratorTest.php` |
| HCP Editor `ModuleJsonExportGenerator` | `resolveModuleSource()` classification (framework vs hcp-editor) | `tests/MailEditorTests/Composer/ModuleJsonExport/ModuleJsonExportGeneratorTest.php` |

### 2. Migrate echo calls in ModuleJsonExportGenerator::generate() to $onProgress (low priority)

`KeywordGlossaryGenerator` uses an optional `$onProgress` callable for progress output. The new framework `ModuleJsonExportGenerator::generate()` uses direct `echo` — creating an inconsistency within the same module. A follow-up WP should align the API.

### 3. Extract private SOURCE constant in ModuleInfoParser (low priority)

The string literal `'ModuleInfoParser'` appears three times in `parseFile()` as the `BuildMessages` source label. A `private const SOURCE = 'ModuleInfoParser'` would make a future rename a single-point change. Small tidy-up; the class is `final` and stable.

### 4. Fix pre-existing PHPStan error in DBHelper/FetchMany.php (low priority)

A return type incompatibility on `FetchMany::fetch()` was flagged as pre-existing across multiple WPs (confirmed in WP-001, WP-005). This should be addressed in a dedicated clean-up WP to keep the PHPStan baseline clean.

### 5. Consider making ReadmeOverviewParser final in CTX docs (done)

The WP-008 integration review applied a Fix-Forward adding `final` to `ReadmeOverviewParser` (consistent with `ModuleInfoParser`). This is already shipped.

---

## Behaviour Change to Note

A **behaviour change** was introduced in WP-004 and documented in `constraints.md`:

> `module-context.yaml` files that previously had `id` and `keywords` but lacked `label` or `description` would have their keywords included in the glossary. They are now **silently skipped** (a `BuildMessages` warning is emitted) because `ModuleInfoParser` requires all three fields before returning a `ModuleInfo`.

All existing module-context.yaml files in both projects already include all required fields — no content regressions were observed in WP-008. New modules must include `id`, `label`, and `description`.

---

## DEV Build Path Note (Non-Regression)

In DEV mode, framework module source paths in `modules-overview.md` appear as absolute filesystem paths (e.g., `Users/smordziol/Webserver/...`) because `ModuleContextFileFinder::normalizePath()` calls `realpath()` which resolves the DEV symlink. This pre-existed before this plan — the old `ModulesOverviewGenerator::resolveSourcePath()` was identical code. PROD builds produce the correct relative `vendor/mistralys/...` paths.

---

## Next Steps for Planner

1. **Create a follow-up WP** to add the three missing unit tests (see Strategic Recommendations §1).
2. **Create a follow-up WP** to migrate `ModuleJsonExportGenerator::generate()` echo calls to the `$onProgress` callable pattern.
3. **Create a clean-up WP** for the pre-existing PHPStan error in `DBHelper/FetchMany.php`.
4. (Optional) **Create a tidy-up WP** to add `private const SOURCE = 'ModuleInfoParser'` to `ModuleInfoParser`.
5. (Optional) **Consider a follow-up WP** in HCP Editor to migrate `ModuleJsonExportGenerator::buildGlossary()` to use `KeywordGlossaryBuilder` — still has a manual duplicate of the deduplication loop (flagged in WP-005 implementation debt note).
