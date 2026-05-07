# Synthesis Report — Test Database Seed Infrastructure (Framework)

**Plan:** `2026-05-05-test-db-seed-data`
**Date:** 2026-05-06
**Status:** COMPLETE

---

## Executive Summary

This session delivered a complete, production-ready seed infrastructure for the Application Framework's `composer seed-tests` command. Five work packages extended `TestSuiteBootstrap` and `ComposerScripts` with three new seed methods (`truncateAllTables`, `seedLocales`, `seedCountries`) and wired them into an orchestrating `doSeedTests()` flow that is **fully idempotent** and can seed a schema-only empty database from scratch.

The original `tests/sql/testsuite.sql` raw-SQL seed file was simultaneously decommissioned — its responsibilities are now entirely covered by programmatic seeding on top of `docs/sql/pristine.sql`, eliminating SQL file duplication and keeping seed data under version control as PHP code.

One significant integration bug was discovered and resolved during QA: ORM collection caches were not invalidated after `truncateAllTables()`, causing `seedSystemUsers()` to silently skip re-inserting user 1 on every run. A companion bootstrap bug (authentication chain querying user 1 before seeding on an empty DB) was also identified and fixed. Both fixes have been verified by live idempotent double-run execution and the full regression suite passes cleanly.

---

## Metrics

| Metric | Value |
|---|---|
| Work Packages | 5 / 5 COMPLETE |
| Pipeline Stages | 20 / 20 PASS |
| QA Rework Cycles | 1 (WP-004) |
| PHPStan Errors | 0 (all WPs) |
| Tests Passing (QA verification runs) | 322 across all passing QA stages |
| Tests Failing | 0 |
| Files Modified | 7 |
| Files Deleted | 1 (`tests/sql/testsuite.sql`) |

---

## Work Package Summary

### WP-001 — `truncateAllTables()`

**Purpose:** Reset the entire test database before re-seeding.

**Implementation:** `TestSuiteBootstrap::truncateAllTables()` disables FK checks, fetches all base tables via `SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'`, truncates each via `DBHelper::truncate()`, and re-enables FK checks in a `finally` block — guaranteeing re-enablement even if truncation fails mid-loop. Intentionally runs outside a transaction (MySQL `TRUNCATE` is DDL and auto-commits).

**Code Review Fix-Forward:** SQL string quoting changed from escaped single-quotes to double-quoted outer string — the established PHP idiom for SQL with embedded single quotes. Non-behavioral.

**Note confirmed correct:** Developer's concern about `DBHelper::$tablesList` cache staleness after truncation was confirmed a false positive by code review — `TRUNCATE` empties rows but does not drop tables; the table-names cache remains accurate.

---

### WP-002 — `seedCountries()`

**Purpose:** Seed 8 test countries plus the ZZ invariant country into the `application_countries` table.

**Implementation:** `SEED_COUNTRIES` constant defines 8 ISO=>label pairs (DE, CA, FR, IT, ES, GB, US, MX). `seedCountries()` calls `createInvariantCountry()` unconditionally (it is internally idempotent), then inserts each country in `SEED_COUNTRIES` guarded by an `isoExists()` check — making the method **safe to call without a preceding truncation** (idempotent re-runs skip already-present countries). All operations wrapped in a transaction with `rollbackConditional()` on failure.

**Inline documentation note:** The `gb` vs `uk` normalization rationale is documented as an inline comment, cited as a model of contextual documentation by the Reviewer.

---

### WP-003 — `seedLocales()`

**Purpose:** Seed `de_DE` and `en_UK` into both `locales_application` and `locales_content` tables.

**Implementation:** `SEED_LOCALES` constant defines the two locale strings. `seedLocales()` iterates both locales and inserts into both tables via `DBHelper::insertDynamic()`, wrapped in a transaction. Unlike `seedCountries()`, **this method is NOT idempotent** — it has no duplicate guard. A duplicate-key exception will be thrown if called without a preceding `truncateAllTables()`.

**Documentation-Forward Resolved:** Code review flagged missing non-idempotency note in the PHPDoc. The documentation stage updated the docblock in `TestSuiteBootstrap.php` and added an explicit warning to `testing.md`.

---

### WP-004 — `doSeedTests()` Orchestration *(one rework cycle)*

**Purpose:** Wire all four seed methods into a single `doSeedTests()` call sequence and verify end-to-end idempotency.

**Bugs Discovered (QA Bounce):**

| Bug | Severity | Root Cause | Fix Applied |
|---|---|---|---|
| ORM cache not invalidated after truncation | **CRITICAL** | After bootstrap authenticated user 1, `BaseCollection::$idLookup[1]` stayed populated. After `truncateAllTables()` wiped the row, `seedSystemUsers()` saw user 1 as "existing" via stale cache, took the UPDATE branch, and never re-inserted the row. | `doSeedTests()` now calls `AppFactory::createUsers()->resetCollection()` and `AppFactory::createCountries()->resetCollection()` immediately after `truncateAllTables()`. |
| Bootstrap fails on empty DB | **SECONDARY** | The authentication chain (`createEnvironment()` → session auth → `getSystemUser()`) ran before `doSeedTests()`, querying user 1 from an empty table. | `TestSuiteBootstrap::_boot()` now guards `createEnvironment()`, `configurePaths()`, AND `configureUsers()` with `!defined('APP_SEED_MODE')`. In seed mode only `configureDatabase()` and `registerTransactionCleanupHandler()` run. |

**Post-fix QA:** Both bugs fixed and verified by live double-run of `composer seed-tests`. Regression tests: DBHelper 83/83, Countries 22/22, Locale 9/9 — all pass.

**Reviewer Observations (non-blocking):**
- Future seed method additions must include a matching `resetCollection()` call in `doSeedTests()`. A maintenance note was added to `doSeedTests()` PHPDoc and to `testing.md`.
- If `APP_SEED_MODE` guarding grows to more bootstrap methods, a private `isInSeedMode(): bool` helper would improve discoverability.

---

### WP-005 — Decommission `tests/sql/testsuite.sql`

**Purpose:** Remove the now-redundant raw-SQL seed file and update all references to point to `docs/sql/pristine.sql`.

**Implementation:**
- Deleted `tests/sql/testsuite.sql`
- Updated `SETUP_SQL_SCHEMA` constant in `tools/setup-local.php`
- Updated `AGENTS.md` Local Environment Setup section

**Code Review Fix-Forward:** Updated 4 stale references to the deleted file in `README.md` (manual setup instructions, automated setup description, `composer setup` section, seed-tests section).

**Documentation:** `composer build` regenerated `.context/framework-core-system-overview.md`, resolving 3 stale operational references to the deleted file. The now-empty `tests/sql/` directory will disappear from VCS on the next commit (git does not track empty directories).

---

## Modified Files

| File | Change |
|---|---|
| `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php` | Added `truncateAllTables()`, `SEED_COUNTRIES`, `seedCountries()`, `SEED_LOCALES`, `seedLocales()`; updated `_boot()` APP_SEED_MODE guard |
| `src/classes/Application/Composer/ComposerScripts.php` | Updated `doSeedTests()` with full call sequence and ORM cache resets |
| `tools/setup-local.php` | Updated `SETUP_SQL_SCHEMA` constant to `docs/sql/pristine.sql` |
| `AGENTS.md` | Updated SQL file reference |
| `README.md` | Fixed 4 stale `tests/sql/testsuite.sql` references (Fix-Forward) |
| `docs/agents/project-manifest/testing.md` | Added subsections: Resetting the Test Database, Seeding Countries, Seeding Locales; updated Seeding the Test Database section |
| `.context/framework-core-system-overview.md` | Regenerated via `composer build` |
| ~~`tests/sql/testsuite.sql`~~ | Deleted |

---

## Strategic Recommendations

### 1. ORM Cache Invalidation Is a Systemic Risk

The critical bug in WP-004 reveals a framework-level pattern gap: any workflow that calls `truncateAllTables()` must manually invalidate all ORM collection caches that may have been populated during bootstrap. Currently this is handled by explicit `resetCollection()` calls in `doSeedTests()`, but the obligation falls on developers to remember this for each future seed method.

**Recommendation:** Consider adding a `resetAllCollections()` helper to `TestSuiteBootstrap` that calls `resetCollection()` on all framework-level ORM singletons in one shot. This reduces the per-developer maintenance burden and provides a single authoritative reset point. Document it as the required post-truncation call in `constraints.md`.

### 2. Dual-Idempotency Contract for Seed Methods

Two different idempotency contracts now coexist:
- `seedCountries()` — fully idempotent via `isoExists()` guard (safe without prior truncation)
- `seedLocales()` and `seedSystemUsers()` — NOT idempotent (require prior truncation)

This inconsistency is documented but is a latent maintenance trap. **Recommendation:** For the HCP Editor's application-specific seed plan, establish and document a single convention — either all seed methods are idempotent (preferred) or all require truncation-first. Mixing patterns in a growing seed infrastructure will lead to subtle ordering bugs.

### 3. Test Isolation Gap: Countries Truncates Locales

`seedCountries()` calls `truncateAllTables()` internally (via `doSeedTests()`). QA confirmed a pre-existing issue where running Countries and Locales test suites in the same process (combined filter) produces false Locales failures because Countries tests invoke truncation that wipes the Locales table.

**Recommendation:** Address in a future test infrastructure pass. Each test suite that requires seed data should call `truncateAllTables()` + the relevant seed methods in its own `setUpBeforeClass()`, or use a test trait that manages test-database state isolation.

### 4. Pre-existing Technical Debt: `$port` Undefined

`tools/setup-local.php` line 228 has a "possible undefined variable `$port`" flagged by static analysis. Pre-existing and out of scope for this plan, but should be cleaned up.

### 5. HCP Editor Application-Specific Seeding (Next Plan)

This plan explicitly positions itself as the framework-level foundation. The HCP Editor's own `composer seed-tests` extension for application-specific entities (tenants, users, etc.) should be planned and implemented next, building on the now-stable framework seed infrastructure.

---

## Next Steps for Planner

1. **HCP Editor seed plan** — Application-specific seeding on top of this foundation.
2. **Idempotency convention decision** — Align `seedLocales()` (and future methods) with the `seedCountries()` idempotent pattern, or document the truncate-first contract as the single official approach.
3. **`resetAllCollections()` helper** — Evaluate extracting the multi-collection reset into a named helper on `TestSuiteBootstrap`.
4. **Test isolation** — Address Countries-truncates-Locales isolation issue in the test suite.
5. **`$port` undefined** in `tools/setup-local.php` — Minor cleanup ticket.
