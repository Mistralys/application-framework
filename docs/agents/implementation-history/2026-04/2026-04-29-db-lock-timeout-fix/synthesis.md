# Project Synthesis Report

**Project:** `2026-04-29-db-lock-timeout-fix`  
**Status:** COMPLETE  
**Date:** 2026-04-29  
**Work Packages:** 4 / 4 COMPLETE  
**All Pipeline Stages Passed:** ✅

---

## Executive Summary

This project eliminated a class of InnoDB lock wait timeout errors (MySQL error 1205) that surfaced during PHPUnit test bootstrapping in the FENRIR application framework. The fix was implemented in three coordinated layers:

1. **TestSuiteBootstrap refactor** — Decoupled system-user _seeding_ (write path) from the test bootstrap _verification_ (read path). `configureUsers()` now performs non-locking `SELECT` checks only, throwing a `BootException` (code `175001`) with a clear `composer seed-tests` hint if system users are missing. The new `public static seedSystemUsers()` method provides the seeding path with proper transaction handling and rollback-on-failure.

2. **Shutdown handler for orphaned transactions** — A new `registerTransactionCleanupHandler()` method registers a PHP shutdown function that calls `DBHelper::rollbackConditional()`. This ensures any open transaction is rolled back even on fatal error, uncaught exception, or OOM — preventing lock accumulation from crashed test processes.

3. **Accurate database name in error messages** — `BaseErrorRenderer` now resolves the active database connection via `DBHelper::getSelectedDB()` at error-render time (falling back to boot-time constants on early failures), so DB errors that occur while the test database is selected correctly report the test database name.

All changes are framework-level and automatically inherited by consuming applications (e.g., HCP Editor). The solution requires no changes to consuming application code.

---

## Work Package Outcomes

| WP | Description | Pipelines | Outcome |
|---|---|---|---|
| WP-001 | Fix `BaseErrorRenderer` DB name reporting | impl → qa → code-review → docs | ✅ PASS |
| WP-002 | Refactor `TestSuiteBootstrap` (shutdown handler + verify-only + seedSystemUsers) | impl → qa → code-review → docs | ✅ PASS |
| WP-003 | Create `tests/seed-test-db.php` + `composer seed-tests` entry | impl → qa → code-review → docs | ✅ PASS |
| WP-004 | Update `docs/agents/project-manifest/testing.md` | docs | ✅ PASS |

---

## Metrics

| Metric | Value |
|---|---|
| Total work packages | 4 |
| Work packages COMPLETE | 4 |
| Pipelines run | 13 |
| Pipelines PASS | 13 |
| Pipelines FAIL | 0 |
| PHPStan level-max errors (new code) | 0 |
| Tests passed (WP-002 suite) | 1,091 |
| Tests failed (WP-002 suite) | 7 (pre-existing LDAP failures — `test-cas-config.php` missing; unrelated) |
| Rework cycles | 0 |
| Files modified | 5 |

**Files modified:**

| File | Changed By |
|---|---|
| `src/classes/DBHelper/Exception/BaseErrorRenderer.php` | WP-001 |
| `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php` | WP-002 |
| `tests/seed-test-db.php` | WP-003 (new file) |
| `composer.json` | WP-003 |
| `README.md` | WP-003 |
| `docs/agents/project-manifest/testing.md` | WP-004 |

---

## Strategic Recommendations (Gold Nuggets)

### 🔴 High Priority — Follow-up Required

*(No high-priority blockers were introduced. All high-priority items below relate to pre-existing issues surfaced during review.)*

### 🟡 Medium Priority — Follow-up Tasks Recommended

1. **`tests/bootstrap.php` — TESTS_ROOT double-define warning** *(Pre-existing, surfaced by WP-003)*  
   `tests/bootstrap.php` line 21 declares `const TESTS_ROOT = __DIR__` unconditionally. When `seed-test-db.php` runs `define('TESTS_ROOT', ...)` first and then requires `bootstrap.php`, PHP emits `E_WARNING: Constant TESTS_ROOT already defined` on every `composer seed-tests` invocation. The value is correct (both paths resolve to `tests/`), but the warning is noise in CI output and could mask real warnings.  
   **Fix:** Change `bootstrap.php` to use `defined('TESTS_ROOT') || define('TESTS_ROOT', __DIR__)` or a `!defined()` guard. Recommend a dedicated WP targeting `tests/bootstrap.php`.

2. **Pre-existing logic inversion in `BaseErrorRenderer::analyzeQuery()` line 127** *(Surfaced by WP-001 code review)*  
   `if(!$errors) { $this->line('NOTE: Placeholders have inconsistencies.'); }` — the condition is inverted; the note should print when `$errors` is _true_, not false. This bug predates this project and was confirmed by git log. Should be fixed in a dedicated cleanup pass.

### 🟢 Low Priority — Quality Improvements

3. **`BaseErrorRenderer::analyzeQuery()` PHPStan debt** *(Pre-existing)*  
   The `$values` parameter is typed as bare `array` without an iterable value type, causing a PHPStan level-max error. Suggest adding `array<string, mixed>` or a more specific annotation in a future cleanup pass.

4. **`configureUsers()` silent pass on empty system user list** *(WP-002 QA observation)*  
   If `Application::getSystemUserIDs()` returns an empty array, `configureUsers()` logs "System users verified." and passes silently — no users are actually checked. A guard asserting at least one system user ID exists would prevent this from masking a misconfigured system user list.

5. **`configurePaths()` uses `die()` instead of `BootException`** *(WP-002 Developer observation)*  
   `configurePaths()` calls `die()` on a missing tests folder rather than throwing a `BootException`. This is inconsistent with the rest of the bootstrap. Replacing it with a `BootException` throw would allow shutdown handlers (including the newly registered transaction cleanup handler) to react gracefully.

6. **`configureDatabase()` uses unchecked constant references** *(WP-002 Developer observation)*  
   `APP_DB_TESTS_NAME`, `APP_DB_TESTS_USER`, etc. are referenced without null-check guards. In a misconfigured environment (PHP 8+), this generates deprecation notices. Low risk in a controlled test bootstrap but worth defending.

---

## Architecture Decisions Confirmed

| Decision | Rationale |
|---|---|
| Verify-only `configureUsers()` | Eliminates lock acquisition (writes → `SELECT`-only non-locking reads) from every PHPUnit bootstrap |
| `seedSystemUsers()` as `public static` | Allows consuming apps to call it from setup scripts without subclassing |
| Shutdown handler scoped to `TestSuiteBootstrap` | Production `handleShutDown()` should not roll back transactions — masks bugs. Test-only scope is correct. |
| `catch(\\Throwable)` in `resolveConnectionInfo()` | Guards against both `DBHelper_Exception` (checked) and any unexpected `Error` subclass during early boot |
| `DBHelper::rollbackConditional()` in shutdown handler | Safe as a no-op when no transaction is active — confirmed by reading `DBHelper.php` line 921 |

---

## Next Steps for Planner / Manager

1. **Create a follow-up WP** to fix the `TESTS_ROOT` double-define warning in `tests/bootstrap.php` (medium-priority, 1-line fix with broad CI impact).
2. **Create a follow-up WP** for the `analyzeQuery()` logic inversion in `BaseErrorRenderer` (line 127).
3. **Verify in HCP Editor** that `composer seed-tests` runs cleanly against the HCP Editor test database after the framework changes are deployed.
4. **Consider adding a guard** in `configureUsers()` to assert that at least one system user ID is expected (prevents silent pass on misconfigured environments).
5. **Optional:** Replace `die()` in `configurePaths()` with a `BootException` throw for consistency.
