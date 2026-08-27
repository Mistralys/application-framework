# Plan — Module Info Parser Audit: Follow-Up Rework

## Summary

Address four actionable items surfaced during the Module Info Parser Audit synthesis (`2026-04-20-module-info-parser-audit`). The work adds three missing unit test files, migrates direct `echo` calls to a `$onProgress` callable pattern in `ModuleJsonExportGenerator`, extracts a `SOURCE` constant in `ModuleInfoParser`, and fixes a pre-existing PHPStan return-type incompatibility in `DBHelper_FetchMany::fetch()`.

A fifth synthesis item (HCP Editor `buildGlossary()` dedup loop) was verified as **already resolved** — the HCP Editor subclass is a thin 29-line class with no `buildGlossary()` override.

## Architectural Context

### Composer Build Subsystem (Application Framework)

All source classes live under `src/classes/Application/Composer/` and share these conventions:

- **Base test class:** `AppFrameworkTestClasses\ApplicationTestCase`
- **Fixture strategy:** Temporary directories with synthetic `module-context.yaml` files (see `KeywordGlossaryGeneratorTest` and `ModulesOverviewGeneratorTest`)
- **Progress output:** Generators accept an optional `?callable $onProgress` parameter (established pattern in `KeywordGlossaryGenerator`)
- **Diagnostics:** All parse errors and warnings route through `Application\Composer\BuildMessages`

### Key Files

| File | Role |
|---|---|
| `src/classes/Application/Composer/KeywordGlossary/KeywordGlossaryBuilder.php` | SRP-extracted glossary builder — dedup, conflict detection, sorting |
| `src/classes/Application/Composer/ModulesOverview/ModuleJsonExportGenerator.php` | Framework base class — Template Method pattern with `resolveModuleSource()` / `resolveModuleBrief()` hooks |
| `src/classes/Application/Composer/ModulesOverview/ModuleInfoParser.php` | Canonical YAML-to-`ModuleInfo` parser — `final` class, 3 `BuildMessages` source-label literals |
| `src/classes/DBHelper/FetchMany.php` | Legacy non-namespaced class; `fetch()` override has return type incompatible with parent `DBHelper_FetchOne::fetch()` |
| `assets/classes/Maileditor/Composer/ModuleJsonExport/ModuleJsonExportGenerator.php` (HCP Editor) | Thin subclass overriding only `resolveModuleSource()` |

### Existing Test Files (Composer module)

Framework (`tests/AppFrameworkTests/Composer/`):
- `KeywordGlossary/KeywordGlossaryGeneratorTest.php` — fixture-based integration tests
- `KeywordGlossary/KeywordParserTest.php`
- `KeywordGlossary/DecorateGlossaryEventTest.php`
- `KeywordGlossary/GlossarySectionTest.php`
- `ModulesOverview/ModulesOverviewGeneratorTest.php`
- `ModulesOverview/ModulesOverviewRendererTest.php`
- `ModulesOverview/ReadmeOverviewParserTest.php`
- `ModulesOverview/ModuleContextFileFinderTest.php`
- `BuildMessagesTest.php`

HCP Editor (`tests/MailEditorTests/Composer/`):
- `ModulesOverview/ModulesOverviewGeneratorTest.php`
- `ModulesOverview/ModuleInfoTest.php`
- `ModulesOverview/ModuleContextFileFinderTest.php`
- `KeywordGlossary/KeywordGlossaryGeneratorTest.php`
- `KeywordGlossary/TenantListProviderTest.php`

## Approach / Architecture

Four independent work packages, each self-contained and independently testable:

1. **WP-A: KeywordGlossaryBuilder unit tests** — New test file exercising the conflict-warning code path and core builder behaviour.
2. **WP-B: Framework ModuleJsonExportGenerator unit tests** — New test file covering `generate()` end-to-end, `$includeAll` behaviour, and hook override points.
3. **WP-C: HCP Editor ModuleJsonExportGenerator unit tests** — New test file verifying `resolveModuleSource()` classification logic.
4. **WP-D: Migrate `echo` to `$onProgress` in ModuleJsonExportGenerator** — Add an optional `$onProgress` constructor parameter and replace all 4 `echo` calls with guarded callback invocations. Aligns with `KeywordGlossaryGenerator` pattern.
5. **WP-E: Extract `SOURCE` constant in ModuleInfoParser** — Replace 3 string-literal occurrences of `'ModuleInfoParser'` with `private const string SOURCE`.
6. **WP-F: Fix PHPStan return-type incompatibility in FetchMany::fetch()** — Resolve the covariance issue between `DBHelper_FetchOne::fetch()` (returns `array<int|string,string|int|float|NULL>`) and `DBHelper_FetchMany::fetch()` (returns `array<int,array<int|string,string|int|float|NULL>>`).

## Rationale

- **WP-A/B/C** close the three test gaps flagged in the synthesis. `KeywordGlossaryBuilder` in particular has a conflict-warning code path (lines 75–82) that is only exercised when two modules define the same keyword with different context strings — this path has zero direct coverage today.
- **WP-D** eliminates an inconsistency within the Composer build subsystem: `KeywordGlossaryGenerator` uses `$onProgress` while `ModuleJsonExportGenerator` uses `echo`. The `$onProgress` pattern enables testability (progress messages can be captured), suppresses output in automated contexts, and is already established.
- **WP-E** is a minor refactoring to consolidate a repeated string literal. The class is `final` and stable.
- **WP-F** resolves a genuine PHPStan error that has been carried as pre-existing debt. `FetchMany::fetch()` returns a structurally different type than its parent `FetchOne::fetch()`, which PHPStan correctly flags as a return type incompatibility.

## Detailed Steps

### WP-A: KeywordGlossaryBuilder Unit Tests

**New file:** `tests/AppFrameworkTests/Composer/KeywordGlossary/KeywordGlossaryBuilderTest.php`

1. Create the test file extending `ApplicationTestCase`.
2. Implement fixture helper that creates `ModuleInfo` value objects directly (no filesystem fixtures needed — `KeywordGlossaryBuilder` accepts `ModuleInfo[]`).
3. Test cases:
   - **Basic build:** Two modules with distinct keywords → all keywords appear, sorted alphabetically.
   - **Deduplication:** Same keyword in two modules → single entry with merged module IDs.
   - **Case preservation:** First-seen casing is preserved; later occurrences don't overwrite.
   - **Conflict warning:** Same keyword, different context, with `$onProgress` callback → warning message emitted.
   - **Conflict warning suppressed:** Same keyword, different context, without `$onProgress` → no error (no callback).
   - **Empty keywords:** Module with empty keyword string → skipped silently.
   - **Empty module list:** No modules → empty result array.
4. Run: `composer test-file -- tests/AppFrameworkTests/Composer/KeywordGlossary/KeywordGlossaryBuilderTest.php`

### WP-B: Framework ModuleJsonExportGenerator Unit Tests

**New file:** `tests/AppFrameworkTests/Composer/ModulesOverview/ModuleJsonExportGeneratorTest.php`

1. Create the test file extending `ApplicationTestCase`.
2. Use temporary fixture directories with synthetic `module-context.yaml` files (same pattern as `KeywordGlossaryGeneratorTest`).
3. Create a test subclass within the test file that overrides `resolveModuleSource()` and `resolveModuleBrief()` to verify hook invocation.
4. Test cases:
   - **End-to-end:** Generate JSON from fixtures → valid JSON with expected top-level keys (`generatedAt`, `modules`, `glossary`, `glossarySections`).
   - **`$includeAll = false`:** Module without a `README-Brief.md` → excluded from output.
   - **`$includeAll = true`:** Module without a `README-Brief.md` → included with `brief: null`.
   - **Hook override:** Custom subclass returning custom source/brief → values appear in JSON output.
   - **Empty fixture root:** No modules → JSON contains empty arrays.
5. Run: `composer test-file -- tests/AppFrameworkTests/Composer/ModulesOverview/ModuleJsonExportGeneratorTest.php`

### WP-C: HCP Editor ModuleJsonExportGenerator Unit Tests

**New file:** `tests/MailEditorTests/Composer/ModuleJsonExport/ModuleJsonExportGeneratorTest.php`

1. Create the test file extending `MailTestCase`.
2. Test cases for `resolveModuleSource()`:
   - Module with `mistralys/application_framework` package → returns `'framework'`.
   - Module with `mistralys/anything` package → returns `'framework'`.
   - Module with `communication/maileditor` package → returns `'hcp-editor'`.
   - Module with any non-`mistralys/` package → returns `'hcp-editor'`.
3. Note: `resolveModuleSource()` is `protected`, so the test must either use `generate()` end-to-end or use a reflection-based approach. Prefer end-to-end with fixture YAML files — consistent with the existing test patterns.
4. Run: `composer test-file -- tests/MailEditorTests/Composer/ModuleJsonExport/ModuleJsonExportGeneratorTest.php`

### WP-D: Migrate `echo` to `$onProgress` in ModuleJsonExportGenerator

**Modified file:** `src/classes/Application/Composer/ModulesOverview/ModuleJsonExportGenerator.php`

1. Add a `private $onProgress` property and an optional `?callable $onProgress = null` parameter to the constructor (second parameter, after `$rootFolder`).
2. Add a `private function progress(string $message): void` helper that checks `$this->onProgress !== null` before calling `($this->onProgress)($message)`.
3. Replace all 4 `echo` statements (lines 63, 68, 84, 124) with calls to `$this->progress(...)`, preserving the exact message strings.
4. Update the class docblock to mention the `$onProgress` parameter.
5. Update the HCP Editor call site(s) that instantiate `ModuleJsonExportGenerator` to pass `null` (or a progress callback if the call site currently relies on the echo output). Search for all instantiation sites.
6. Run: `composer test-filter -- ModuleJsonExportGenerator`

### WP-E: Extract SOURCE Constant in ModuleInfoParser

**Modified file:** `src/classes/Application/Composer/ModulesOverview/ModuleInfoParser.php`

1. Add `private const string SOURCE = 'ModuleInfoParser';` inside the class (after the existing `FALLBACK_PACKAGE_NAME` constant).
2. Replace the 3 string-literal occurrences at lines 64, 72, and 82 with `self::SOURCE`.
3. Run: `composer test-filter -- ModuleInfoParser`

### WP-F: Fix PHPStan Return Type in FetchMany::fetch()

**Modified files:** `src/classes/DBHelper/FetchMany.php` and potentially `src/classes/DBHelper/FetchOne.php`

1. Analyse the inheritance: `DBHelper_FetchOne::fetch()` returns `array<int|string,string|int|float|NULL>` (single row). `DBHelper_FetchMany::fetch()` returns `array<int,array<int|string,string|int|float|NULL>>` (multiple rows). These are structurally incompatible — the child cannot narrow/widen a completely different shape.
2. The most appropriate fix depends on how these classes are consumed. Options:
   - **Option A (preferred):** Rename `FetchMany::fetch()` to `fetchAll()` so it doesn't override the parent, then update all call sites. This is the cleanest but has the widest blast radius.
   - **Option B:** Widen the parent's `@return` to `array` (phpDoc only) and use `@phpstan-return` on each subclass for the specific shapes. This avoids renaming but weakens type safety at the parent level.
   - **Option C:** Add a `@phpstan-ignore-next-line` suppression. Not recommended — hides a real design issue.
3. Research all callers of `FetchMany::fetch()` to determine blast radius.
4. Implement the chosen option.
5. Run: `composer analyze` to verify zero regressions.

## Dependencies

- WP-A, WP-B, WP-C, WP-D, WP-E, WP-F are all **independent** and can be implemented in any order or in parallel.
- WP-B and WP-C depend on WP-D only if the tests want to verify progress output. Otherwise independent. Recommend implementing WP-D first so the test files can exercise the `$onProgress` parameter.
- WP-F is entirely isolated from the Composer module.

## Required Components

### New Files

| File | Project | Purpose |
|---|---|---|
| `tests/AppFrameworkTests/Composer/KeywordGlossary/KeywordGlossaryBuilderTest.php` | Application Framework | Unit tests for `KeywordGlossaryBuilder` |
| `tests/AppFrameworkTests/Composer/ModulesOverview/ModuleJsonExportGeneratorTest.php` | Application Framework | Unit tests for framework `ModuleJsonExportGenerator` |
| `tests/MailEditorTests/Composer/ModuleJsonExport/ModuleJsonExportGeneratorTest.php` | HCP Editor | Unit tests for HCP Editor `ModuleJsonExportGenerator` |

### Modified Files

| File | Project | Purpose |
|---|---|---|
| `src/classes/Application/Composer/ModulesOverview/ModuleJsonExportGenerator.php` | Application Framework | Add `$onProgress`, replace `echo` calls |
| `src/classes/Application/Composer/ModulesOverview/ModuleInfoParser.php` | Application Framework | Add `SOURCE` constant |
| `src/classes/DBHelper/FetchMany.php` | Application Framework | Fix return-type incompatibility |
| Potentially `src/classes/DBHelper/FetchOne.php` | Application Framework | Widen `@return` if Option B chosen |

## Assumptions

- `ModuleInfo` can be instantiated directly in tests (no factory or database dependency).
- The HCP Editor's instantiation of `ModuleJsonExportGenerator` does not rely on `echo` output for functional behaviour (it is purely informational console output during builds).
- The PHPStan error in `FetchMany` is the only pre-existing PHPStan error.

## Constraints

- Array syntax: `array()` only — never `[]`.
- No constructor promotion.
- `declare(strict_types=1)` in every file.
- Typed constants (`private const string SOURCE`, not `private const SOURCE`).
- Legacy (non-namespaced) classes like `DBHelper_FetchMany` must not be refactored to namespaced unless explicitly requested.
- Run `composer dump-autoload` after adding new test files.

## Out of Scope

- Refactoring `DBHelper_FetchMany`/`DBHelper_FetchOne` into namespaced classes.
- Adding `$onProgress` to other generators that don't currently use it.
- Refactoring existing test files.
- CTX documentation regeneration (no architectural changes warrant it).
- The already-resolved `buildGlossary()` dedup item from the synthesis.

## Acceptance Criteria

- [ ] `KeywordGlossaryBuilderTest` passes with ≥6 test methods covering dedup, conflict warning, case preservation, and edge cases.
- [ ] Framework `ModuleJsonExportGeneratorTest` passes with ≥4 test methods covering end-to-end, `$includeAll`, hook override, and empty fixture.
- [ ] HCP Editor `ModuleJsonExportGeneratorTest` passes with ≥3 test methods covering `resolveModuleSource()` classification.
- [ ] `ModuleJsonExportGenerator::generate()` contains zero `echo` statements; all progress output goes through `$onProgress`.
- [ ] `ModuleInfoParser` uses `self::SOURCE` constant for all `BuildMessages` source labels.
- [ ] `composer analyze` reports zero new PHPStan errors.
- [ ] All existing Composer module tests continue to pass: `composer test-filter -- Composer` (in framework).
- [ ] HCP Editor Composer tests pass: `composer test-filter -- Composer` (in HCP Editor).

## Testing Strategy

| Scope | Command | Purpose |
|---|---|---|
| WP-A | `composer test-file -- tests/AppFrameworkTests/Composer/KeywordGlossary/KeywordGlossaryBuilderTest.php` | New builder tests |
| WP-B | `composer test-file -- tests/AppFrameworkTests/Composer/ModulesOverview/ModuleJsonExportGeneratorTest.php` | New framework generator tests |
| WP-C | `composer test-file -- tests/MailEditorTests/Composer/ModuleJsonExport/ModuleJsonExportGeneratorTest.php` | New HCP Editor generator tests |
| WP-D | `composer test-filter -- ModuleJsonExportGenerator` | Verify echo migration |
| WP-E | `composer test-filter -- ModuleInfoParser` | Verify constant extraction |
| WP-F | `composer analyze` | Verify PHPStan clean |
| Regression | `composer test-filter -- Composer` (both projects) | Full Composer module regression |

## Risks & Mitigations

| Risk | Mitigation |
|---|---|
| **WP-F blast radius:** Renaming `FetchMany::fetch()` to `fetchAll()` could break callers across the codebase. | Research all call sites before choosing the fix approach. Option B (phpDoc widening) is available as a zero-blast-radius fallback. |
| **WP-B/C fixture fragility:** Temporary fixture directories could collide in parallel test runs. | Use `getmypid()` + `mt_rand()` in temp paths (established pattern from `KeywordGlossaryGeneratorTest`). |
| **WP-D: Subclass constructor compatibility.** Adding a parameter to the framework base constructor could break the HCP Editor subclass if it has its own constructor. | Verified: HCP Editor `ModuleJsonExportGenerator` has no constructor override — it inherits from the framework base class. Adding a second optional parameter is safe. |
| **ModuleInfo construction in tests:** If `ModuleInfo` requires complex setup, test fixtures become brittle. | Verify `ModuleInfo` constructor before writing tests. If complex, create a builder helper or use the YAML fixture pattern. |
