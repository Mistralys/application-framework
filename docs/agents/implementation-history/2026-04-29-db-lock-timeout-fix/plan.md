# Plan

## Summary

Prevent InnoDB lock wait timeout errors (error 1205) during PHPUnit test bootstrapping by refactoring the framework's `TestSuiteBootstrap` class. The solution has three layers: (1) decouple system user seeding from the test bootstrap so it only verifies preconditions, (2) register a shutdown handler to roll back orphaned transactions on process termination, and (3) fix misleading database name reporting in `BaseErrorRenderer`. These changes are framework-level and automatically inherited by all consuming applications (e.g., HCP Editor).

**Research:** The root cause analysis is documented in the HCP Editor's [docs/agents/research/2026-04-29-db-lock-timeout.md](../../../../../../../hcp-editor/docs/agents/research/2026-04-29-db-lock-timeout.md).

## Architectural Context

### Test Bootstrap Chain

The PHPUnit test bootstrap follows this call chain:

```
tests/bootstrap.php
  └─ Application_Bootstrap::bootClass(TestSuiteBootstrap::class)
       └─ TestSuiteBootstrap::_boot()
            ├─ configureDatabase()     ← selects the test DB
            ├─ configurePaths()        ← loads test helper classes
            └─ configureUsers()        ← THE PROBLEM: unconditional INSERT/UPDATE in a transaction
```

**Key file:** `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php`

### The Problem

`configureUsers()` wraps `InitSystemUsers` in `startTransaction()` / `commitTransaction()` on every PHPUnit invocation. This acquires row-level InnoDB locks on `known_users`. If a previous PHPUnit process was orphaned (parent killed without terminating child), its open transaction holds these locks indefinitely. All subsequent test runs block for 50 seconds and fail with error 1205.

The system user data (`user_id=1`, `user_id=2`) is **static and deterministic** — hardcoded in `Application::getSystemUserData()`. Writing it on every bootstrap is unnecessary.

### Existing Shutdown Handler

`Bootstrap::handleShutDown()` (line ~533) fires on shutdown but only writes the request log and triggers `EVENT_SYSTEM_SHUTDOWN`. It does **not** roll back open transactions. The handler is registered via `initShutdownHandler()` during the main boot sequence.

### Relevant Files

| File | Role |
|---|---|
| `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php` | Test bootstrap — calls `configureUsers()` |
| `src/classes/Application/Users/Users.php` | `initSystemUsers()` / `initSystemUser()` — the actual INSERT/UPDATE logic |
| `src/classes/Application/Installer/Task/InitSystemUsers.php` | Installer task wrapper — calls `Users::initSystemUsers()` |
| `src/classes/Application/Bootstrap/Bootstrap.php` | Main bootstrap — owns the existing shutdown handler |
| `src/classes/Application/Bootstrap/BootException.php` | Exception class for bootstrap failures |
| `src/classes/DBHelper/Exception/BaseErrorRenderer.php` | Error rendering — hardcodes `APP_DB_NAME` constant |
| `src/classes/DBHelper/DBHelper.php` | Transaction API (`startTransaction`, `rollbackConditional`, `getSelectedDB`) |
| `src/classes/Application/Application.php` | `USER_ID_SYSTEM`, `USER_ID_DUMMY`, `getSystemUserIDs()`, `getSystemUserData()` |
| `tests/bootstrap.php` | Framework's own test bootstrapper |
| `tests/sql/testsuite.sql` | Test DB schema (~1,291 lines) |

### Key Observations from Source

**`TestSuiteBootstrap::configureUsers()` (current):**
```php
private function configureUsers(): void
{
    DBHelper::startTransaction();
    Application::createInstaller()->getTaskByID('InitSystemUsers')->process();
    DBHelper::commitTransaction();
}
```
- No `try/catch` — if `process()` throws, the transaction stays open.
- No rollback path at all.

**`Users::initSystemUser()` (called by the task):**
- Calls `DBHelper::requireTransaction()` — mandates an active transaction.
- If user exists → updates all fields and saves (acquires row lock).
- If user doesn't exist → inserts.
- Data is deterministic — the UPDATE is always a no-op in practice.

**`DBHelper::rollbackConditional()`:**
- Rolls back only if a transaction is active (no-op otherwise).
- Safe to call in shutdown handlers — will not throw.

**`DBHelper::getSelectedDB()`:**
- Returns `array{name, username, password, host, port}` for the currently selected DB.
- Throws `DBHelper_Exception` if no DB is selected.

**`BaseErrorRenderer` constructor (line 36):**
```php
$this->line('Database: '.APP_DB_USER . '@' .APP_DB_NAME . ' on '.APP_DB_HOST);
```
- Uses boot-time constants, not the currently selected DB.
- Misleading when the test DB is active — shows the main DB name.

## Approach / Architecture

### Layer 1: Decouple Seeding from Bootstrap

**Principle:** Tests should assert preconditions, not establish infrastructure.

- **`configureUsers()`** becomes verify-only: checks that system users exist via `SELECT` (non-locking read) and throws `BootException` if any are missing, with a message directing the developer to run `composer seed-tests`.
- **New public static method `seedSystemUsers()`** encapsulates the seeding logic (the current `configureUsers()` body). It wraps the transaction in try/catch with rollback-on-failure. This is callable from setup scripts in the framework and consuming applications.
- **New `tests/seed-test-db.php`** script provides the `composer seed-tests` entry point.

### Layer 2: Shutdown Handler for Transaction Cleanup

- A new private method `registerTransactionCleanupHandler()` calls `register_shutdown_function` with a static closure that invokes `DBHelper::rollbackConditional()`.
- Called in `_boot()` after `configureDatabase()`.
- Fires on fatal errors, uncaught exceptions, OOM, and normal exit — everything except `SIGKILL`.
- Scoped to `TestSuiteBootstrap` only — production code is unaffected.

### Layer 3: Fix Error Reporter Database Name

- Replace hardcoded `APP_DB_NAME` / `APP_DB_USER` / `APP_DB_HOST` constants in `BaseErrorRenderer` with a call to a new private method `resolveConnectionInfo()`.
- The method calls `DBHelper::getSelectedDB()` with a try/catch fallback to the original constants (for early boot failures before any DB is registered).

## Rationale

| Decision | Why |
|---|---|
| Verify-only bootstrap | Eliminates the write (and lock acquisition) from the bootstrap entirely. Verification via `SELECT` is a non-locking read in InnoDB. |
| Seeding as a separate step | Follows the principle of separation of concerns. DB initialization belongs in setup/build, not in the test runner. |
| Shutdown handler in `TestSuiteBootstrap` (not `Bootstrap`) | Scoped to test mode only. The main `handleShutDown()` serves all application modes and should not roll back transactions — that could mask bugs in production. |
| `seedSystemUsers()` as public static | Allows consuming applications to call it from their own setup scripts without subclassing. |
| Try/catch in `BaseErrorRenderer` | `DBHelper::getSelectedDB()` throws if no DB is selected. During early boot failures, this could cause a cascade. The fallback preserves current behavior. |
| Try/catch with rollback in `seedSystemUsers()` | The current `configureUsers()` has no error handling — if the seeding throws, the transaction stays open. The new method fixes this. |

## Detailed Steps

### Step 1: Add Seeding Script

Create `tests/seed-test-db.php`:

```php
<?php
/**
 * Seeds system users into the test database.
 * Invoked via: composer seed-tests
 *
 * @package Application
 * @subpackage Tests
 */

declare(strict_types=1);

use Application\Bootstrap\Screen\TestSuiteBootstrap;

$testsRoot = __DIR__;

define('TESTS_ROOT', $testsRoot);
define('APP_TESTS_RUNNING', true);

require_once __DIR__ . '/bootstrap.php';

TestSuiteBootstrap::seedSystemUsers();

echo "Test database seeded successfully.\n";
```

### Step 2: Add `composer seed-tests` Script

In `composer.json`, add to the `scripts` section:

```json
"seed-tests": "php tests/seed-test-db.php"
```

### Step 3: Refactor `TestSuiteBootstrap`

In `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php`:

**3a. Add imports:**

```php
use Application\AppFactory;
use Application\Bootstrap\BootException;
```

**3b. Add error code constant:**

```php
public const int ERROR_TEST_DB_NOT_SEEDED = 175001;
```

**3c. Refactor `_boot()` to include the shutdown handler:**

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
    $this->configureUsers();
}
```

**3d. Replace `configureUsers()` with verify-only logic:**

```php
private function configureUsers(): void
{
    $users = AppFactory::createUsers();
    $missingIDs = array();

    foreach(Application::getSystemUserIDs() as $id)
    {
        if(!$users->idExists($id))
        {
            $missingIDs[] = $id;
        }
    }

    if(!empty($missingIDs))
    {
        throw new BootException(
            'Test database not seeded: system user(s) missing.',
            sprintf(
                'The following system user IDs are missing from the test database: [%s]. '.
                'Run "composer seed-tests" to initialize the test environment.',
                implode(', ', $missingIDs)
            ),
            self::ERROR_TEST_DB_NOT_SEEDED
        );
    }

    $this->log('System users verified.');
}
```

**3e. Add the public seeding method:**

```php
/**
 * Seeds system users into the currently selected (test) database.
 * Called from setup/build scripts — NOT from the test bootstrap.
 *
 * Wraps the operation in a transaction with rollback-on-failure
 * to prevent orphaned locks.
 */
public static function seedSystemUsers(): void
{
    DBHelper::startTransaction();

    try
    {
        Application::createInstaller()
            ->getTaskByID('InitSystemUsers')
            ->process();

        DBHelper::commitTransaction();
    }
    catch(\Throwable $e)
    {
        DBHelper::rollbackConditional();
        throw $e;
    }
}
```

**3f. Add the shutdown handler method:**

```php
/**
 * Registers a shutdown handler that rolls back any open
 * transaction when the PHP process terminates. This prevents
 * orphaned InnoDB locks when a test process is killed or
 * crashes mid-transaction.
 */
private function registerTransactionCleanupHandler(): void
{
    register_shutdown_function(static function(): void {
        DBHelper::rollbackConditional();
    });
}
```

### Step 4: Fix `BaseErrorRenderer` Database Name

In `src/classes/DBHelper/Exception/BaseErrorRenderer.php`:

**4a. Replace the hardcoded constant line in the constructor:**

Before:
```php
$this->line('Database: '.APP_DB_USER . '@' .APP_DB_NAME . ' on '.APP_DB_HOST);
```

After:
```php
$this->line('Database: '.$this->resolveConnectionInfo());
```

**4b. Add the helper method:**

```php
private function resolveConnectionInfo(): string
{
    try
    {
        $db = DBHelper::getSelectedDB();
        return $db['username'] . '@' . $db['name'] . ' on ' . $db['host'];
    }
    catch(\Throwable $e)
    {
        // Fallback to boot-time constants if no DB is selected yet
        return APP_DB_USER . '@' . APP_DB_NAME . ' on ' . APP_DB_HOST;
    }
}
```

### Step 5: Update Framework Test Documentation

Update `docs/agents/project-manifest/testing.md`:
- Add `composer seed-tests` to the commands table.
- Add a note that the test database must be seeded before running tests.
- Document the shutdown handler behavior.

### Step 6: Run Tests

1. Run `composer seed-tests` — verify it completes without error and that system users exist in the test DB.
2. Run any test file to verify the verify-only bootstrap works.
3. Truncate `known_users` in the test DB and run a test — verify the `BootException` is thrown with the correct message.
4. Run `composer seed-tests` again to re-seed, then confirm tests pass.

## Dependencies

- **Step 3 depends on Step 1:** The verify-only bootstrap directs developers to `composer seed-tests`, which must exist first.
- **Step 4 is independent:** The `BaseErrorRenderer` fix can be done in any order.
- **Step 5 depends on Steps 1–4:** Documentation follows implementation.

## Required Components

### Modified Files

| File | Changes |
|---|---|
| `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php` | Refactor `configureUsers()` to verify-only; add `seedSystemUsers()`; add `registerTransactionCleanupHandler()`; add `ERROR_TEST_DB_NOT_SEEDED` constant; update `_boot()` |
| `src/classes/DBHelper/Exception/BaseErrorRenderer.php` | Replace hardcoded DB constants with `resolveConnectionInfo()` method |
| `composer.json` | Add `seed-tests` script |
| `docs/agents/project-manifest/testing.md` | Document `seed-tests`, seeding requirement, shutdown handler |

### New Files

| File | Purpose |
|---|---|
| `tests/seed-test-db.php` | Standalone seeding script for `composer seed-tests` |

## Assumptions

- The test database schema (`tests/sql/testsuite.sql`) includes the `known_users` table.
- `Users::idExists()` performs a non-locking `SELECT` read (standard InnoDB behavior for non-`FOR UPDATE` reads), so the verify step cannot itself cause lock contention.
- The framework's test application (`tests/application/`) has the necessary config for DB access.

## Constraints

- All code must use `array()` syntax, not `[]`.
- All new PHP code must include `declare(strict_types=1)`.
- No constructor promotion.
- Class constants must have explicit type declarations.
- The shutdown handler must use `rollbackConditional()` (not `rollbackTransaction()`) to avoid throwing if no transaction is active.
- The `BaseErrorRenderer` fix must fall back to constants if `DBHelper::getSelectedDB()` throws.

## Out of Scope

- **HCP Editor integration** — covered by a separate plan.
- **MariaDB server configuration changes** (e.g., reducing `wait_timeout`).
- **AI orchestrator terminal lifecycle management.**
- **Kill-stale-connections bootstrap step** (Approach C from the research) — unnecessary with verify-only bootstrap + shutdown handler.
- **Process-level guards** (PID files, advisory locks).
- **Refactoring `Users::initSystemUser()`** to compare data before writing — unnecessary once seeding is moved out of the bootstrap.

## Acceptance Criteria

1. **No writes to `known_users` during test bootstrap.** `configureUsers()` performs only `SELECT` queries.
2. **Clear error on unseeded DB.** If system users are missing, the bootstrap throws `BootException` with an actionable message mentioning `composer seed-tests`.
3. **`composer seed-tests` works.** Running it creates/updates system users in the test database.
4. **Shutdown handler fires on crash.** If a test triggers a fatal error mid-transaction, the transaction is rolled back (verifiable by checking `information_schema.INNODB_TRX` after a forced crash).
5. **Error messages show correct DB.** When an error occurs on the test database, `BaseErrorRenderer` displays the test DB name, not the main DB name.
6. **Existing tests pass.** All framework tests continue to pass after the changes (given a seeded test DB).

## Testing Strategy

1. Run `composer seed-tests` — verify it completes without error and system users exist in the test DB.
2. Run any test file — verify the verify-only bootstrap succeeds.
3. Manually verify the shutdown handler: add a `trigger_error(E_ERROR)` after a `startTransaction()` call in a test, run it, and confirm no orphaned transaction remains in `information_schema.INNODB_TRX`.
4. Truncate `known_users` in the test DB and run a test — verify the `BootException` is thrown with the correct message.
5. Trigger a DB error while the test DB is selected — verify the error message shows the test DB name.

## Risks & Mitigations

| Risk | Mitigation |
|---|---|
| **Developers forget to run `composer seed-tests`.** | The `BootException` message is clear and actionable. Documentation will be updated. Consuming applications can auto-seed via their setup scripts. |
| **`tests/seed-test-db.php` fails because test DB config is missing.** | The script inherits the same bootstrap path as `tests/bootstrap.php`, which already handles missing config. |
| **`BaseErrorRenderer` fallback hides the real selected DB.** | The try/catch fallback only activates if no DB is registered at all — a rare edge case during very early boot. |
| **Shutdown handler masks transaction bugs in production.** | The handler is registered only in `TestSuiteBootstrap`, not in the main `Bootstrap`. Production code is unaffected. |
