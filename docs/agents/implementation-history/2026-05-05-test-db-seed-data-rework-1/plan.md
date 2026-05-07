# Plan

## Summary

Follow-up rework addressing the strategic recommendations from the `2026-05-05-test-db-seed-data` synthesis. This plan covers: (1) refactoring the seed command to use process isolation instead of brittle manual ORM cache resets, (2) harmonizing the seed idempotency contract so all methods are safely re-runnable, (3) fixing stale documentation references to the deleted `tests/sql/testsuite.sql`, and (4) fixing the `$port` defensive initialization in `tools/setup-local.php`.

The test isolation gap (synthesis recommendation #3) was investigated and determined to be a **non-issue**: no test class calls `truncateAllTables()`, and all tests use per-test transactions that roll back in `tearDown()`. This item requires no code change.

The HCP Editor application-specific seeding (synthesis recommendation #5) is out of scope — it belongs in the HCP Editor project as a separate plan.

## Architectural Context

The seed infrastructure was implemented in the prior plan with this flow:

```
composer seed-tests
  → ComposerScripts::seedTests()         [PHP callback, runs in Composer's process]
    → define('APP_SEED_MODE', true)
    → init()                              [loads tests/bootstrap.php, boots framework]
    → doSeedTests()
      → truncateAllTables()               [DDL: TRUNCATE all base tables]
      → resetCollection() × N            [manually invalidate ORM caches]
      → seedSystemUsers()
      → seedLocales()
      → seedCountries()
```

The ORM cache invalidation after `truncateAllTables()` is required because `init()` boots the full framework (loading `tests/bootstrap.php` → `Application_Bootstrap::init()` → `bootClass(TestSuiteBootstrap::class)`), which may populate singleton collection caches (via `AppFactory::$instances` and individual `::$instance` statics). After truncation, these caches hold references to now-deleted rows.

**Key singleton mechanisms:**
- `AppFactory::$instances` — factory-level static array cache (`createClassInstance()`)
- `Application_Countries::$instance` — class-level `getInstance()` singleton
- `BaseCollection::$idLookup` / `$records` / `$allRecords` — per-collection internal memory caches

Currently, developers must add a `resetCollection()` call for EACH collection that might be cached before truncation. This is a maintenance trap documented in `testing.md` as a maintenance note.

## Approach / Architecture

### Process Isolation for Seeding

Replace the single-process PHP callback with a **two-phase CLI approach**:

1. **Phase 1 — Truncation** (`php tools/seed-truncate.php`): Boots the framework in seed mode, truncates all tables, exits. Process terminates — all ORM caches are destroyed with it.

2. **Phase 2 — Insertion** (`php tools/seed-insert.php`): Boots the framework in seed mode in a fresh process. Since the process just started, NO ORM caches exist. Calls all seed methods sequentially. No `resetCollection()` calls needed.

Composer script definition:
```json
"seed-tests": [
    "php tools/seed-truncate.php",
    "php tools/seed-insert.php"
]
```

Each element in a Composer script array runs as a separate shell command (= separate PHP process). This provides natural process isolation without any code complexity.

### Idempotency Harmonization

Make `seedLocales()` idempotent by adding an existence check before each insert (matching the `seedCountries()` pattern). This eliminates the mixed-contract maintenance trap.

### Documentation and Code Quality Fixes

- Fix 2 stale references to `tests/sql/testsuite.sql` in `docs/agents/project-manifest/testing.md`
- Add defensive `$port = 'null'` initialization before the do-while loop in `tools/setup-local.php`

## Rationale

**Why process isolation over `resetAllCollections()` helper:**

The synthesis recommended a `resetAllCollections()` helper. While that reduces per-developer burden, it still requires:
- Maintaining an exhaustive list of all framework-level ORM singletons
- Updating the list when new collections are added
- Running correctly regardless of which collections were actually populated

Process isolation eliminates the problem at its root: after truncation completes and the process exits, ALL in-memory state (including static properties, singletons, and ORM caches) is destroyed. The insertion process starts clean with zero stale state. This is:
- **Zero-maintenance:** No list of collections to maintain
- **Future-proof:** Works automatically as new collections are added
- **Simpler mental model:** Each phase has a clean environment

**Performance cost:** Two process bootstraps (~0.5–1s each) instead of one. Total ~1–2s for the seed command. Acceptable for a developer-facing CLI tool.

**Why make `seedLocales()` idempotent:**
- Consistent contract across all seed methods (no ordering surprises)
- Safe for partial re-runs during development
- Matches the pattern already used by `seedCountries()` and `seedSystemUsers()`

## Detailed Steps

### Step 1: Create `tools/seed-truncate.php`

Create a minimal CLI script that:
1. Defines `APP_SEED_MODE = true`
2. Requires the test bootstrap
3. Calls `TestSuiteBootstrap::truncateAllTables()`
4. Prints a status message

**New file:** `tools/seed-truncate.php`

### Step 2: Create `tools/seed-insert.php`

Create a CLI script that:
1. Defines `APP_SEED_MODE = true`
2. Requires the test bootstrap
3. Calls `TestSuiteBootstrap::seedSystemUsers()`, `TestSuiteBootstrap::seedLocales()`, `TestSuiteBootstrap::seedCountries()` in sequence
4. Prints a status message

**Note:** This script calls the seed methods directly — it does NOT call `doSeedTests()`. The process isolation between `seed-truncate.php` and `seed-insert.php` eliminates the need for `resetCollection()` calls without changing the `doSeedTests()` contract.

**New file:** `tools/seed-insert.php`

### Step 3: Update Composer script definition

Change `composer.json`:
```json
"seed-tests": [
    "php tools/seed-truncate.php",
    "php tools/seed-insert.php"
]
```

### Step 4: Refactor `ComposerScripts`

- Remove `seedTests()` static method (no longer called as a Composer PHP callback)
- Keep `doSeedTests()` **self-contained**: it retains `truncateAllTables()` + `resetCollection()` calls + all three seed calls. This preserves the existing contract for programmatic callers (the HCP Editor's `ComposerScripts::seedTests()` calls `doSeedTests()` expecting truncation + seeding in one shot).
- The `resetCollection()` calls remain necessary inside `doSeedTests()` because programmatic callers run in a single process (no process isolation). They are harmless but required for correctness in that context.
- Update the `doSeedTests()` docblock to note that it is intended for programmatic callers; the Composer `seed-tests` script uses the process-isolated CLI scripts instead.

**Rationale:** The CLI scripts (`seed-truncate.php` / `seed-insert.php`) achieve process isolation by calling the low-level methods directly. `doSeedTests()` keeps its original contract intact so downstream projects (HCP Editor) do not need changes.

### Step 5: Make `seedLocales()` idempotent

In `TestSuiteBootstrap::seedLocales()`, add an existence check before each insert using `DBHelper::recordExists()` (confirmed at `src/classes/DBHelper/DBHelper.php:1914`):
```php
foreach(self::SEED_LOCALES as $name)
{
    if(!DBHelper::recordExists('locales_application', array('locale_name' => $name)))
    {
        DBHelper::insertDynamic('locales_application', array('locale_name' => $name));
    }
    if(!DBHelper::recordExists('locales_content', array('locale_name' => $name)))
    {
        DBHelper::insertDynamic('locales_content', array('locale_name' => $name));
    }
}
```

Update the PHPDoc to remove the non-idempotency warning.

### Step 6: Fix `$port` defensive initialization

In `tools/setup-local.php`, add `$port = 'null';` before the do-while loop at line ~202 to satisfy static analysis and make the code's intent explicit.

### Step 7: Fix stale documentation references

In `docs/agents/project-manifest/testing.md`, replace:
- Line 230: `tests/sql/testsuite.sql` → `docs/sql/pristine.sql`
- Line 238: `After importing tests/sql/testsuite.sql` → `After importing docs/sql/pristine.sql`

### Step 8: Update seed documentation in `testing.md`

Update the "Seeding the Test Database" section to reflect the new process-isolated architecture:
- Remove references to `APP_SEED_MODE` internal implementation
- Remove the "Maintenance note" about manual `resetCollection()` calls (no longer applicable)
- Document the two-phase process model
- Remove the non-idempotency warning from the Seeding Locales subsection

## Dependencies

- Prior plan `2026-05-05-test-db-seed-data` must be fully committed (it is — status COMPLETE)
- No external dependencies

## Required Components

- `tools/seed-truncate.php` (new)
- `tools/seed-insert.php` (new)
- `composer.json` (modify `seed-tests` script)
- `src/classes/Application/Composer/ComposerScripts.php` (remove `seedTests()`, update `doSeedTests()` docblock)
- `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php` (make `seedLocales()` idempotent)
- `tools/setup-local.php` (defensive `$port` initialization)
- `docs/agents/project-manifest/testing.md` (fix stale refs + update seed docs)

## Assumptions

- The framework's test bootstrap (`tests/bootstrap.php`) works correctly when `APP_SEED_MODE` is defined before requiring it
- The existence check uses `DBHelper::recordExists(string $table, array $where) : bool` (verified at `src/classes/DBHelper/DBHelper.php:1914`)
- Composer script arrays execute each element as a separate shell process (confirmed by Composer documentation)

## Constraints

- Array syntax: `array()` only (project rule)
- All PHP files must have `declare(strict_types=1)`
- Run `composer dump-autoload` is NOT needed (no new class files; only CLI scripts)
- The `doSeedTests()` public method must be preserved for programmatic use (the HCP Editor may call it)
- The `APP_SEED_MODE` constant guard in `TestSuiteBootstrap::_boot()` remains unchanged

## Out of Scope

- **HCP Editor application-specific seeding** — separate project, separate plan
- **Test isolation between suites** — investigated and confirmed as non-issue (tests use per-test transactions; no test calls `truncateAllTables()`)
- **`resetAllCollections()` helper** — superseded by the process-isolation approach; no longer needed
- **PHPStan baseline for `tools/setup-local.php`** — the file has 91 `function.notFound` errors due to locally-defined functions not visible to PHPStan; out of scope

## Acceptance Criteria

- `composer seed-tests` succeeds on a schema-only (empty) database
- `composer seed-tests` is idempotent (running twice produces no errors)
- No `resetCollection()` calls exist in the CLI seed scripts (`tools/seed-truncate.php`, `tools/seed-insert.php`)
- `doSeedTests()` retains its self-contained contract (truncate + reset + seed) for programmatic callers
- `seedLocales()` can be called on an already-seeded database without throwing
- PHPStan reports no new errors in modified files (beyond pre-existing baseline)
- All framework tests pass after seeding (`composer test`)
- `testing.md` contains no references to `tests/sql/testsuite.sql`
- `$port` variable in `tools/setup-local.php` is initialized before the do-while loop

## Testing Strategy

1. **Idempotency test:** Run `composer seed-tests` twice in succession; both runs must succeed with exit code 0.
2. **Empty-database test:** Import only `docs/sql/pristine.sql` (schema only), then run `composer seed-tests`; must succeed.
3. **Regression:** Run `composer test` (full framework suite) after seeding; all tests must pass.
4. **Locales idempotency:** Call `seedLocales()` manually after seeding; must not throw duplicate-key exception.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **Double bootstrap overhead (~1–2s)** | Acceptable for a CLI developer tool; documented as intentional trade-off. |
| **`doSeedTests()` called externally by HCP Editor** | `doSeedTests()` remains self-contained (truncate + reset + seed). No contract change for programmatic callers. The process-isolated Composer scripts bypass `doSeedTests()` by calling methods directly. |
| **Locale existence check uses wrong column name** | Use `DBHelper::recordExists()` with `array('locale_name' => $name)` — the `locale_name` column is the natural key in both locale tables. Verify against `docs/sql/pristine.sql` before implementing. |
| **Composer script array order not guaranteed** | Composer documentation confirms sequential execution for arrays; add a comment in `composer.json` for visibility. |
| **`tests/bootstrap.php` changes break seed scripts** | Both seed scripts use the same bootstrap path; changes are caught by the test suite. |
