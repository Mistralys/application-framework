# Synthesis Report — API Method Rights Enforcement

**Project:** API Method Rights Enforcement  
**Date:** 2026-07-21  
**Status:** COMPLETE  
**Work Packages:** 5 of 5 complete  
**Plan path:** `docs/agents/plans/2026-07-21-api-method-rights-enforcement/`

---

## Executive Summary

This project closed a long-standing authorization gap in the Application Framework's API execution pipeline. API methods using key-based authentication previously authenticated callers but never verified whether the key was granted access to the specific method, or whether the key's pseudo-user held the required domain right. This plan introduced a mandatory, two-tier authorization gate enforced at the framework level, with rights declarations on all 20 HCP Editor write-operation API methods.

### What Was Built

| Component | Description |
|---|---|
| **Error infrastructure (WP-001)** | Two new error constants on `APIMethodInterface` — `ERROR_METHOD_NOT_GRANTED` (183005) and `ERROR_INSUFFICIENT_RIGHTS` (183006) — plus `ErrorResponse::makeForbidden()` (HTTP 403) builder. |
| **Rights declaration contract (WP-002)** | `APIKeyMethodInterface::getRequiredRight(): ?string` declaration with default-null implementation in `APIKeyMethodTrait`. Fully backward-compatible — all 20 existing HCP Editor implementors inherited the null default without modification. |
| **Authorization gate (WP-003)** | Private `authorize()` method in `BaseAPIMethod`, wired into `_process()` after `validate()` and before `getActiveVersion()`. Two-check sequence: method-access whitelist → pseudo-user right (when non-null). Also fixed `APIKeyRecord::getPseudoUser()` caching — a latent bug where each call returned a new object because `Application::createUser()` has a dead `$knownUsers` cache. |
| **OpenAPI 403 documentation (WP-004)** | `ResponseConverter::HTTP_403` constant and conditional 403 response entry in `convertResponses()` for all `APIKeyMethodInterface` methods. Fixed a pre-existing PHPStan error in `ErrorResponse::makeForbidden()` (`@return $this` annotation mismatch). |
| **HCP Editor rights declarations (WP-005)** | `getRequiredRight()` overrides on 15 comtype and 5 mail write-operation API methods, all using `#[Override]`. New `GetRequiredRightTest` with 20 data-provider-driven test cases. Reviewer applied a Fix-Forward to add `#[Override]` to all pre-existing bare overrides in `CreateMailAPI.php` and `CreateMailAudienceAPI.php`. |

---

## Metrics

| WP | Rework | Tests Passed | Tests Failed | Security Issues | PHPStan |
|---|---|---|---|---|---|
| WP-001 | 0 | 3 | 0 | 0 | ✔ Clean |
| WP-002 | 0 | 15 | 0 | 0 | ✔ Clean (framework + HCP Editor) |
| WP-003 | 1 (QA bounce) | 479 | 0 | 0 | ✔ Clean |
| WP-004 | 0 | 22 | 0 | 0 | ✔ Clean |
| WP-005 | 0 | 20 | 0 | 0 | ✔ Clean (3907 files) |

**Total pipeline stages passed:** 23 (including rework cycle in WP-003)  
**Total security issues (Critical/High):** 0  
**PHPStan regressions introduced:** 0 (WP-004 fixed one pre-existing regression from WP-001)

### QA Rework — WP-003

The only rework cycle in the project occurred in WP-003. `APIKeyParameterTest::test_selectKeyManually` created an API key but did not grant it access to `TestAPIKeyMethod`, so the new `authorize()` gate correctly returned HTTP 403. The fix was a single `addMethod()` call. **Root cause:** pre-existing test setup assumed keys required no explicit method grants; the new mandatory gate made this assumption fail silently. This pattern is now documented and a follow-up helper is planned (see Deferred Items).

---

## Strategic Recommendations (Gold Nuggets)

### 1. Add `createTestAPIKeyForMethod()` to `APITestCase` — High Value

**Raised by:** Developer (WP-003), QA (WP-003), Reviewer (WP-003)  
**Priority:** Medium

Every test that exercises an API method through an API key now requires an explicit `getMethods()->addMethod()` call before calling `processReturn()`. Without a helper, this is easy to omit and produces a cryptic HTTP 403 failure rather than an obvious setup error. A `createTestAPIKeyForMethod(string $methodName): APIKeyRecord` factory in `APIClientTestCase` or `APITestCase` would eliminate this boilerplate and prevent the same class of regression in future tests.

**Action:** Add helper in a follow-up task; document the pattern in `docs/agents/project-manifest/testing.md` (already partially done by WP-003 documentation).

### 2. `authorize()` Log Message Should Include Context Identifiers

**Raised by:** Security Auditor (WP-003), Reviewer (WP-003)  
**Priority:** Low

The current denial log message reads: `"API authorization failed: pseudo-user lacks the required right."` It omits the API key ID, pseudo-user ID, and method name. Adding these identifiers would dramatically improve auditability of authorization failures without exposing the right name to callers (the right name is already correctly withheld from the API response). Low effort change.

### 3. `Application::createUser()` Dead Cache — Clean Up

**Raised by:** Developer (WP-003), Reviewer (WP-003)  
**Priority:** Low

`Application::createUser()` reads from `$knownUsers` but has no write path — the cache check is dead code. `APIKeyRecord::getPseudoUser()` works around this with a per-record cache (introduced in WP-003). A future task should either implement the write path in `Application::createUser()` or remove the dead cache check to avoid misleading readers of that method.

### 4. `SetMailingStateAPI` Missing `RIGHT_FINALIZE_MAILS` Tier 2 Check

**Raised by:** Security Auditor (WP-005)  
**Priority:** Medium — Security Follow-up

`SetMailingStateAPI` is gated at Tier 1 by `RIGHT_EDIT_MAILS` (via the new `authorize()` gate), but the plan documented a Tier 2 `RIGHT_FINALIZE_MAILS` check inside `handleFinalize()` that was never implemented. Any API key pseudo-user holding `RIGHT_EDIT_MAILS` can trigger the draft→finalized (publish) transition without the `PublishMails` right. This gap pre-dates WP-005 but should be addressed in a new work package.

**Remediation:** Add `hasRight(MailRightsInterface::RIGHT_FINALIZE_MAILS)` check at the start of `handleFinalize()`, returning a 403 / `ERROR_INSUFFICIENT_RIGHTS` response on failure.

### 5. `APIMethodDetailTmpl::resolveHTTPStatusCodes()` Missing HTTP 403

**Raised by:** Reviewer (WP-001), deferred through WP-004  
**Priority:** Low

`APIMethodDetailTmpl.php::resolveHTTPStatusCodes()` hardcodes HTTP 200, 400, and 500 in the documentation UI status-code list. Now that `makeForbidden()` and the 403 authorization gate are first-class framework features, this UI template should include HTTP 403 with an appropriate description (e.g. _"Forbidden — insufficient rights to call this method"_). The WP-001 Documentation agent deferred this to WP-004; WP-004's documentation scope covered only the OpenAPI `README.md` and CTX context. This item slipped through.

### 6. Architecture — Two-Tier Rights Model Is Sound and Extensible

**Confirmed by:** Reviewer (WP-005), Security Auditor (WP-005)

The Tier 1 gate (`getRequiredRight()` → `authorize()`) handles method-level access control uniformly across all API key methods. The Tier 2 pattern (per-method `hasRight()` check in `collectResponseData()`) is available for fine-grained sub-operation control (e.g. finalization). The `#[Override]` attribute on all right declarations provides compile-time safety against interface drift. This architecture scales cleanly to additional right declarations without touching `authorize()`.

---

## Files Modified

### Application Framework

| File | WPs |
|---|---|
| `src/classes/Application/API/APIMethodInterface.php` | WP-001 |
| `src/classes/Application/API/ErrorResponse.php` | WP-001, WP-004 |
| `src/classes/Application/API/README.md` | WP-001, WP-003 |
| `src/classes/Application/API/Clients/API/APIKeyMethodInterface.php` | WP-002 |
| `src/classes/Application/API/Clients/API/APIKeyMethodTrait.php` | WP-002 |
| `src/classes/Application/API/Clients/README.md` | WP-002 |
| `src/classes/Application/API/BaseMethods/BaseAPIMethod.php` | WP-003 |
| `src/classes/Application/API/Clients/Keys/APIKeyRecord.php` | WP-003 |
| `src/classes/Application/API/OpenAPI/ResponseConverter.php` | WP-004 |
| `docs/agents/project-manifest/testing.md` | WP-003, WP-005 |
| `docs/agents/project-manifest/constraints.md` | WP-003 |
| `tests/AppFrameworkTests/API/Keys/KeyAuthorizationTest.php` | WP-003 |
| `tests/AppFrameworkTests/API/Parameters/APIKeyParameterTest.php` | WP-003 |
| `tests/AppFrameworkTests/API/OpenAPI/ResponseConverterTest.php` | WP-004 |
| `tests/application/assets/classes/TestDriver/API/TestAPIKeyMethodWithRight.php` | WP-003 |
| `tests/application/storage/api/method-index.json` | WP-003 |

### HCP Editor (WP-005)

20 API method files across `assets/classes/Maileditor/Comtypes/API/Methods/` and `assets/classes/Maileditor/Mails/API/Methods/`, plus:

| File | Notes |
|---|---|
| `tests/MailEditorTests/API/GetRequiredRightTest.php` | New: 20-case data-provider test |
| `assets/classes/Maileditor/API/README.md` | New two-tier rights model section |
| `phpunit.xml` / `phpunit-unit.xml` | api-methods suite registered |

---

## Deferred & Follow-Up Items

| # | Source | Agent | Description | Type | Priority |
|---|---|---|---|---|---|
| 1 | WP-005 | Security Auditor | `SetMailingStateAPI::handleFinalize()` missing `RIGHT_FINALIZE_MAILS` Tier 2 check — any `RIGHT_EDIT_MAILS` holder can publish without `PublishMails` right | **Deferred — new WP needed** | Medium |
| 2 | WP-003 | Developer / QA / Reviewer | Add `createTestAPIKeyForMethod(string $methodName)` helper to `APITestCase` or `APIClientTestCase` to prevent future permission-setup regressions in tests | **Deferred — follow-up task** | Medium |
| 3 | WP-001 | Reviewer | `APIMethodDetailTmpl::resolveHTTPStatusCodes()` hardcodes 200/400/500 — HTTP 403 should be added now that `makeForbidden()` is a first-class API feature | **Deferred — slipped through WP-004** | Low |
| 4 | WP-003 | Security Auditor / Reviewer | `authorize()` log message omits API key ID, pseudo-user ID, and method name — improve auditability | **Deferred — follow-up task** | Low |
| 5 | WP-003 | Developer / Reviewer | `Application::createUser()` has a dead `$knownUsers` cache (read path only, no write path) — either implement write path or remove dead check | **Deferred — technical debt** | Low |
| 6 | WP-003 | Developer / Reviewer | `TestAPIKeyMethodWithRight::init()` and `TestAPIKeyMethod::init()` both redundantly call `manageParamAPIKey()->register()` (already called by `initReservedParams()`). Fix both together in one pass. | **Deferred — minor debt** | Low |
| 7 | WP-002 | Security Auditor | `getRequiredRight()` in `APIKeyMethodTrait` is non-final — an override can silently weaken a parent's non-null right back to null. Pattern is intentional but the strengthening-only contract is documented in the docblock; no enforcement mechanism exists. Consider a PHPStan rule if enforcement is desired. | **Out of scope — design limitation, noted** | Low |
| 8 | WP-005 | Developer | HCP Editor is in DEV mode (`composer.json.DEV` marker present). Must run `composer switch-prod` before committing or releasing HCP Editor changes. The pre-commit hook enforces this but warrants explicit reminder. | **Operational — pre-release gate** | Medium |

---

## Next Steps for Planner / Manager

1. **New WP: SetMailingStateAPI Tier 2 Rights Check** — Implement `RIGHT_FINALIZE_MAILS` check in `SetMailingStateAPI::handleFinalize()`. This is a security gap flagged by the Security Auditor. It does not require framework changes; it is an HCP Editor fix only.

2. **New task: `createTestAPIKeyForMethod()` helper** — Add a convenience factory to `APITestCase` that creates a test key and grants it a specific method in one call. Update `docs/agents/project-manifest/testing.md` to reference the helper.

3. **Framework release** — WP-001 through WP-004 changes live in the framework (`application-framework`). The HCP Editor's WP-005 changes depend on `getRequiredRight()` being present in the published framework package. The HCP Editor is currently in DEV mode. Release the framework first, then update the HCP Editor's PROD dependency to activate end-to-end enforcement.

4. **Quick fix: `APIMethodDetailTmpl::resolveHTTPStatusCodes()`** — Add HTTP 403 to the UI documentation list. Trivial one-liner change; no WP needed.

5. **Optional cleanup: authorize() log enrichment** — Add API key ID and method name to denial log messages in `BaseAPIMethod::authorize()` for improved auditability. Low-effort.
