# Plan

## Summary

Follow-up plan to close the three remaining actionable items from the `2026-04-20-icon-builder-cleanup` synthesis. The work targets `IconBuilder::insertIconCode()` (silent read-failure bug), three IconBuilder test fixtures (strict-types / `realpath()` safety), and the `TestableLanguageRenderer` class (missing file-scope placement comment). All changes are in the Application Framework repository.

## Architectural Context

The Icon Builder subsystem lives in `src/classes/Application/Composer/IconBuilder/` and is a build-time code-generation pipeline with no runtime application dependencies. Its test suite is in `tests/AppFrameworkTests/Composer/IconBuilder/` (4 test files, 21 tests, 275 assertions). A companion test file exists at `tests/AppFrameworkTests/UI/IconInfoTest.php`.

Key files involved:

| File | Role |
|---|---|
| [src/classes/Application/Composer/IconBuilder/IconBuilder.php](src/classes/Application/Composer/IconBuilder/IconBuilder.php) | Orchestrator — reads JSON, renders PHP/JS, replaces markers in target files |
| [tests/AppFrameworkTests/Composer/IconBuilder/IconBuilderTest.php](tests/AppFrameworkTests/Composer/IconBuilder/IconBuilderTest.php) | Tests for `IconBuilder::build()` and `insertIconCode()` |
| [tests/AppFrameworkTests/Composer/IconBuilder/IconsReaderTest.php](tests/AppFrameworkTests/Composer/IconBuilder/IconsReaderTest.php) | Tests for `IconsReader` |
| [tests/AppFrameworkTests/Composer/IconBuilder/AbstractLanguageRendererTest.php](tests/AppFrameworkTests/Composer/IconBuilder/AbstractLanguageRendererTest.php) | Tests for `AbstractLanguageRenderer` via `TestableLanguageRenderer` |

Error constants in `IconBuilder` follow the range `82301–82305`. The next available code is `82306`.

The `OperationResult` class (from `mistralys/application-utils-result-handling`) provides `makeError(string $message, int $code)` for reporting failures.

## Approach / Architecture

Three independent, low-risk changes:

1. **Bug fix:** Add an explicit `false`-check on the `file_get_contents()` return value in `insertIconCode()`, returning a new `ERROR_READ_FAILED` error code (`82306`) before marker detection runs. This mirrors the existing `file_put_contents()` write-failure guard already present in the same method.

2. **Test fixture hardening:** In the three IconBuilder test files that assign `realpath()` directly to a `private string` property, introduce a local variable to capture the `realpath()` result, assert it with `assertNotFalse()`, and only then assign to the typed property. This prevents a `TypeError` from masking the descriptive assertion message under `declare(strict_types=1)`.

3. **Documentation micro-fix:** Add a single-line comment above the `TestableLanguageRenderer` class declaration explaining why it lives at file scope (PHP lacks inner classes).

## Rationale

- **Item 1** was independently flagged by three agents (Developer, QA, Reviewer) in the prior session. The silent `(string)file_get_contents()` cast converts an I/O failure into a misleading `ERROR_START_MARKER_NOT_FOUND`, making diagnosis difficult for operators. The fix is one guard clause and one new constant.
- **Item 2** is a correctness issue: the `assertNotFalse()` guards in `setUp()` are dead code in the failure path because the `TypeError` fires first on assignment to `private string`. Fixing the assignment order makes the guards effective and preserves the descriptive failure messages.
- **Item 3** is a one-line comment that prevents future contributor confusion about the unconventional class placement.

## Detailed Steps

### Step 1 — Add `ERROR_READ_FAILED` guard to `insertIconCode()`

1. In `IconBuilder.php`, add a new constant:
   ```php
   public const int ERROR_READ_FAILED = 82306;
   ```

2. In `insertIconCode()`, replace the current line:
   ```php
   $content = (string)file_get_contents($filePath);
   ```
   with an explicit `false`-check:
   ```php
   $content = file_get_contents($filePath);

   if($content === false)
   {
       return $result->makeError(
           sprintf('Failed to read file [%s].', $filePath),
           self::ERROR_READ_FAILED
       );
   }
   ```

3. Update the `insertIconCode()` docblock: remove the "Known edge case" paragraph (the gap it documents is now closed) and add `ERROR_READ_FAILED` to the error list.

4. Update the `build()` docblock to list `ERROR_READ_FAILED` alongside the other possible error codes.

### Step 2 — Add test for `ERROR_READ_FAILED`

1. In `IconBuilderTest.php`, add a new test method after the existing `test_build_writeFailure_returnsError`:
   ```php
   public function test_build_readFailure_returnsError() : void
   ```

2. The test should create a valid marker file, then make it unreadable (`chmod 0000`), run `build()`, assert the result is invalid with code `ERROR_READ_FAILED`, and restore permissions in the cleanup path.

3. Note: the test must restore the file permissions before `tearDown()` runs, otherwise the temp file cleanup will also fail. Use a `try/finally` block or restore after the assertions (same pattern used in `test_build_writeFailure_returnsError`).

### Step 3 — Fix `realpath()` / `private string` pattern in test fixtures

In each of the following three files, change the `setUp()` method to use a local variable for the `realpath()` call:

**Files:**
- `tests/AppFrameworkTests/Composer/IconBuilder/IconBuilderTest.php`
- `tests/AppFrameworkTests/Composer/IconBuilder/IconsReaderTest.php`
- `tests/AppFrameworkTests/Composer/IconBuilder/AbstractLanguageRendererTest.php`

**Current pattern (in all three):**
```php
$this->iconsJsonPath = realpath(
    __DIR__ . '/../../../../src/themes/default/icons.json'
);

$this->assertNotFalse(
    $this->iconsJsonPath,
    'Fixture icons.json could not be resolved.'
);
```

**Target pattern:**
```php
$resolved = realpath(
    __DIR__ . '/../../../../src/themes/default/icons.json'
);

$this->assertNotFalse(
    $resolved,
    'Fixture icons.json could not be resolved.'
);

$this->iconsJsonPath = $resolved;
```

### Step 4 — Add file-scope placement comment to `TestableLanguageRenderer`

In `AbstractLanguageRendererTest.php`, add a brief comment above the class docblock explaining the file-scope placement:

```php
// Lives at file scope because PHP does not support inner/nested classes.
```

This should be placed directly above the existing docblock for `TestableLanguageRenderer` (after the existing separator comment block at line 22–24).

### Step 5 — Update docblock and CTX documentation

1. Remove the "Known edge case" paragraph from the `insertIconCode()` docblock since the issue is now resolved.
2. Run `composer build` to regenerate CTX files and the autoload classmap.
3. Run `composer test-file -- tests/AppFrameworkTests/Composer/IconBuilder/IconBuilderTest.php` to confirm the new test passes.
4. Run `composer test-filter -- IconBuilder` to confirm no regressions across the full IconBuilder test suite.

## Dependencies

- Steps 1 and 2 are sequential (the constant must exist before the test can reference it).
- Step 3 is independent of steps 1–2.
- Step 4 is independent of all other steps.
- Step 5 depends on steps 1–4 being complete.

## Required Components

| Component | Status | Location |
|---|---|---|
| `IconBuilder.php` | Existing | `src/classes/Application/Composer/IconBuilder/IconBuilder.php` |
| `IconBuilderTest.php` | Existing | `tests/AppFrameworkTests/Composer/IconBuilder/IconBuilderTest.php` |
| `IconsReaderTest.php` | Existing | `tests/AppFrameworkTests/Composer/IconBuilder/IconsReaderTest.php` |
| `AbstractLanguageRendererTest.php` | Existing | `tests/AppFrameworkTests/Composer/IconBuilder/AbstractLanguageRendererTest.php` |
| `ERROR_READ_FAILED` constant | **New** | Added to `IconBuilder.php` as `82306` |

## Assumptions

- The error code `82306` is not already in use elsewhere in the framework. (Verified: the current range for `IconBuilder` is `82301–82305`; `82306` is the next sequential value.)
- The `chmod(0000)` approach for simulating read failures works on the test environment (macOS/Linux). This mirrors the existing `chmod(0444)` pattern used for write-failure testing in the same test file.
- No other test files in the framework use the `realpath()` → `private string` anti-pattern. (Verified: only the three IconBuilder test files exhibit this pattern.)

## Constraints

- All code must use `array()` syntax, not `[]`.
- All files must have `declare(strict_types=1)`.
- Error constants must follow the `ERROR_UPPER_SNAKE_CASE` naming convention.
- Error codes must be globally unique integers.
- After adding or modifying class constants, run `composer build` to regenerate CTX documentation.

## Out of Scope

- Refactoring `insertIconCode()` beyond the read-failure guard (e.g., extracting the marker-replacement logic).
- Adding read-failure simulation to the JS file path (the single test covering the PHP path is sufficient since `insertIconCode()` is a shared private method — the guard fires before any language-specific logic).
- Broader test suite hardening beyond the three identified IconBuilder files.
- Recommendation #2 from the synthesis (`IconInfo::normaliseID()` test) — already resolved in the prior session.
- Recommendation #4 from the synthesis (`resetInstance()` convention) — already documented in `coding-patterns.md`.

## Acceptance Criteria

- `IconBuilder::ERROR_READ_FAILED` constant exists with value `82306`.
- `insertIconCode()` returns `ERROR_READ_FAILED` when `file_get_contents()` returns `false`, before attempting marker detection.
- A new test `test_build_readFailure_returnsError` exists and passes.
- All three IconBuilder test fixtures assign `realpath()` to a local variable, assert it, then assign to the typed property.
- `TestableLanguageRenderer` has a comment explaining its file-scope placement.
- The `insertIconCode()` docblock no longer contains the "Known edge case" paragraph.
- `composer test-filter -- IconBuilder` passes with zero failures.
- CTX documentation is regenerated via `composer build`.

## Testing Strategy

1. **Unit test for read failure:** A new test (`test_build_readFailure_returnsError`) creates a valid marker file, removes read permissions, runs `build()`, and asserts the result code is `ERROR_READ_FAILED`. Permissions are restored after assertions.
2. **Regression:** Run `composer test-filter -- IconBuilder` to verify all existing tests still pass after the `realpath()` fixture changes and the new constant addition.
3. **Static analysis:** Run `composer analyze` to confirm no PHPStan regressions.

## Risks & Mitigations

| Risk | Mitigation |
|---|---|
| **`chmod(0000)` may not prevent reads on some OS/filesystem configurations** | Same risk exists for the current `chmod(0444)` write-failure test, which is already passing. If the test environment doesn't honour POSIX permissions (e.g., running as root), the test will need a skip annotation — but this is unlikely in standard CI. |
| **Changing `realpath()` assignment order could mask a deeper issue** | The change preserves the same assertion and error message; it only reorders operations so the assertion fires before the `TypeError`. No behaviour change for passing tests. |
| **Error code collision with `82306`** | Verified that no other class uses this code. Error codes are sequential within the `IconBuilder` class range. |
