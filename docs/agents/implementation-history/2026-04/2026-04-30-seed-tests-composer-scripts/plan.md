# Plan

## Summary

Integrate the standalone `tests/seed-test-db.php` script into the `ComposerScripts` class so that test-database seeding follows the same pattern as all other Composer-driven build/maintenance tasks. This also fixes a latent chicken-and-egg bug where `composer seed-tests` cannot actually seed a fresh database because the bootstrap's user-verification check (`configureUsers()`) throws before the seeding logic runs.

## Architectural Context

### Current setup

- **Standalone script:** `tests/seed-test-db.php` — defines constants, requires `tests/bootstrap.php`, then calls `TestSuiteBootstrap::seedSystemUsers()`.
- **Composer script:** `composer.json` maps `seed-tests` → `php tests/seed-test-db.php`.
- **`ComposerScripts` class:** `src/classes/Application/Composer/ComposerScripts.php` — static methods invoked by Composer scripts. Uses `init()` to bootstrap the framework via `tests/bootstrap.php`. All build/maintenance tasks follow a `publicMethod()` → `self::init()` → `doPublicMethod()` pattern.
- **`TestSuiteBootstrap`:** `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php` — the boot screen for the test environment. Its `_boot()` method calls `configureUsers()`, which verifies that system users exist in the test database and throws `BootException` (error code `175001`) if they are missing.
- **`tests/bootstrap.php`:** Requires `Application_Bootstrap`, boots `TestSuiteBootstrap`, catches any `Throwable` and calls `exit;`.

### The chicken-and-egg problem

Both the standalone script and `ComposerScripts::init()` load `tests/bootstrap.php`, which boots `TestSuiteBootstrap::_boot()`. This calls `configureUsers()`, which throws if system users are missing — exactly the scenario where seeding is needed. The bootstrap catches the exception and calls `exit;`, so `seedSystemUsers()` never executes. This means `composer seed-tests` currently cannot seed a truly fresh (unseeded) database.

### Integration points

| Component | File |
|---|---|
| ComposerScripts | `src/classes/Application/Composer/ComposerScripts.php` |
| TestSuiteBootstrap | `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php` |
| Composer config | `composer.json` |
| Standalone script | `tests/seed-test-db.php` |
| Test bootstrap | `tests/bootstrap.php` |

## Approach / Architecture

1. **Add a seed-mode constant** (`APP_SEED_MODE`) that `ComposerScripts::seedTests()` defines before calling `init()`.
2. **Guard `configureUsers()` in `TestSuiteBootstrap::_boot()`** — skip the user-existence check when `APP_SEED_MODE` is defined. This resolves the chicken-and-egg problem.
3. **Add `seedTests()` / `doSeedTests()` to `ComposerScripts`** following the established public/do pattern.
4. **Update `composer.json`** to point `seed-tests` at the new static method.
5. **Remove the standalone script** `tests/seed-test-db.php`.

## Rationale

- **Consistency:** Every other maintenance task (cache clearing, icon rebuilding, event indexing, etc.) is a `ComposerScripts` static method. The seed task is the only outlier using a standalone script.
- **Bug fix:** The chicken-and-egg problem is resolved by introducing a seed-mode guard, which is needed regardless of whether the task is in ComposerScripts or a standalone script.
- **Simplification:** The standalone script's workaround for `TESTS_ROOT` constant ordering (documented in its docblock) is no longer necessary — `tests/bootstrap.php` already has the `if(!defined('TESTS_ROOT'))` guard, and `ComposerScripts::init()` handles bootstrapping correctly.

## Detailed Steps

### Step 1: Guard `configureUsers()` in `TestSuiteBootstrap::_boot()`

In `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php`, modify `_boot()` to skip the `configureUsers()` call when `APP_SEED_MODE` is defined:

```php
protected function _boot() : void
{
    $this->disableAuthentication();
    $this->enableScriptMode();

    $this->createEnvironment();

    if (!defined('APP_TESTS_RUNNING')) {
        define('APP_TESTS_RUNNING', true);
    }

    $this->configureDatabase();
    $this->registerTransactionCleanupHandler();
    $this->configurePaths();

    if (!defined('APP_SEED_MODE')) {
        $this->configureUsers();
    }
}
```

### Step 2: Add `seedTests()` and `doSeedTests()` to `ComposerScripts`

In `src/classes/Application/Composer/ComposerScripts.php`, add the new methods following the existing pattern. Place them after the `clearCaches()` / `doClearCaches()` block (since seeding is a maintenance task, not a build artifact):

```php
public static function seedTests() : void
{
    define('APP_SEED_MODE', true);

    self::init();

    self::doSeedTests();
}

public static function doSeedTests() : void
{
    echo '- Seeding test database...'.PHP_EOL;

    TestSuiteBootstrap::seedSystemUsers();

    echo '  DONE.'.PHP_EOL;
}
```

Add the `use` import for `TestSuiteBootstrap`:

```php
use Application\Bootstrap\Screen\TestSuiteBootstrap;
```

Key design notes:
- `APP_SEED_MODE` is defined **before** `self::init()` so it is available when `TestSuiteBootstrap::_boot()` runs.
- `seedTests()` does NOT call `self::init()` inside an `if(!self::$initialized)` check — it always defines the constant first, then delegates to `init()` (which has its own idempotency guard).
- Error handling follows the existing pattern: let exceptions propagate to Composer, which displays them and sets a non-zero exit code.

### Step 3: Update `composer.json`

Change the `seed-tests` script from the standalone PHP invocation to the ComposerScripts method:

```json
"seed-tests": "Application\\Composer\\ComposerScripts::seedTests"
```

### Step 4: Remove the standalone script

Delete `tests/seed-test-db.php`. Its logic is now fully encapsulated in `ComposerScripts::seedTests()`.

### Step 5: Update `seedSystemUsers()` docblock

In `TestSuiteBootstrap::seedSystemUsers()`, update the docblock to reflect the new invocation path. The `@see` reference should point to `ComposerScripts::seedTests()` instead of the standalone script.

### Step 6: Run `composer dump-autoload`

No new class files are added, but the import change should be verified.

### Step 7: Verify with `composer seed-tests`

Run `composer seed-tests` to confirm the integrated script works correctly.

## Dependencies

- None — all changes are within the application-framework project.

## Required Components

- `src/classes/Application/Composer/ComposerScripts.php` — add `seedTests()` and `doSeedTests()` methods
- `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php` — guard `configureUsers()` with `APP_SEED_MODE` check
- `composer.json` — update `seed-tests` script reference
- `tests/seed-test-db.php` — delete

## Assumptions

- The `APP_SEED_MODE` constant name does not conflict with any existing constant in the codebase (verified: no matches found).
- `ComposerScripts::init()` successfully bootstraps the test environment with the database configured, which is confirmed by all existing `ComposerScripts` methods that interact with the database (e.g., `indexAdminScreens`, `apiMethodIndex`).

## Constraints

- Array syntax: `array()` only — no `[]`.
- `declare(strict_types=1)` is already present in both target files.
- Follow the existing `publicMethod()` / `doPublicMethod()` pattern in `ComposerScripts`.

## Out of Scope

- Integrating seeding into the `build()` pipeline — seeding is an environment setup task, not a build artifact. It should remain a separate, explicit step.
- Adding the seed step to CI/CD pipelines — that is an infrastructure concern.
- HCP Editor integration — see the compatibility note below.

### HCP Editor Compatibility Note

The HCP Editor **cannot** call the framework's `Application\Composer\ComposerScripts::seedTests()` directly from its own `composer.json`. The framework's `init()` resolves `tests/bootstrap.php` relative to the `ComposerScripts` class file (`__DIR__.'/../../../../tests/bootstrap.php'`), which — when invoked from the HCP Editor — resolves into the framework package inside `vendor/`. This would seed the framework's test database, not the HCP Editor's.

Additionally, the HCP Editor's `Maileditor\Composer\ComposerScripts::initAutoloader()` boots `ComposerScriptBootstrap` (not `TestSuiteBootstrap`), and uses the project root `bootstrap.php`. Seeding requires `TestSuiteBootstrap` because it calls `configureDatabase()` to select the test database.

**What IS compatible:** The `APP_SEED_MODE` guard on `configureUsers()` in `TestSuiteBootstrap` is the critical framework-level change, and it works universally — both projects share the same `TestSuiteBootstrap` class. Once this guard exists, the HCP Editor can implement its own seeding entry point that:

1. Defines `APP_SEED_MODE`
2. Boots via the HCP Editor's own `tests/bootstrap.php`
3. Calls `TestSuiteBootstrap::seedSystemUsers()`

This can be done either as a standalone script (e.g. `tools/seed-test-db.php` as proposed in the HCP Editor's `2026-04-29-db-lock-timeout-fix` plan), or as a `Maileditor\Composer\ComposerScripts::seedTests()` method with its own bootstrap path.

## Acceptance Criteria

- `composer seed-tests` successfully seeds system users into a fresh (empty) test database.
- `composer seed-tests` remains idempotent — running it on an already-seeded database completes without error.
- All existing Composer scripts (`build`, `clear-caches`, `test-*`) continue to work unchanged.
- The standalone `tests/seed-test-db.php` script is removed.
- No regressions in the test suite (`composer test-filter -- SystemUser`).

## Testing Strategy

1. **Fresh database test:** Drop and recreate the test database from `tests/sql/testsuite.sql`, then run `composer seed-tests`. Verify it completes successfully.
2. **Idempotent re-run:** Run `composer seed-tests` again on the already-seeded database. Verify no errors.
3. **Test suite verification:** Run `composer test-filter -- SystemUser` to confirm the seeded users are correctly detected.
4. **Build verification:** Run `composer build` to confirm no side effects from the `TestSuiteBootstrap` change.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **`APP_SEED_MODE` leaks into test runs** | The constant is only defined in `ComposerScripts::seedTests()`, which is a dedicated entry point. PHPUnit uses `tests/bootstrap.php` directly, which does not define this constant. Test runs always execute `configureUsers()`. |
| **Existing CI/CD calls `php tests/seed-test-db.php` directly** | Search CI configuration for references to the standalone script. If found, update to `composer seed-tests`. |
| **`configureUsers()` skip hides real configuration issues during seeding** | After seeding completes, `doSeedTests()` could optionally call `configureUsers()` as a post-seed verification. However, since `seedSystemUsers()` uses a transaction with rollback-on-failure, a successful completion already guarantees the users exist. |
| **HCP Editor cannot reuse `ComposerScripts::seedTests()` directly** | By design — the framework's `init()` resolves paths relative to its own class file. The HCP Editor must provide its own entry point that boots through its own `tests/bootstrap.php`. The `APP_SEED_MODE` guard in `TestSuiteBootstrap` is the shared enabler. See the HCP Editor Compatibility Note above. |
