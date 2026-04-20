## Synthesis

### Completion Status
- Date: 2026-04-20
- Status: COMPLETE
- Completed by: Standalone Developer Agent

### Implementation Summary
- **WP-A (KeywordGlossaryBuilderTest):** Created `tests/AppFrameworkTests/Composer/KeywordGlossary/KeywordGlossaryBuilderTest.php` with 7 unit tests covering: basic distinct keywords (alphabetical sort), deduplication with merged module IDs, first-seen casing preservation, conflict warning emitted via callback, conflict warning suppressed without callback, empty keyword skipped, and empty module list.
- **WP-B (Framework ModuleJsonExportGeneratorTest):** Created `tests/AppFrameworkTests/Composer/ModulesOverview/ModuleJsonExportGeneratorTest.php` with 5 fixture-based tests covering: end-to-end valid JSON output with expected top-level keys, `$includeAll=false` excludes module without brief, `$includeAll=true` includes module with `brief=null`, hook override via test subclass, and empty fixture root produces empty arrays.
- **WP-C (HCP Editor ModuleJsonExportGeneratorTest):** Created `tests/MailEditorTests/Composer/ModuleJsonExport/ModuleJsonExportGeneratorTest.php` in the HCP Editor with 3 end-to-end fixture tests verifying `resolveModuleSource()` classification: `mistralys/application_framework` → `'framework'`, any other `mistralys/*` → `'framework'`, non-`mistralys/*` → `'hcp-editor'`.
- **WP-D (echo → $onProgress migration):** Replaced all 4 `echo` calls in `ModuleJsonExportGenerator::generate()` with calls to a new private `progress()` helper. Added `/** @var callable|null */ private $onProgress` property and `?callable $onProgress = null` as a second constructor parameter. Updated the HCP Editor call site in `tools/generate-module-json.php` to pass an explicit progress callback that writes to stdout, preserving the original console output behaviour.
- **WP-E (SOURCE constant):** Added `private const string SOURCE = 'ModuleInfoParser';` to `ModuleInfoParser` (after `FALLBACK_PACKAGE_NAME`) and replaced all 3 string-literal occurrences of `'ModuleInfoParser'` in `BuildMessages` calls with `self::SOURCE`.
- **WP-F (FetchMany return type):** Changed `DBHelper_FetchOne::fetch()` `@return` annotation from `array<int|string,string|int|float|NULL>` to `array<int|string, mixed>` (Option B). This makes `DBHelper_FetchMany::fetch()`'s return type `array<int,array<int|string,string|int|float|NULL>>` a valid PHPStan subtype of the parent's declared return type, eliminating the pre-existing covariance error.

### Documentation Updates
No documentation updates were required because:
- No public API interfaces changed (the `$onProgress` parameter is optional with a `null` default, so all existing call sites remain compatible).
- The `SOURCE` constant is `private` — no external contract changed.
- The `FetchOne::fetch()` annotation change is a PHPDoc-only fix in a legacy non-namespaced class; widening a `@return` to `mixed` values does not constitute a breaking API change.
- No new architectural concepts were introduced that would require CTX regeneration.

### Verification Summary
- Tests run:
  - `KeywordGlossaryBuilderTest` → **7 tests, 15 assertions — OK**
  - `ModuleJsonExportGeneratorTest` (framework) → **5 tests, 20 assertions — OK**
  - `ModuleJsonExportGeneratorTest` (HCP Editor) → **3 tests, 19 assertions — OK**
  - Full Application Framework Composer filter → **88 tests, 212 assertions — OK**
  - Full HCP Editor Composer filter → **27 tests, 210 assertions — OK**
- Static analysis run:
  - `composer analyze` (Application Framework, PHPStan level 5) → **No errors**
- Result: **PASS — zero regressions, zero new PHPStan errors**

### Code Insights
- [low] (debt) `src/classes/Application/Composer/ModulesOverview/ModuleJsonExportGenerator.php`: The `buildGlossary()` method instantiates `KeywordGlossaryBuilder` without forwarding `$this->onProgress`, so conflict warnings from the builder are silently discarded. Consider passing `$this->onProgress` to the builder constructor to surface keyword conflicts during the JSON export workflow, consistent with how `KeywordGlossaryGenerator` does it.
- [low] (convention) `src/classes/Application/Composer/ModulesOverview/ModulesOverviewGenerator.php`: This generator still uses `echo` for progress output (visible in test output), while `KeywordGlossaryGenerator` and the now-updated `ModuleJsonExportGenerator` use the `$onProgress` callback pattern. If consistency is desired across the full Composer build subsystem, `ModulesOverviewGenerator` is the remaining generator that would benefit from the same migration.
- [low] (debt) `src/classes/DBHelper/FetchOne.php`: Widening the `@return` of `FetchOne::fetch()` to `array<int|string, mixed>` resolves the PHPStan covariance violation but weakens static type information for callers (values are now `mixed` instead of `string|int|float|null`). The root cause remains a Liskov Substitution Principle violation: `FetchMany::fetch()` returns a structurally incompatible shape from its parent. A future refactoring could rename `FetchMany::fetch()` to `fetchAll()` to make the API self-documenting, but this has a large call-site blast radius and is out of scope for this plan.
- [low] (improvement) `tests/AppFrameworkTests/Composer/ModulesOverview/ModuleJsonExportGeneratorTest.php`: The `removeFixtureRoot()` helper uses `RecursiveIteratorIterator` for teardown. A utility for recursive temp-dir cleanup is duplicated across this test file and the new HCP Editor test. If a third fixture-based test is added in the same area, consider extracting a shared `TempFixtureRoot` trait in the respective test-class namespaces.

### Additional Comments
- The test subclass `TestModuleJsonExportGenerator` inside `ModuleJsonExportGeneratorTest.php` required its `resolveModuleBrief()` override to declare a non-nullable `string` return type (narrowed from parent's `?string`). This is valid PHP 8 covariant return narrowing and satisfies the PHPStan `return.unusedType` rule that flagged the unnecessary `|null` in the test-only override.
- The HCP Editor's `tools/generate-module-json.php` call site was updated to pass a closure that writes to `stdout`. This is a no-op change from the user's perspective (CLI output is identical) but correctly restores the progress output that would otherwise be silently suppressed after the echo-to-callback migration.
