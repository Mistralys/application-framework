# Synthesis Report — Test DB Seed Data Rework (Pass 1)

**Plan:** `2026-05-05-test-db-seed-data-rework-1`  
**Date:** 2026-05-06  
**Status:** ✅ COMPLETE — all 6 work packages delivered, all pipelines PASS  

---

## Executive Summary

This plan delivered the four strategic improvements recommended by the prior synthesis (`2026-05-05-test-db-seed-data`). The centrepiece change replaced the brittle single-process Composer PHP callback with a **two-phase, process-isolated CLI seed workflow**, permanently eliminating a class of ORM cache-bleed bugs that required manual `resetCollection()` maintenance calls. Alongside that, `seedLocales()` was made idempotent (matching the `seedCountries()` contract), a PHPStan undefined-variable warning in `tools/setup-local.php` was resolved, and all stale `tests/sql/testsuite.sql` references in the developer documentation were corrected.

The test isolation gap identified in the prior synthesis was **investigated and confirmed a non-issue**: no test class calls `truncateAllTables()` directly, and all tests use per-test transactions that roll back in `tearDown()`. No code change was required for that item.

### What Was Built

| # | Work Package | Summary |
|---|---|---|
| WP-001 | Process-isolated seed scripts | Created `tools/seed-truncate.php` and `tools/seed-insert.php`; updated `composer.json` `seed-tests` to a two-command array |
| WP-002 | Fix stale `testsuite.sql` references | Audited `docs/agents/project-manifest/testing.md`; both references already corrected by WP-005 documentation pass |
| WP-003 | `seedLocales()` idempotency | Added `DBHelper::recordExists()` guards for both `locales_application` and `locales_content`; updated PHPDoc |
| WP-004 | `$port` defensive initialization | Added `$port = 'null';` before the do-while port loop in `tools/setup-local.php`; eliminated PHPStan warning |
| WP-005 | Remove `seedTests()` + doc cleanup | Removed the now-dead `ComposerScripts::seedTests()` wrapper; updated `doSeedTests()` docblock; updated narrative docs |
| WP-006 | Final documentation audit | Final audit of `testing.md`; removed residual `APP_SEED_MODE` internal detail and stale `resetCollection()` sentence |

---

## Metrics

### Pipeline Health

| WP | Stages Run | All PASS | Security Issues | Tests Passed | Tests Failed |
|---|---|---|---|---|---|
| WP-001 | implementation · qa · security-audit · code-review · documentation | ✅ | 0 | 5/5 | 0 |
| WP-002 | documentation | ✅ | — | — | — |
| WP-003 | implementation · qa · code-review · documentation | ✅ | — | 9/9 | 0 |
| WP-004 | implementation · qa · code-review · documentation | ✅ | — | 2/2 | 0 |
| WP-005 | implementation · qa · code-review · documentation | ✅ | — | 4/4 | 0 |
| WP-006 | documentation | ✅ | — | — | — |

**Total acceptance criteria:** 15 across all WPs — **all met (15/15)**  
**Security audit (WP-001):** 0 Critical, 0 High, 0 Medium, 0 Low findings on the new files  
**PHPStan:** Zero new errors introduced; WP-004 eliminated one pre-existing undefined-variable warning  

### Files Modified

| File | Changed By |
|---|---|
| `tools/seed-truncate.php` | WP-001 (created) |
| `tools/seed-insert.php` | WP-001 (created) |
| `composer.json` | WP-001 |
| `src/classes/Application/Composer/ComposerScripts.php` | WP-001 docs, WP-005, WP-005 code-review fix-forward |
| `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php` | WP-003, WP-005 code-review fix-forward |
| `tools/setup-local.php` | WP-004 |
| `README.md` | WP-001 docs |
| `.context/framework-core-system-overview.md` | WP-001 docs |
| `.context/modules/composer/architecture-core.md` | WP-001 docs, WP-005 docs |
| `docs/agents/project-manifest/testing.md` | WP-005 docs, WP-006 docs |

---

## Incidents & Notable Events

One tooling incident was logged during execution (medium priority, resolved):

> **`ledger_begin_work` stale active-WP pointer** — When the Documentation agent attempted to claim WP-002, `ledger_begin_work` rejected the claim citing a stale pointer to the already-COMPLETE WP-005. The WP-002 acceptance criteria had already been satisfied as part of the WP-005 documentation pass. Resolved by claiming and completing WP-002 via `ledger_claim_work_package` + `ledger_start_pipeline` directly.

Two low-priority artifact traceability warnings were also recorded (WP-002 and WP-004 documentation pipelines completed with no `files_modified` declared), as no files actually required modification in those pipelines.

---

## Strategic Recommendations (Gold Nuggets)

### 1. 🏗️ Dead Code Path: `ComposerScripts::doSeedTests()`

`doSeedTests()` (and its now-removed `seedTests()` wrapper) is no longer invoked by `composer seed-tests`. The method is retained as a **programmatic entry point for direct callers**, but its `resetCollection()` calls make it subtly inconsistent with the new process-isolated model. A future plan should evaluate:
- Whether any direct callers of `doSeedTests()` remain in the codebase (none found currently).
- Whether `doSeedTests()` should be formally deprecated or removed, or whether the `resetCollection()` calls inside it should be documented more explicitly as a "single-process only" contract.

### 2. 🔒 Floating `dev-master` Dependency: `shark/simple_html_dom`

The `composer.json` has `"shark/simple_html_dom": "dev-master"` pinned to a floating branch. This was flagged during the security audit (WP-001) as an **OWASP A06 (Vulnerable & Outdated Components)** risk:
- Floating `dev-master` pins bypass Composer's security advisory checks.
- PHP 8.4 deprecation notices from this package (`$http_response_header` on lines 99, 102, 113) clutter every seed run and test output.
- **Recommended action:** Pin to a specific release tag, or evaluate replacing the package entirely.

### 3. 📚 PHPDoc Cross-Reference Quality

The Reviewer identified an opportunity to improve discoverability of the seeding surface. The `seedLocales()` PHPDoc now cross-references `@see self::SEED_LOCALES` and `@see self::seedCountries()` (delivered in WP-003). This pattern — bidirectional `@see` tags between related seed methods — should be applied consistently to `seedSystemUsers()` and `seedCountries()` as well, so contributors can navigate the full seeding surface from any entry point.

### 4. 📝 Pre-Existing PHPStan Baseline (91 `function.notFound` Errors)

PHPStan currently reports 91 pre-existing errors in `tools/setup-local.php`, all of the form `function.notFound` for helpers loaded via runtime `require`. These are not introduced by this plan and are out of scope, but they represent ongoing noise in static analysis. A future task should audit whether these helpers can be declared in a PHPStan stub file or moved to an autoloadable location to clean up the baseline.

### 5. ✅ Seed Idempotency Contract Is Now Uniform

All three seed methods (`seedSystemUsers()`, `seedLocales()`, `seedCountries()`) now share the same idempotency contract — safe to call on a pre-seeded database. The two-phase process-isolated model reinforces this: `seed-truncate.php` and `seed-insert.php` can be run repeatedly without risk of duplicate-key exceptions or stale state. This makes the seed infrastructure suitable for use in CI pipelines without pre-flight checks.

---

## Next Steps for the Planner

1. **Create a task** to address the `shark/simple_html_dom` `dev-master` pin — either pin to a release or replace the package. This will clean up PHP 8.4 deprecation notices across all seed/test runs.
2. **Consider a follow-up plan** to formally remove or deprecate `ComposerScripts::doSeedTests()` if no direct callers are confirmed. This removes a confusing dead code path and its stale `resetCollection()` calls.
3. **Consider extending the PHPDoc `@see` pattern** for `seedSystemUsers()` and `seedCountries()` to match the cross-references added to `seedLocales()`.
4. **The PHPStan `function.notFound` baseline** in `tools/setup-local.php` (91 errors) warrants a dedicated cleanup task.
5. **HCP Editor application-specific seeding** (synthesis recommendation #5 from the prior plan) remains out of scope here — it should be planned as a separate task in the HCP Editor project.
