# Plan

## Summary

Address six pre-existing issues surfaced during the `2026-04-29-db-lock-timeout-fix` project synthesis. These span two files — `tests/bootstrap.php` and `src/classes/DBHelper/Exception/BaseErrorRenderer.php` — plus targeted hardening of `TestSuiteBootstrap`. All fixes are low-risk, narrowly scoped, and independent of each other.

## Architectural Context

The synthesis identified issues in three areas:

1. **Test bootstrap** — `tests/bootstrap.php` (the PHPUnit entry point) and `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php` (the test-environment configurator). The bootstrap uses `const` at file scope, which is compile-time and cannot be guarded with `defined()`. `TestSuiteBootstrap` is the MVC bootstrap screen that configures the database, paths, and system users for the test suite.

2. **DB error rendering** — `src/classes/DBHelper/Exception/BaseErrorRenderer.php` renders diagnostic information when a database query fails. Its `analyzeQuery()` method has a logic inversion bug and a PHPStan-level typing gap.

3. **Test database constants** — `TestSuiteBootstrap::configureDatabase()` references `APP_DB_TESTS_*` constants without guards, which produces `Error` exceptions in PHP 8.4+ if the constants are undefined.

### Key files

| File | Role |
|---|---|
| `tests/bootstrap.php` | PHPUnit bootstrap entry point |
| `tests/seed-test-db.php` | Standalone seeder script (requires `bootstrap.php`) |
| `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php` | Test environment configurator |
| `src/classes/DBHelper/Exception/BaseErrorRenderer.php` | DB error diagnostic renderer |

## Approach / Architecture

Six independent fixes, each small enough to be its own work package:

1. **Fix the `TESTS_ROOT` double-define warning** — Change `tests/bootstrap.php` line 21 from `const TESTS_ROOT = __DIR__` to a `defined()` guard using `define()`. This eliminates the E_WARNING emitted whenever `seed-test-db.php` (or any other entry point) pre-defines the constant.

2. **Fix the `analyzeQuery()` logic inversion** — In `BaseErrorRenderer::analyzeQuery()`, change `if(!$errors)` to `if($errors)` so the "Placeholders have inconsistencies" note prints only when inconsistencies were actually detected.

3. **Add PHPDoc type annotation to `analyzeQuery()` `$values` parameter** — Add `@param array<string, mixed> $values` to resolve the PHPStan level-max error on the bare `array` type.

4. **Guard `configureUsers()` against an empty system user list** — Add an early guard: if `Application::getSystemUserIDs()` returns an empty array, throw a `BootException` indicating that the system user list is misconfigured. This prevents the method from silently reporting "System users verified." when zero users were actually checked.

5. **Replace `die()` with `BootException` in `configurePaths()`** — The `die('Cannot run tests: ...')` call is inconsistent with the rest of the bootstrap, which uses `BootException` for all error conditions. Replace it with a `BootException` throw for consistency and to make the failure testable. (Note: `die()` *does* trigger shutdown handlers, so the transaction cleanup handler is not at risk — this is purely a consistency and testability improvement.)

6. **Guard `configureDatabase()` against undefined `APP_DB_TESTS_*` constants** — Add `defined()` checks for `APP_DB_TESTS_NAME`, `APP_DB_TESTS_USER`, `APP_DB_TESTS_PASSWORD`, and `APP_DB_TESTS_HOST` before they are referenced. If any are missing, throw a `BootException` with a clear message naming the missing constant(s). The existing guard on `APP_DB_TESTS_PORT` already demonstrates this pattern.

## Rationale

- All six items are pre-existing issues confirmed in the current codebase — none were introduced by the lock-timeout-fix project.
- Each fix is independent, so they can be implemented and tested in any order.
- The `TESTS_ROOT` fix (#1) directly eliminates CI noise on every `composer seed-tests` invocation.
- The `analyzeQuery()` inversion (#2) is a functional bug that causes misleading error output.
- Fixes #4–#6 harden the test bootstrap against misconfigured environments, converting silent failures or ungraceful `die()` calls into structured `BootException` errors.

## Detailed Steps

### Step 1 — Fix `TESTS_ROOT` double-define in `tests/bootstrap.php`

**File:** `tests/bootstrap.php` (line 21)

Change:

```php
const TESTS_ROOT = __DIR__;
```

To:

```php
if(!defined('TESTS_ROOT')) {
    define('TESTS_ROOT', __DIR__);
}
```

This replaces the compile-time `const` (which cannot be conditionally guarded) with a runtime `define()` behind a `defined()` check, matching the pattern already used in `TestSuiteBootstrap::_boot()` for `APP_TESTS_RUNNING`.

### Step 2 — Fix `analyzeQuery()` logic inversion in `BaseErrorRenderer`

**File:** `src/classes/DBHelper/Exception/BaseErrorRenderer.php` (around line 147 in current source)

Change:

```php
if(!$errors) {
    $this->line('NOTE: Placeholders have inconsistencies.');
}
```

To:

```php
if($errors) {
    $this->line('NOTE: Placeholders have inconsistencies.');
}
```

### Step 3 — Add PHPDoc type to `analyzeQuery()` `$values` parameter

**File:** `src/classes/DBHelper/Exception/BaseErrorRenderer.php`

Add a PHPDoc block above `analyzeQuery()` with only the `$values` type annotation (the `$sql` and return types are already declared in the method signature and do not need redundant annotations):

```php
/**
 * @param array<string,mixed> $values
 */
private function analyzeQuery(string $sql, array $values) : void
```

### Step 4 — Guard `configureUsers()` against empty system user list

**File:** `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php`, method `configureUsers()`

Extract `Application::getSystemUserIDs()` into a local variable (it is currently called inline in the `foreach`), then add an empty-array guard before the loop:

```php
private function configureUsers(): void
{
    $users = AppFactory::createUsers();
    $systemUserIDs = Application::getSystemUserIDs();

    if(empty($systemUserIDs))
    {
        throw new BootException(
            'No system users configured.',
            'Application::getSystemUserIDs() returned an empty array. ' .
            'At least one system user ID must be configured for the test environment.',
            self::ERROR_NO_SYSTEM_USERS_CONFIGURED
        );
    }

    $missingIDs = array();

    foreach($systemUserIDs as $id)
    {
        if(!$users->idExists($id))
        {
            $missingIDs[] = $id;
        }
    }

    // ... rest unchanged
}
```

Add a new error code constant to the class:

```php
public const int ERROR_NO_SYSTEM_USERS_CONFIGURED = 175002;
```

> **Note:** The error code `175002` follows the project's convention (class ID `175`, error number `002`). The previous error code in this class is `175001`.

### Step 5 — Replace `die()` with `BootException` in `configurePaths()`

**File:** `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php`, method `configurePaths()`

Change:

```php
if (!is_dir($testsRoot)) {
    die('Cannot run tests: Could not find the application\'s [tests] folder.');
}
```

To:

```php
if (!is_dir($testsRoot)) {
    throw new BootException(
        'Cannot run tests: tests folder not found.',
        sprintf(
            'The expected tests folder [%s] does not exist.',
            $testsRoot
        ),
        self::ERROR_TESTS_FOLDER_NOT_FOUND
    );
}
```

Add a new error code constant:

```php
public const int ERROR_TESTS_FOLDER_NOT_FOUND = 175003;
```

### Step 6 — Guard `configureDatabase()` against undefined constants

**File:** `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php`, method `configureDatabase()`

Add a check at the top of the method for the four required constants:

```php
private function configureDatabase(): void
{
    $requiredConstants = array(
        'APP_DB_TESTS_NAME',
        'APP_DB_TESTS_USER',
        'APP_DB_TESTS_PASSWORD',
        'APP_DB_TESTS_HOST'
    );

    $missing = array();
    foreach($requiredConstants as $name)
    {
        if(!defined($name))
        {
            $missing[] = $name;
        }
    }

    if(!empty($missing))
    {
        throw new BootException(
            'Test database constants not configured.',
            sprintf(
                'The following required constants are not defined: [%s]. ' .
                'Ensure they are set in the test configuration file.',
                implode(', ', $missing)
            ),
            self::ERROR_TEST_DB_CONSTANTS_MISSING
        );
    }

    // ... rest of method unchanged
}
```

Add a new error code constant:

```php
public const int ERROR_TEST_DB_CONSTANTS_MISSING = 175004;
```

## Dependencies

- None between the six steps — all are independent and can be implemented in any order.
- All changes are within the Application Framework (`application-framework`).

## Required Components

| Component | Type | Action |
|---|---|---|
| `tests/bootstrap.php` | Existing file | Modify (Step 1) |
| `src/classes/DBHelper/Exception/BaseErrorRenderer.php` | Existing file | Modify (Steps 2–3) |
| `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php` | Existing file | Modify (Steps 4–6) |

No new files are required. No autoload dump needed (no files added/renamed).

## Assumptions

- Error codes `175002`, `175003`, and `175004` are available (not used elsewhere). The previous code `175001` was added in the preceding project for this same class, so the sequence is consistent.
- `Application::getSystemUserIDs()` should always return at least one ID in a properly configured environment.
- The `BootException` class is already imported in `TestSuiteBootstrap` (confirmed — see use statement at line 7).

## Constraints

- Must use `array()` syntax for all array creation (project hard rule).
- Must use `declare(strict_types=1)` in all PHP files (already present in all three target files).
- Do not refactor surrounding code — each fix is surgically scoped to the identified issue.

## Out of Scope

- Refactoring the `analyzeQuery()` method beyond the inversion fix and type annotation.
- Adding unit tests for `BaseErrorRenderer::analyzeQuery()` (it is a private method in an abstract class — testing via its subclasses is appropriate but not part of this focused fix plan).
- The `seed-test-db.php` script itself — it already documents the `TESTS_ROOT` issue in its docblock; once Step 1 is implemented, the warning it documents will be eliminated.
- Updating `seed-test-db.php`'s docblock to remove the now-resolved technical debt note (minor, can be done opportunistically).
- Verifying `composer seed-tests` in the HCP Editor — that is a separate integration verification task.

## Acceptance Criteria

1. Running `composer seed-tests` no longer emits an `E_WARNING` about `TESTS_ROOT already defined`.
2. When `analyzeQuery()` detects placeholder inconsistencies, the "NOTE: Placeholders have inconsistencies." line is printed. When there are no inconsistencies, it is not printed.
3. Running `composer analyze` does not report a type error on the `$values` parameter of `analyzeQuery()`.
4. If `Application::getSystemUserIDs()` returns an empty array, `configureUsers()` throws a `BootException` with code `175002`.
5. If the tests folder does not exist, `configurePaths()` throws a `BootException` with code `175003` instead of calling `die()`.
6. If any of the four `APP_DB_TESTS_*` constants are undefined, `configureDatabase()` throws a `BootException` with code `175004` naming the missing constant(s).
7. The existing test suite (`composer test-filter -- TestSuiteBootstrap`) continues to pass.

## Testing Strategy

- **Steps 1–3:** Run `composer seed-tests` to confirm no E_WARNING. Run `composer analyze` to confirm PHPStan passes on the `$values` parameter. The `analyzeQuery()` inversion fix (Step 2) cannot be exercised by automated tests — it is a private method in an abstract class only invoked during DB errors. Its correctness must be verified by code review and static analysis. This is a known testing gap.
- **Steps 4–6:** Run `composer test-filter -- TestSuiteBootstrap` to confirm existing tests still pass. The new guards only trigger in misconfigured environments, so they will not fire during normal test execution — their correctness is verified by code review and static analysis.
- **All steps:** Run `composer analyze` to confirm no PHPStan regressions.

## Risks & Mitigations

| Risk | Mitigation |
|---|---|
| **Changing `const` to `define()` in `bootstrap.php` breaks PHPUnit bootstrap** | Both `const` and `define()` create the same global constant. PHPUnit loads `bootstrap.php` as the entry point, so `defined()` will be false and `define()` will execute — identical behavior to the current `const`. |
| **New `BootException` throws in `configureDatabase()` / `configurePaths()` cause test failures** | These only trigger when constants are undefined or the tests folder is missing — conditions that already cause fatal errors or `die()` today. The change converts silent/ungraceful failures into structured exceptions. |
| **Error codes `175002`–`175004` collide with existing codes** | The `175` prefix was introduced in the preceding project (`175001`). A grep for these codes confirms they are not used elsewhere. |
