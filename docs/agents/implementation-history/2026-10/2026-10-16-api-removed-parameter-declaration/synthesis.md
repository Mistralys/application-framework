# Synthesis Report — API Removed-Parameter Declaration

### Outcome Summary

The framework's API module gained a declarative removed-parameter mechanism: a method can call `addRemovedParam()` in `init()` to name a parameter it no longer accepts (with an optional replacement, removal version, and note), and the framework itself rejects any request still using that name with a single standard error code (`183009`, HTTP 400) before validation runs, replacing the pattern of applications hand-writing their own guards. The declaration surfaces consistently across the full method surface — process/processReturn responses, generated OpenAPI specs (`x-removed-parameters`, with removed names excluded from `parameters`/`requestBody`), the HTML method documentation page, and a live-HTTP regression test — and shipped with a security audit (0 findings), full code review sign-off across all five work packages, and a v7.6.0 changelog entry. All acceptance criteria across all 5 work packages were met with no deferred functional scope; a handful of small hardening/tooling follow-ups were logged for a future cycle.

### Metrics

| WP | Title | Stages Passed | Tests (last reported) | Security Issues | Blocking Review Issues |
|---|---|---|---|---|---|
| WP-001 | Removed-Parameter Declaration Types & Manager Wiring | 4/4 (impl, qa, code-review, documentation) | 538 passed / 0 failed | — | 0 |
| WP-002 | Lifecycle Rejection Step & Standard Error Code | 6/6 (impl, qa, security-audit, code-review, release-engineering, documentation) | 550 passed / 0 failed | 0 | 0 |
| WP-003 | OpenAPI Generation Support | 4/4 (impl, qa, code-review, documentation) | 557 passed / 0 failed | — | 0 |
| WP-004 | HTML Method Documentation for Removed Parameters | 4/4 (impl, qa, code-review, documentation) | 557 passed / 0 failed | — | 0 (1 non-blocking fix-forward applied) |
| WP-005 | Live-HTTP Regression Test & Release-Readiness Gate | 4/4 (impl, qa, code-review, documentation) | 582 passed / 0 failed (incl. 7/7 live-http group) | — | 0 |

- Static analysis (`phpstan --level=max` / `composer analyze`) was clean on all touched files across every WP, with no new errors introduced.
- All 7 named exit-gate commands in WP-005 (analyze, targeted filters/files, `test-filter -- API`, and `test-group -- live-http` against a reachable `APP_URL`) passed with zero new failures; the only failures observed anywhere in the project (7 pre-existing `AppFrameworkTests\LDAP\ConfigTest` failures from a missing local `test-cas-config.php`) are environmental and predate this project.
- Release engineering confirmed a non-breaking minor version bump (v7.5.3 → v7.6.0) is correctly reflected in `changelog.md`, with no migration guide required.

### Blockers, Failures & Security Concerns

None. No blocking code-review issues, no security findings (Critical/High/Medium), and no test regressions were reported at any stage across all 5 work packages.

### Strategic Recommendations

- **Single source of truth for generated metadata:** `MethodConverter::buildRemovedParamsData()` derives OpenAPI output directly from `RemovedParameter::toArray()` rather than hand-building fields, so any future field added to the declaration type propagates automatically to the OpenAPI spec without a second edit — a pattern worth reusing for other declaration-to-spec mappings in the API module.
- **Format-string safety as a recurring audit point:** the security audit specifically validated that the composed rejection message is always passed as `setErrorMessage('%s', $composed)` rather than as the format string itself, closing a format-string-injection vector in `ErrorResponse::setErrorMessage()`. This is a good template constraint for any future author-supplied text reaching a `sprintf()`-based API.
- **Mock-helper drift is a real hazard class:** WP-003 confirmed empirically (and mitigated across four separate test files) that auto-generated mocks for new interface methods default collection-like getters to "non-empty," silently changing pre-existing test assertions. Any future addition to a widely-mocked interface (e.g. `APIParamManager`) should budget for an audit of all existing mock-helper call sites, not just the new test cases.

### Code Insights

**Developer**
- `src/classes/Application/API/Parameters/Removed/RemovedParameter.php`: name validation uses a per-character scan against a literal denylist rather than a regex, keeping the banned-character list and its PHPDoc in sync and trivially diffable (WP-001).
- `src/classes/Application/API/BaseMethods/BaseAPIMethod.php`: `rejectRemovedParams()` composes a single `setErrorMessage('%s', $joined)` call from all offending parameters so the lifecycle step stays a single direct-send call (mirroring `authorize()`) while still naming every offending parameter (WP-002).
- `src/classes/Application/API/OpenAPI/MethodConverter.php` / `ResponseConverter.php`: new private `buildRemovedParamsData()` / `buildBadRequestDescription()` methods mirror the file's existing composition patterns (`buildValidationRulesData`, `buildSuccessResponse`/`buildErrorResponse`) (WP-003).
- `src/themes/default/templates/api/APIMethodDetailTmpl.php`: `generateRemovedParamList()` follows the established "generate, skip when empty" pattern used by `generateParamList()`/`generateChangelog()`; the Reviewer added an explicit return-type declaration to a new private helper to match the file's `strict_types=1` convention (WP-004).
- Exit gate (WP-005): ran all eight named commands directly without provisioning a temporary git worktree baseline, reasoning that zero failures in the current run makes a baseline diff moot for this evaluation.

**Reviewer (Code Review)**
- `APIParamManager`'s bidirectional conflict detection (both `registerParam()` → removed and `addRemovedParam()` → registered) correctly guards both declaration orders under the single `ERROR_REMOVED_PARAM_IS_REGISTERED` code (WP-001).
- The four OpenAPI mock-helper updates all document the "auto-mock `isEmpty()` defaults false" hazard in their docblocks — good defensive documentation for future contributors (WP-003).

**Security Auditor**
- `rejectRemovedParams()` runs before `authorize()`, cache read, and `collectRequestData()`, consistently enforced for every API method with no opt-out; error response data is built exclusively from author-declared metadata, never from submitted request values (WP-002).

### Deferred & Follow-Up Items

- **Source:** WP-001 — **Agent:** Developer — **Description:** The historical gap at error code `183105` in `APIParameterException` has no inline comment explaining it is intentionally skipped; a one-line comment would stop a future reader from trying to "fill the gap" when adding new codes. **Priority:** low (out-of-scope documentation polish).
- **Source:** WP-002 — **Agent:** Security Auditor — **Description:** `rejectRemovedParams()` does not log the rejection the way `authorize()` logs every denial, which would aid detecting removed-parameter-name probing/scanning. **Priority:** low, non-blocking hardening suggestion (A09).
- **Source:** WP-002 — **Agent:** QA — **Description:** The composed multi-parameter rejection message (`implode(' ', $messages)`) has no explicit separator test asserting readability at 3+ simultaneous offending parameters. **Priority:** low, not a functional defect.
- **Source:** WP-003 — **Agent:** QA — **Description:** `createJsonMethodMock()` in `MethodConverterTest.php` does not accept a `removedParams` override, unlike `createMethodMock()`; the one test needing it hand-built its own mock instead. **Priority:** low, minor API asymmetry for future test authors.
- **Source:** WP-004 — **Agent:** Developer — **Description:** `UI_DataGrid::$ids` is a process-wide static registry (one grid ID per string, for the lifetime of the PHPUnit process), which blocked a second full-page render of the same template in one test and forced an accessor-only fallback for the "section absent" assertion. An optional `allowDuplicateID` parameter (or a test-only registry reset hook) would let future template tests render the same template multiple times per run. **Priority:** low, framework-wide testability constraint, explicitly out of scope for this WP.

### Next Steps

- No functional work remains on this plan — all 5 work packages are COMPLETE with all acceptance criteria met and a clean exit gate.
- Consider a small follow-up WP (or inclusion in the next maintenance cycle) to address the low-priority items above: the `183105` gap comment, optional probing-detection logging in `rejectRemovedParams()`, and the `UI_DataGrid` duplicate-ID testability constraint — none are urgent, but all were explicitly flagged by name during this cycle.
- When planning future additions to widely-mocked interfaces (e.g. further `APIParamManager` growth), budget time upfront for an audit of existing mock-helper call sites across the test suite, per the "mock-helper drift" strategic recommendation above.
