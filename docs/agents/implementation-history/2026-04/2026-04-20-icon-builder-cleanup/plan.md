# Plan — Icon Builder CLI: Post-Synthesis Cleanup

## Summary

Follow-up plan addressing all actionable items from the `2026-04-17-icon-builder-cli` synthesis strategic recommendations, test coverage gaps, and next steps. Each item has been independently verified against the codebase. The work spans the Application Framework repository only (the HCP Editor is not affected by these items).

## Architectural Context

The Icon Builder CLI was delivered across two layers:

- **Runtime layer** (`UI\Icons`): `IconInfo` value object + `IconCollection` singleton in `src/classes/UI/Icons/`.
- **Build-time layer** (`Application\Composer\IconBuilder`): `IconDefinition`, `IconsReader`, `AbstractLanguageRenderer`, `PHPRenderer`, `JSRenderer`, and `IconBuilder` in `src/classes/Application/Composer/IconBuilder/`.

Existing tests:
- [tests/AppFrameworkTests/Composer/IconBuilder/IconBuilderTest.php](tests/AppFrameworkTests/Composer/IconBuilder/IconBuilderTest.php) — 11 tests, 33 assertions.
- [tests/AppFrameworkTests/UI/IconCollectionTest.php](tests/AppFrameworkTests/UI/IconCollectionTest.php) — 14 tests, 234 assertions.

## Approach / Architecture

Six independent work items, grouped by concern:

1. **Rename `ERROR_START_MARKER_NOT_FOUND`** — Add a new `ERROR_END_MARKER_NOT_FOUND` constant and use it in the end-marker validation branch. This is a public API constant change; update the existing `IconBuilderTest` accordingly.
2. **Add `IconCollection::resetInstance()`** — Introduce a `@internal` static reset method for future test isolation scenarios (e.g., testing with different icon configurations or verifying fresh-load behavior).
3. **Consolidate `normaliseID()` duplication** — Extract the identical `str_replace(array('-', ' '), '_', $id)` logic from `IconCollection` and `IconsReader` into a single shared static method on `IconInfo`, and have both consumers delegate to it.
4. **Add `IconsReaderTest`** — Medium-priority coverage gap. Tests: icon count, spinner exclusion, ID normalisation, sort order, missing-file handling.
5. **Add `IconInfoTest`** — Low-priority coverage gap. Tests: camelCase conversion, `createIcon()` factory, getters.
6. **Add `AbstractLanguageRendererTest`** — Low-priority coverage gap. Tests: `render()` template method, `toPascalCase()` conversion via a test subclass (the method is `protected`).

Item 6 from the synthesis (composer.json sync documentation) was already completed during WP-013. Item 7 (CTX documentation for `IconCollection::getByID()` exception) was verified as already present in `.context/modules/ui/architecture-core.md`.

## Rationale

- The `ERROR_START_MARKER_NOT_FOUND` reuse for end-marker failures is a confirmed bug: error codes should unambiguously identify the failure site. Adding a separate constant is the minimal correct fix.
- `IconCollection` singleton without reset limits future test scenarios — e.g., testing with different icon configurations or verifying fresh-load behavior. The existing tests work without it because they only read the singleton, but this addition enables proper isolation for future tests that may need to mutate state. The `@internal` annotation keeps the method out of the public API surface.
- The `normaliseID()` logic is duplicated identically in `IconCollection` (runtime, `UI\Icons` namespace) and `IconsReader` (build-time, `Application\Composer\IconBuilder` namespace). Placing the shared method on `IconInfo` is the natural choice: it is the value object that carries icon IDs, it already lives in the `UI\Icons` namespace that `IconCollection` uses, and `IconsReader` already depends on the icon domain. This avoids introducing a new class or trait for a single one-liner.
- The three missing test files cover classes that are currently only exercised indirectly through `IconBuilderTest` and `IconCollectionTest`. Direct unit tests improve regression confidence.

## Detailed Steps

### Step 1: Add `ERROR_END_MARKER_NOT_FOUND` constant and fix its usage

**File:** `src/classes/Application/Composer/IconBuilder/IconBuilder.php`

1. Add a new constant `public const int ERROR_END_MARKER_NOT_FOUND = 82305;` after the existing `ERROR_WRITE_FAILED` constant.
2. In the `insertIconCode()` method (around line 177), change the end-marker validation branch from `self::ERROR_START_MARKER_NOT_FOUND` to `self::ERROR_END_MARKER_NOT_FOUND`.
3. Update the `build()` method docblock `@see` reference: it currently lists `{@see self::ERROR_START_MARKER_NOT_FOUND}` for all marker failures — add a separate entry for `{@see self::ERROR_END_MARKER_NOT_FOUND}`.
4. Update `tests/AppFrameworkTests/Composer/IconBuilder/IconBuilderTest.php`: the existing `test_build_phpEndMarkerMissing_returnsError` test asserts `ERROR_START_MARKER_NOT_FOUND` — change it to `ERROR_END_MARKER_NOT_FOUND`.
5. Add a new test `test_build_jsEndMarkerMissing_returnsError` that creates a JS file with the start marker but no end marker, and asserts `ERROR_END_MARKER_NOT_FOUND`. Currently no such test exists.

### Step 2: Add `IconCollection::resetInstance()`

**File:** `src/classes/UI/Icons/IconCollection.php`

1. Add a `@internal` static method:
   ```php
   /**
    * Resets the singleton instance. Intended for test isolation only.
    * @internal
    */
   public static function resetInstance() : void
   {
       self::$instance = null;
   }
   ```
2. In `tests/AppFrameworkTests/UI/IconCollectionTest.php`, add a `tearDown()` method that calls `IconCollection::resetInstance()` to ensure test isolation.

### Step 3: Consolidate `normaliseID()` into `IconInfo`

**Files:**
- `src/classes/UI/Icons/IconInfo.php`
- `src/classes/UI/Icons/IconCollection.php`
- `src/classes/Application/Composer/IconBuilder/IconsReader.php`

`IconCollection` (line ~201) and `IconsReader` (line ~118) both contain identical private `normaliseID()` methods:
```php
private function normaliseID(string $id) : string
{
    return str_replace(array('-', ' '), '_', $id);
}
```

1. Add a **public static** method to `IconInfo`:
   ```php
   /**
    * Normalises an icon ID by replacing hyphens and spaces with underscores.
    *
    * @param string $id Raw icon ID.
    * @return string Normalised icon ID.
    */
   public static function normaliseID(string $id) : string
   {
       return str_replace(array('-', ' '), '_', $id);
   }
   ```
2. In `IconCollection`, replace the private `normaliseID()` method body with a delegation to `IconInfo::normaliseID()`, or remove the private method entirely and replace all call sites with `IconInfo::normaliseID($id)`.
3. In `IconsReader`, add `use UI\Icons\IconInfo;` and replace all `$this->normaliseID($id)` calls with `IconInfo::normaliseID($id)`. Remove the private `normaliseID()` method.
4. Run `composer test-filter -- IconBuilder` and `composer test-filter -- IconCollection` to confirm no regressions.

### Step 4: Create `IconsReaderTest.php`

**File (new):** `tests/AppFrameworkTests/Composer/IconBuilder/IconsReaderTest.php`

Test cases to implement:
1. `test_readsExpectedIconCount` — Load the framework's `icons.json` and verify the count matches the expected number of icons (excluding spinners).
2. `test_excludesSpinnerIcons` — Verify that icons with `type: spinner` are excluded from the definitions.
3. `test_normalisesIconIDs` — Verify that hyphens and spaces in icon IDs are converted to underscores.
4. `test_definitionsAreSortedByID` — Verify that the returned definitions are sorted alphabetically by ID.
5. `test_returnsEmptyOnMissingFile` — Verify that a non-existent JSON path returns an empty definitions list (the reader handles missing files gracefully via `$file->exists()`, it does not throw).
6. `test_iconDefinitionProperties` — Verify that an `IconDefinition` has the expected ID, icon name, and icon type properties (via `getID()`, `getIconName()`, `getIconType()`).

Use `ApplicationTestCase` as the base class and follow the patterns in `IconBuilderTest.php`.

### Step 5: Create `IconInfoTest.php`

**File (new):** `tests/AppFrameworkTests/UI/IconInfoTest.php`

Test cases to implement:
1. `test_getID` — Verify the ID getter returns the normalised icon ID.
2. `test_getPrefix` — Verify the prefix getter returns the FA prefix string (e.g. `far`, `fas`). `IconInfo` uses `getPrefix()`, not `getType()`.
3. `test_getIconName` — Verify the icon name getter returns the FA icon name (e.g. `exclamation-triangle`). `IconInfo` has no `getLabel()` method.
4. `test_getFullIconName` — Verify that `getFullIconName()` returns `prefix:name` when a prefix is present, and just the name when empty.
5. `test_createIcon` — Verify the `createIcon()` factory method returns a `UI_Icon` instance with the correct configuration.
6. `test_camelCaseConversion` — Verify that `getMethodName()` converts underscore-separated IDs to camelCase (e.g. `attention_required` → `attentionRequired`).
7. `test_normaliseID` — Verify that `IconInfo::normaliseID()` replaces hyphens and spaces with underscores.

### Step 6: Create `AbstractLanguageRendererTest.php`

**File (new):** `tests/AppFrameworkTests/Composer/IconBuilder/AbstractLanguageRendererTest.php`

**Note:** `toPascalCase()` and `renderSetTypeArgs()` are `protected` methods. The test file must define a minimal concrete test subclass that exposes them as public wrappers. Example:
```php
class TestableLanguageRenderer extends AbstractLanguageRenderer
{
    protected function renderMethod(IconDefinition $icon) : string
    {
        return '';
    }

    public function exposeToPascalCase(string $id) : string
    {
        return $this->toPascalCase($id);
    }
}
```

Test cases to implement:
1. `test_toPascalCase` — Using the test subclass, verify that underscored icon IDs are converted to PascalCase (e.g., `time_tracker` → `TimeTracker`).
2. `test_renderProducesOutput` — Verify that `render()` on a concrete renderer (use `PHPRenderer` or `JSRenderer`) produces non-empty output when given icon definitions.
3. `test_renderContainsMethodNames` — Verify that the rendered output contains method names matching the PascalCase-converted icon IDs.

## Dependencies

- Steps 1–2 are independent of each other and of all other steps.
- Step 3 (normaliseID consolidation) should be completed before Step 4 (IconsReaderTest) and Step 5 (IconInfoTest), since those tests should exercise the consolidated method.
- Steps 4–6 (new test files) are independent of each other.

## Required Components

| Component | Type | Status |
|---|---|---|
| `src/classes/Application/Composer/IconBuilder/IconBuilder.php` | Existing | Modified (Step 1) |
| `src/classes/UI/Icons/IconCollection.php` | Existing | Modified (Steps 2, 3) |
| `src/classes/UI/Icons/IconInfo.php` | Existing | Modified (Step 3) |
| `src/classes/Application/Composer/IconBuilder/IconsReader.php` | Existing | Modified (Step 3) |
| `tests/AppFrameworkTests/Composer/IconBuilder/IconBuilderTest.php` | Existing | Modified (Step 1) |
| `tests/AppFrameworkTests/UI/IconCollectionTest.php` | Existing | Modified (Step 2) |
| `tests/AppFrameworkTests/Composer/IconBuilder/IconsReaderTest.php` | **New** | Created (Step 4) |
| `tests/AppFrameworkTests/UI/IconInfoTest.php` | **New** | Created (Step 5) |
| `tests/AppFrameworkTests/Composer/IconBuilder/AbstractLanguageRendererTest.php` | **New** | Created (Step 6) |

## Assumptions

- The error code `82305` is available and not used elsewhere in the codebase.
- The `IconsReader`, `IconInfo`, and `AbstractLanguageRenderer` classes are stable and will not change before these tests are written.

## Constraints

- All code must use `array()` syntax, never `[]`.
- All new files must include `declare(strict_types=1);`.
- Run `composer dump-autoload` after adding new test files (classmap autoloading).
- The CTX documentation for `IconCollection::getByID()` exception behavior (synthesis recommendation #2) was verified as already present and requires no action.

## Out of Scope

- HCP Editor changes (no items in the synthesis affect the HCP Editor codebase).
- CTX documentation updates (already verified as current).
- `composer.json` / `composer-prod.json` sync documentation (already completed in WP-013).
- `DBHelper_FetchMany` PHPStan error (`method.childReturnType`) — pre-existing and unrelated to the Icon Builder feature.
- Git operations (commit, push, branch management).

## Acceptance Criteria

1. `ERROR_END_MARKER_NOT_FOUND` constant exists with value `82305` and is used in the end-marker validation branch of `IconBuilder::insertIconCode()`.
2. `IconCollection::resetInstance()` exists, is annotated `@internal`, and sets the singleton instance to `null`.
3. `IconInfo::normaliseID()` is a public static method. Neither `IconCollection` nor `IconsReader` contain a private `normaliseID()` method. Both delegate to `IconInfo::normaliseID()`.
4. `IconsReaderTest.php` exists and all tests pass via `composer test-file -- tests/AppFrameworkTests/Composer/IconBuilder/IconsReaderTest.php`.
5. `IconInfoTest.php` exists with a test for `IconInfo::normaliseID()`, and all tests pass via `composer test-file -- tests/AppFrameworkTests/UI/IconInfoTest.php`.
6. `AbstractLanguageRendererTest.php` exists and all tests pass via `composer test-file -- tests/AppFrameworkTests/Composer/IconBuilder/AbstractLanguageRendererTest.php`.
7. Existing tests remain green: `composer test-filter -- IconBuilder` and `composer test-filter -- IconCollection` both pass with zero failures.

## Testing Strategy

- Each step includes its own test verification (either updating existing tests or creating new ones).
- After all steps, run `composer test-filter -- Icon` to verify all Icon-related tests pass together.
- Run `composer analyze` to confirm zero new PHPStan errors across the entire codebase.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **Error code `82305` is already in use** | Grep for `82305` across the codebase before implementation. If taken, use the next available code. |
| **`resetInstance()` leaks into production usage** | The `@internal` annotation signals non-public API. The method is only called from test tearDown. |
| **`IconsReader` coupling to `UI\Icons` namespace** | `IconsReader` gains a `use UI\Icons\IconInfo` import. This is acceptable: the build-time layer already produces artifacts consumed by the runtime layer, so the dependency direction (build → runtime) is correct. |
| **New test files not autoloaded** | Run `composer dump-autoload` after creating test files — classmap autoloading requires it. |
