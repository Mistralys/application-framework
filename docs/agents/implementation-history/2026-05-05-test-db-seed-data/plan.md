# Plan: Test Database Seed Infrastructure (Framework)

## Summary

Extend the framework's `composer seed-tests` infrastructure with three new capabilities: a truncate-all-tables reset step, locale seeding, and country seeding. These are framework-level concerns reusable by any application built on the framework. The HCP Editor will build on this foundation with its own application-specific seeding (separate plan).

## Architectural Context

### Existing Infrastructure

- **`composer seed-tests`** is defined in `composer.json` and calls `Application\Composer\ComposerScripts::seedTests()`.
- **`ComposerScripts::seedTests()`** (`src/classes/Application/Composer/ComposerScripts.php`) defines `APP_SEED_MODE`, calls `self::init()` (boots the application in seed mode), then calls `self::doSeedTests()`.
- **`ComposerScripts::doSeedTests()`** currently only calls `TestSuiteBootstrap::seedSystemUsers()`.
- **`TestSuiteBootstrap::seedSystemUsers()`** (`src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php`) wraps the `InitSystemUsers` installer task in a manual transaction: `startTransaction()` → task → `commitTransaction()`, with `rollbackConditional()` in the catch block.
- **`APP_SEED_MODE`:** When defined, `TestSuiteBootstrap::_boot()` skips `configureUsers()` (the check that system users exist), allowing the seeder to boot before users are inserted.
- **`DBHelper::truncate(string $tableName)`** (`src/classes/DBHelper/DBHelper.php`) executes `TRUNCATE TABLE` on the given table.
- **FK checks:** No standalone helper exists. The pattern used in `getDropTablesQuery()` is: `DBHelper::execute(DBHelper_OperationTypes::TYPE_UPDATE, "SET FOREIGN_KEY_CHECKS=0")`.
- **Locale tables** (`locales_application`, `locales_content`) are single-column tables (`locale_name varchar(5)`) with no ORM — data must be inserted via `DBHelper::insertDynamic()`.
- **Country creation** uses `Application_Countries::createNewCountry(string $iso, string $label)` and `createInvariantCountry()` for the ZZ invariant country. Note: only `createInvariantCountry()` is idempotent (checks existence first). `createNewCountry()` throws `CountryException` (`ERROR_ISO_ALREADY_EXISTS`) if the ISO already exists — therefore `seedCountries()` must only run after `truncateAllTables()` has cleared the table, or must guard each insert with an `isoExists()` check.
- The framework's own test SQL (`tests/sql/testsuite.sql`) seeds locales and countries via raw SQL, confirming these are framework-level concerns. This file will be removed because its responsibilities are now fully covered by importing the pristine schema SQL followed by the programmatic seeder.
- **`createTestLocale()`** in `ApplicationTestCase` fetches a random `locale_name` from `locales_application` — requires at least one row to exist.

### Entry Points for Seeding

Multiple CLI pathways invoke `composer seed-tests` — none require code changes, but implementers should be aware of them:

- **`composer seed-tests`** — standalone command, runs the seeder directly.
- **`composer setup`** (`tools/setup-local.php`) — interactive local environment setup; imports the schema SQL and calls `composer seed-tests` as its final step.
- **Developer menu** (`tools/menu.php`, option 5) — calls `composer seed-tests`.

### Key Files

| File | Purpose |
|---|---|
| `src/classes/Application/Composer/ComposerScripts.php` | Composer script entry points, `doSeedTests()` |
| `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php` | Test boot, `seedSystemUsers()` |
| `src/classes/Application/Installer/Task/InitSystemUsers.php` | Only existing installer task |
| `src/classes/Application/Installer/Task.php` | Installer task base class |
| `src/classes/Application/Countries/Countries.php` | `createNewCountry()`, `createInvariantCountry()` |
| `src/classes/Application/Countries/Country.php` | `COUNTRY_INDEPENDENT_ISO` constant |
| `src/classes/DBHelper/DBHelper.php` | `truncate()`, `execute()`, `insertDynamic()`, transaction methods |
| `tests/AppFrameworkTestClasses/ApplicationTestCase.php` | `createTestLocale()`, `createTestCountry()` |

## Approach / Architecture

Add three new public static methods to `TestSuiteBootstrap`, following the established `seedSystemUsers()` pattern, then wire them into `ComposerScripts::doSeedTests()`:

1. **`truncateAllTables()`** — Disables FK checks, truncates every table in the database via `SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'`, re-enables FK checks in a `try/finally` block to guarantee re-enablement even on failure. Runs outside a transaction (MySQL `TRUNCATE` is DDL and auto-commits).

2. **`seedLocales()`** — Inserts `de_DE` and `en_UK` into both `locales_application` and `locales_content` via `DBHelper::insertDynamic()`. Wrapped in a transaction.

3. **`seedCountries()`** — Creates the invariant country (ZZ) via `createInvariantCountry()` and 8 test countries (DE, CA, FR, IT, ES, GB, US, MX) via `createNewCountry()`. Wrapped in a transaction. The country ISOs and labels are defined as a constant array on `TestSuiteBootstrap`.

The updated `doSeedTests()` execution order:
```
doSeedTests()
  ├─ truncateAllTables()      ← FK off, TRUNCATE *, FK on
  ├─ seedSystemUsers()        ← known_users (IDs 1, 2)
  ├─ seedLocales()            ← locales_application + locales_content
  └─ seedCountries()          ← 8 countries + ZZ invariant
```

The new local environment setup flow becomes:
1. Create the database (if needed).
2. Import `docs/sql/pristine.sql` (schema only, no test data).
3. Run `composer seed-tests` (populates test data programmatically).

## Rationale

- **Locales and countries are framework concepts.** The framework's own test infrastructure (`createTestLocale()`, `createTestCountry()`) depends on them. Seeding them in the framework benefits all applications.
- **Truncate-and-reseed** is simpler than per-row idempotency checks. Disabling FK checks during truncation makes table ordering irrelevant.
- **Static methods on `TestSuiteBootstrap`** follow the existing pattern established by `seedSystemUsers()`. No new class hierarchy needed.
- **The `doSeedTests()` public helper** allows application-level ComposerScripts to call the framework seeding first, then add their own steps — the established delegation pattern.

## Detailed Steps

### Step 1: Add `TestSuiteBootstrap::truncateAllTables()`

**File:** `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php`

Add a new public static method that:
1. Executes `SET FOREIGN_KEY_CHECKS=0` via `DBHelper::execute()`.
2. In a `try` block:
   a. Fetches all base table names via `SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'`.
   b. Calls `DBHelper::truncate()` for each table.
3. In a `finally` block: executes `SET FOREIGN_KEY_CHECKS=1`.

The `try/finally` guarantees FK checks are re-enabled even if a truncation fails mid-loop (following the safety pattern used in `DBHelper::getDropTablesQuery()`).

This method does not use a transaction (MySQL `TRUNCATE` is DDL and auto-commits).

### Step 2: Add `TestSuiteBootstrap::seedLocales()`

**File:** `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php`

Add a new public static method following the `seedSystemUsers()` transaction pattern:
1. Start transaction.
2. Insert `de_DE` and `en_UK` into `locales_application` via `DBHelper::insertDynamic('locales_application', array('locale_name' => $name))`.
3. Insert `de_DE` and `en_UK` into `locales_content` via `DBHelper::insertDynamic('locales_content', array('locale_name' => $name))`.
4. Commit transaction (rollback on failure).

The locale names should be defined as a class constant for clarity:
```php
public const array SEED_LOCALES = array('de_DE', 'en_UK');
```

### Step 3: Add `TestSuiteBootstrap::seedCountries()`

**File:** `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php`

Add a new public static method:
1. Start transaction.
2. Call `AppFactory::createCountries()->createInvariantCountry()` for ZZ.
3. For each entry in the constant array: check `isoExists($iso)` first — if the country already exists, skip it; otherwise call `createNewCountry($iso, $label)`. This makes `seedCountries()` safe to call even without a preceding truncation (defensive against partial re-runs).
4. Commit transaction (rollback on failure).

Define the country data as a class constant:
```php
// These 8 countries cover the framework's test needs (country tests, locale
// tests, filter criteria) without over-seeding. Downstream applications add
// their own countries via application-level seeders. The previous testsuite.sql
// included pl, ro, at — these are not required by any framework test and are
// omitted to keep the seed minimal.
//
// Note: The old testsuite.sql used 'uk' for United Kingdom. We use 'gb' here
// because it is the correct ISO 3166-1 alpha-2 code. The framework's
// CountryCollection::filterCode() normalizes 'uk' → 'gb', so lookups via
// either code continue to work. No framework test uses raw SQL with
// WHERE iso = 'uk'.
public const array SEED_COUNTRIES = array(
    'de' => 'Germany',
    'ca' => 'Canada',
    'fr' => 'France',
    'it' => 'Italy',
    'es' => 'Spain',
    'gb' => 'United Kingdom',
    'us' => 'United States',
    'mx' => 'Mexico',
);
```

### Step 4: Remove `tests/sql/testsuite.sql`

Delete the file `tests/sql/testsuite.sql`. Its responsibilities (schema + test data) are now split between:
- **Schema:** `docs/sql/pristine.sql` (imported during `composer setup`).
- **Test data:** `composer seed-tests` (programmatic seeding).

Update `tools/setup-local.php` to import `docs/sql/pristine.sql` instead of `tests/sql/testsuite.sql` when creating the test database.

Update the `AGENTS.md` "Local Environment Setup" section to reference `docs/sql/pristine.sql` instead of `tests/sql/testsuite.sql`.

### Step 5: Update `ComposerScripts::doSeedTests()`

**File:** `src/classes/Application/Composer/ComposerScripts.php`

Update the method to call all seed steps in order, with `echo` progress messages matching the existing style:

```php
public static function doSeedTests() : void
{
    echo '- Seeding test database...'.PHP_EOL;

    TestSuiteBootstrap::truncateAllTables();
    TestSuiteBootstrap::seedSystemUsers();
    TestSuiteBootstrap::seedLocales();
    TestSuiteBootstrap::seedCountries();

    echo '  DONE.'.PHP_EOL;
}
```

## Dependencies

- No external dependencies. All required classes and methods already exist in the framework.
- Steps 1–3 (new methods on `TestSuiteBootstrap`) are independent of each other and can be implemented in any order.
- Step 4 (file removal) is independent but should be done after Step 5 is ready.
- Step 5 (wiring into `doSeedTests()`) depends on Steps 1–3 being complete.

## Required Components

### Modified Files

| File | Changes |
|---|---|
| `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php` | Add `truncateAllTables()`, `seedLocales()`, `seedCountries()`, and related constants |
| `src/classes/Application/Composer/ComposerScripts.php` | Update `doSeedTests()` to call new seed methods |
| `tools/setup-local.php` | Update DB import path from `tests/sql/testsuite.sql` to `docs/sql/pristine.sql` |
| `AGENTS.md` | Update "Local Environment Setup" section to reference `docs/sql/pristine.sql` |

### Deleted Files

| File | Reason |
|---|---|
| `tests/sql/testsuite.sql` | Replaced by pristine schema SQL + programmatic seeding |

### New Files

None.

## Assumptions

- The test database has the full schema applied via `docs/sql/pristine.sql`. The seeder populates data, not structure.
- `APP_SEED_MODE` correctly bypasses the user existence check in `TestSuiteBootstrap::_boot()`.
- After `ComposerScripts::init()`, `AppFactory` is fully initialized and all factory methods are available, including `createCountries()`. The seed mode only skips `configureUsers()` — it does not affect factory registration.
- MySQL's `SET FOREIGN_KEY_CHECKS=0` allows `TRUNCATE TABLE` on tables with FK references.
- The locale names `de_DE` and `en_UK` match the values used in the framework's own test infrastructure and the `APP_UI_LOCALES` / `APP_CONTENT_LOCALES` constants configured in test environments.
- `docs/sql/pristine.sql` (50 tables) is a superset of `tests/sql/testsuite.sql` (41 tables) — all tables required by the seeder exist in the pristine schema.

## Constraints

- **Array syntax:** All code must use `array()`, not `[]`.
- **`declare(strict_types=1)`:** Already present in both files; maintain it.
- **Typed constants:** New class constants must have explicit type declarations.
- **No constructor promotion.**

## Out of Scope

- Application-specific seeding (business areas, tenants, templates) — handled by the HCP Editor plan.
- Refactoring existing test helpers (`createTestLocale()`, `createTestCountry()`).
- Adding new installer task classes (the framework seeding uses direct API calls, not the installer task infrastructure).

## Acceptance Criteria

1. Running `composer seed-tests` from a framework-based application completes without errors.
2. After seeding, the test database contains:
   - `known_users`: 2 rows (system, dummy). Note: the previous `testsuite.sql` contained a third user (ID 3) which was not a system user; no test depends on it and it is intentionally not seeded.
   - `locales_application`: 2 rows (de_DE, en_UK).
   - `locales_content`: 2 rows (de_DE, en_UK).
   - `countries`: 9 rows (8 test countries + ZZ invariant).
3. Running `composer seed-tests` a second time produces the same result (truncate-and-reseed is idempotent).
4. All tables not in the seed set are empty after seeding (truncate clears everything).
5. The file `tests/sql/testsuite.sql` no longer exists.
6. `tools/setup-local.php` imports `docs/sql/pristine.sql` for schema creation.
7. The framework's own test suite continues to pass after these changes (existing behavior preserved).

## Testing Strategy

1. **Manual seeder verification:** Run `composer seed-tests`, then query the database to verify row counts for `known_users`, `locales_application`, `locales_content`, and `countries`.
2. **Idempotency check:** Run `composer seed-tests` twice in succession; verify no errors and identical row counts.
3. **Framework test smoke test:** Run `composer test-filter -- Countries` and `composer test-filter -- Locale` to verify country and locale test infrastructure works with seeded data.

## Risks & Mitigations

| Risk | Mitigation |
|---|---|
| **`TRUNCATE TABLE` fails despite `FOREIGN_KEY_CHECKS=0`** | MySQL respects `FOREIGN_KEY_CHECKS=0` for `TRUNCATE`. If issues arise on specific MySQL versions, fall back to `DELETE FROM` + `ALTER TABLE AUTO_INCREMENT = 1` per table. |
| **~~`SHOW TABLES` returns views or temporary tables~~** | Mitigated by design: `truncateAllTables()` uses `SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'` as the primary query, eliminating this risk proactively. |
| **Truncating all tables breaks a downstream application's seed assumptions** | The `doSeedTests()` method is a helper called by application scripts. Applications that need additional data call `doSeedTests()` first, then add their own seeds — the truncate step resets everything to a known-empty state before any seeding begins. |
| **Country auto-increment IDs differ from old `testsuite.sql` values** | No framework test code relies on specific numeric country IDs (verified via grep; full confirmation requires running `composer test-filter -- Countries`). All lookups use ISO codes. |
| **`setup-local.php` references old SQL file** | Step 4 explicitly updates the import path to `docs/sql/pristine.sql`. |
| **`pristine.sql` missing tables from `testsuite.sql`** | Verify before deleting `testsuite.sql` that all 41 tables from `testsuite.sql` exist in `pristine.sql` (which has 50 tables — likely a superset, but confirm). |
