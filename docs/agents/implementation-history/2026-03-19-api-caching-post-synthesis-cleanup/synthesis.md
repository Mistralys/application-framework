# Synthesis Report: API Caching Post-Synthesis Cleanup

**Date:** 2026-03-19
**Plan:** `2026-03-19-api-caching-post-synthesis-cleanup`
**Status:** COMPLETE
**Test Suite:** 996 tests, 0 failures, exit 0

---

## Executive Summary

This session was a targeted cleanup pass following the previous API caching synthesis. Seven independent work packages were completed in a single pass with no rework cycles. The scope covered test correctness (2 WPs), test infrastructure hygiene (1 WP), documentation hardening (3 WPs), and a cosmetic repository fix (1 WP). All changes were low-risk, minimal-scope, and non-breaking. The codebase is in a clean state with an accurate test suite and up-to-date documentation.

---

## Work Accomplished

| WP | Area | Change | Result |
|----|------|--------|--------|
| WP-001 | Test correctness | `HtaccessGeneratorTest`: fixed 2 assertions that expected `/api/` for a constant whose actual value is `''` | 19/19 pass |
| WP-002 | Test correctness | `RecordTieInTest`: added `tearDown()` to unset 3 `$_REQUEST` keys, fixing inter-test pollution that caused `test_ancestryHandling` to fail | 8/8 pass |
| WP-003 | Test infrastructure | Live-HTTP tests isolated via PHPUnit 13 `#[Group('live-http')]` attributes + `phpunit.xml` exclusion block | `composer test` exits 0 without a running web server |
| WP-004 | Developer constraints | `constraints.md`: new rule requiring `phpstan-result.txt` update (`composer analyze-save`) in the same commit when the PHPStan error count changes | Documented |
| WP-005 | API cache interface | `CacheableAPIMethodInterface::readFromCache()` PHPDoc: added corrupt-cache auto-recovery and null-return case to contract, with `@see` cross-reference to `CacheableAPIMethodTrait` | Interface contract complete |
| WP-006 | Production code | `DeepLTestScreen::handleTest()`: replaced `AppFactory::createDeeplHelper()->createTranslator()` double-instantiation with `$this->helper->createTranslator()` | PHPStan baseline unchanged (6 errors, pre-existing) |
| WP-007 | Repository hygiene | `git mv Application_SettingsTest.xml SettingsTest.xml` — IDE run config filename now matches the test class name | Rename tracked (R), not delete+add |

---

## Metrics

- **Tests:** 996 passed, 0 failed, 0 errors (full suite, no regressions)
- **PHPStan:** 6 errors — matches pre-existing baseline, no new issues introduced
- **Rework cycles:** 0 across all 7 WPs
- **Pipeline health:** 7/7 WPs with all four stages (implementation, QA, code-review, documentation) passing

---

## Key Decisions

**PHPUnit 13 `#[Group]` attributes over `@group` annotations (WP-003).** The plan AC used `@group` wording but the implementation correctly used PHP 8 `#[Group('live-http')]` attributes, which is the only mechanism that works in PHPUnit 13. The `@group` annotation is silently ignored by PHPUnit 13. `testing.md` was updated to reflect attribute syntax throughout, including a PHPUnit 13 callout to prevent future agents from regressing to the legacy form.

**Per-class `tearDown()` for `$_REQUEST` cleanup rather than a base-class fix (WP-002).** The immediate fix was scoped to `RecordTieInTest` to stay within the WP boundary. A base-class fix to `ApplicationTestCase` was identified as the correct long-term solution but deferred as a separate concern (see Open Items).

**Interface docblock over implementation comment (WP-005).** The corrupt-cache recovery behavior is documented at the interface contract level (where callers look), with a `@see` cross-reference to the trait (where the implementation lives). This avoids duplicating implementation detail in the interface.

---

## Open Items / Follow-Up Work

### HIGH PRIORITY: `ApplicationTestCase::tearDown()` does not reset `$_REQUEST`

**Root cause identified in WP-002.** Any test subclass that writes to `$_REQUEST` must maintain its own unset list in `tearDown()` or risk inter-test pollution. `BaseRecordSelectionTieIn::getRecord()` caches its result, which amplifies the problem — a stale `$_REQUEST` key from a prior test causes the tie-in to behave as though a record is already selected for the remainder of the test run.

**Current mitigation:** `RecordTieInTest` explicitly unsets the 3 keys it uses. This pattern is documented in `testing.md` (Superglobal Teardown section).

**Recommended fix:** Add a `$_REQUEST` backup/restore mechanism to `ApplicationTestCase::tearDown()`, scoped to keys the base class knows about (similar to PHPUnit's `backupGlobals` but explicit and performant). This prevents the same class of failure from recurring in any new test class that writes to superglobals.

**Files:** `tests/application/assets/classes/TestDriver/TestCase/ApplicationTestCase.php` (or equivalent base class)

### LOW PRIORITY: Legacy `@group` annotations may exist in the test suite

WP-003 Reviewer flagged that other test files may still use `@group` docblock annotations that are silently no-ops in PHPUnit 13. A grep for `@group` across `tests/AppFrameworkTests/` would reveal any legacy annotations that need migrating to `#[Group()]` attributes.

### LOW PRIORITY: `DeepLTestScreen::$helper` property initialization

`$helper` is declared as non-nullable `DeeplHelper` but is only assigned in `_handleActions()`. This is safe at runtime given the call order, but PHPStan may flag it if the baseline is regenerated after a clean-up. The long-term fix is either a constructor assignment or a nullable type with a null-guard.

---

## Documentation Changes

| File | Change |
|------|--------|
| `changelog.md` | v7.2.1 entry covering all 7 WPs |
| `docs/agents/project-manifest/testing.md` | Fixed `@group` → `#[Group()]` throughout Live-HTTP section; added PHPUnit 13 callout; added Superglobal Teardown section |
| `docs/agents/project-manifest/constraints.md` | New PHPStan Baseline section with `composer analyze-save` command and rationale |
| `src/classes/Application/API/Cache/CacheableAPIMethodInterface.php` | `readFromCache()` PHPDoc updated with corrupt-cache null-return case |

---

## Codebase Health Assessment

**Good.** The test suite is accurate (996 tests, all passing), the PHPStan baseline is stable, live-HTTP tests are properly isolated from the default suite, and the key agent-facing documents (`testing.md`, `constraints.md`) are up to date. The one systemic gap — `ApplicationTestCase` not resetting `$_REQUEST` — is documented and understood; it poses no immediate risk because the affected test class has its own teardown, but it should be addressed before new test classes that write to superglobals are added.
