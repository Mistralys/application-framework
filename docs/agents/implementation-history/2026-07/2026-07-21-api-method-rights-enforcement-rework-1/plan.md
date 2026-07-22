# Plan

## Plan Audit Cycles
- Audits: 3 — Plan Auditor v1.6.0
- Architectural Reviews: none — Plan Architect Reviewer v2.1.0

## Prior Project Context
This plan addresses actionable items from the `2026-07-21-api-method-rights-enforcement` synthesis. The original project successfully introduced a two-tier authorization gate (`authorize()` in `BaseAPIMethod`) with rights declarations on all 20 HCP Editor API methods. Knowledge base insights KN-0006 through KN-0008 (framework) and KN-0001 (HCP Editor) document the patterns, known gaps, and follow-ups that this plan resolves.

## Summary

Close remaining gaps from the API Method Rights Enforcement project: fix a security gap where `SetMailingStateAPI::handleFinalize()` skips the Tier 2 `RIGHT_FINALIZE_MAILS` check, add a `createTestAPIKeyForMethod()` helper to eliminate boilerplate in API key tests, enrich `authorize()` denial log messages with context identifiers, add HTTP 403 to the API method documentation UI template, implement the write path in `Application::createUser()`'s dead `$knownUsers` cache, and remove redundant `register()` calls from test fixture classes.

## Architectural Context

The two-tier authorization model introduced by the original project:

- **Tier 1 (framework-enforced):** `BaseAPIMethod::authorize()` (`src/classes/Application/API/BaseMethods/BaseAPIMethod.php` L151–188) — mandatory gate in `_process()` that checks method-access whitelists and pseudo-user rights via `getRequiredRight()`.
- **Tier 2 (method-specific):** Individual API methods may add fine-grained `hasRight()` checks inside `collectResponseData()` or handler methods for sub-operations requiring a different right.

`SetMailingStateAPI` (`assets/classes/Maileditor/Mails/API/Methods/SetMailingStateAPI.php`) declares `RIGHT_EDIT_MAILS` as its Tier 1 right, but the finalization path (`handleFinalize()`) does not verify `RIGHT_FINALIZE_MAILS` — any key holder with edit rights can publish mailings.

The test infrastructure uses `APIClientTestTrait` (`tests/AppFrameworkTestClasses/API/APIClientTestTrait.php`) to provide `createTestAPIKey()`. Since WP-003, every test exercising an API key method must also call `getMethods()->addMethod()` to grant the key access.

## Approach / Architecture

Six targeted changes across framework and HCP Editor, all following established patterns:

1. **Tier 2 rights check** in `SetMailingStateAPI::handleFinalize()` — check `$user->hasRight(RIGHT_FINALIZE_MAILS)` at entry, return 403/ERROR_INSUFFICIENT_RIGHTS on denial.
2. **Test helper** `createTestAPIKeyForMethod()` in `APIClientTestTrait` — wraps `createTestAPIKey()` + `addMethod()` in a single call.
3. **HTTP 403 in documentation UI** — extend `APIMethodDetailTmpl::resolveHTTPStatusCodes()` to conditionally include 403 for `APIKeyMethodInterface` methods.
4. **Log enrichment** — add method name, API key ID, and pseudo-user ID to both denial paths in `authorize()`.
5. **Dead cache fix** — add `self::$knownUsers[$userID] = $user;` write path in `Application::createUser()`.
6. **Test fixture cleanup** — remove the redundant `manageParamAPIKey()->register()` calls from `TestAPIKeyMethod::init()` and `TestAPIKeyMethodWithRight::init()`.

## Rationale

- **Tier 2 check** — This is a known security gap (KN-0001). The Tier 2 pattern is already documented and architecturally sanctioned (KN-0008). The fix follows the exact pattern described in the knowledge base.
- **Test helper** — Recommended by Developer, QA, and Reviewer from WP-003 (KN-0006). Eliminates the single most common regression cause in API key tests.
- **HTTP 403** — The UI template was overlooked when `makeForbidden()` was introduced. Making it conditional on `APIKeyMethodInterface` avoids noise on non-key methods.
- **Log enrichment** — Both denial paths should produce actionable log entries. The current generic message makes production debugging difficult.
- **Dead cache** — The fix is a single line that makes an existing code path work as intended. The write was clearly meant to exist (the read path proves the intent).
- **Test fixture cleanup** — Pure dead-code removal. `initReservedParams()` in the constructor already registers the parameter before `init()` runs.

## Considered Alternatives

| Decision | Chosen Shape | Alternatives Considered | Trade-Off Summary |
|----------|--------------|-------------------------|-------------------|
| Tier 2 check location | Inside `handleFinalize()` at entry | In `setMailingState()` before the if-branch; as a separate middleware | `handleFinalize()` owns the finalization concern; checking there keeps the guard co-located with the operation it protects |
| Test helper location | `APIClientTestTrait` | New dedicated trait; standalone function | Trait is already the home of `createTestAPIKey()` — extending it keeps the factory methods co-located |
| Dead cache fix | Add write path | Remove dead read and property entirely | Adding the write path fulfills the original design intent and benefits all callers, not just `APIKeyRecord::getPseudoUser()` |
| HTTP 403 conditional | `instanceof APIKeyMethodInterface` check | Always show 403 for all methods | Only key-authenticated methods can return 403; showing it unconditionally would be misleading |

## Pattern Alignment

- **Tier 2 `hasRight()` check** follows the documented Tier 2 pattern — `src/classes/Application/API/BaseMethods/BaseAPIMethod.php` (KN-0008 insight).
- **Test factory method** follows `createTestAPIKey()` naming and location — `tests/AppFrameworkTestClasses/API/APIClientTestTrait.php`.
- **`resolveHTTPStatusCodes()` extension** follows the existing `sb()->code()->italic()` pattern — `src/themes/default/templates/api/APIMethodDetailTmpl.php` L320.
- **`log()` usage** follows `Application_Traits_Loggable` sprintf-style — `src/classes/Application/Traits/Loggable.php` L77.
- **Static cache write** follows the pattern of `FileInfo` and `FolderInfo` caches documented in application-utils-core.

## Detailed Steps

### Step 1: Add Tier 2 `RIGHT_FINALIZE_MAILS` check to `SetMailingStateAPI::handleFinalize()`

**File:** `assets/classes/Maileditor/Mails/API/Methods/SetMailingStateAPI.php` (HCP Editor)

At the start of `handleFinalize()` (after the method signature, before the validation call at L322), add a rights check:

```php
private function handleFinalize(
    ArrayDataCollection $response,
    Maileditor_Mails_Mail $mail,
    \Application_User $user,
    bool $triggerGeneration
): void
{
    // Tier 2: finalization requires the PublishMails right beyond the Tier 1 edit gate.
    if (!$user->hasRight(MailRightsInterface::RIGHT_FINALIZE_MAILS)) {
        $this->errorResponse(APIMethodInterface::ERROR_INSUFFICIENT_RIGHTS)
            ->makeForbidden()
            ->setErrorMessage('Insufficient privileges to finalize this mailing.')
            ->send(); // never returns
    }

    // ... existing validation and state change code
}
```

The `$user` parameter is already the pseudo-user resolved from the API key. The error message follows the convention of not including the right name.

### Step 1a: Update `callSetMailingState()` prerequisite setup in `SetMailingStateAPITest`

**File:** `tests/MailEditorTests/Mails/API/SetMailingStateAPITest.php` (HCP Editor)

Before the Tier 2 denial test can work correctly, the existing `callSetMailingState()` helper must be updated. Without these changes, `authorize()` short-circuits at Tier 1 with `ERROR_METHOD_NOT_GRANTED` (HTTP 403 / error 183005) before ever reaching the Tier 2 check:

1. Add `$key->getMethods()->addMethod(SetMailingStateAPI::METHOD_NAME)` to the test API key setup in `callSetMailingState()` to satisfy the Tier 1 method-access gate.
2. Grant `RIGHT_EDIT_MAILS` to the pseudo-user for all existing finalization test paths (Tier 1 right requirement).
3. Grant `RIGHT_FINALIZE_MAILS` to the pseudo-user for all existing positive-path finalization tests (Tier 2 right requirement).

Apply all three changes before writing the new Tier 2 denial test in Step 2, to ensure the Tier 2 check is the actual gate being exercised rather than the Tier 1 method-access check.

### Step 2: Add test for Tier 2 finalization denial

**File:** `tests/MailEditorTests/Mails/API/SetMailingStateAPITest.php` (HCP Editor)

Add a test that creates an API key pseudo-user with `RIGHT_EDIT_MAILS` but without `RIGHT_FINALIZE_MAILS`, then asserts that a finalization request returns HTTP 403 / `ERROR_INSUFFICIENT_RIGHTS`. The test should:

1. Create a test API key using `createTestAPIKey()`.
2. Grant the key access to `SetMailingStateAPI::METHOD_NAME` via `addMethod()`.
3. Set the pseudo-user's rights to include `RIGHT_EDIT_MAILS` but exclude `RIGHT_FINALIZE_MAILS`.
4. Create a mail in draft state.
5. Call `processReturn()` with a finalization request.
6. Assert HTTP 403 and error code 183006.

Also add a positive test: pseudo-user with both `RIGHT_EDIT_MAILS` and `RIGHT_FINALIZE_MAILS` can finalize successfully.

### Step 3: Add `createTestAPIKeyForMethod()` to `APIClientTestTrait`

**File:** `tests/AppFrameworkTestClasses/API/APIClientTestTrait.php` (Framework)

Add a new factory method below `createTestAPIKey()`:

```php
/**
 * Creates a test API key and grants it access to the specified method.
 * Convenience wrapper around {@see createTestAPIKey()} that eliminates
 * the manual {@see APIKeyMethods::addMethod()} boilerplate.
 *
 * @param string $methodName The API method name to grant (e.g. TestAPIKeyMethod::METHOD_NAME).
 * @return APIKeyRecord
 */
public function createTestAPIKeyForMethod(string $methodName) : APIKeyRecord
{
    $key = $this->createTestAPIKey();
    $key->getMethods()->addMethod($methodName);
    return $key;
}
```

### Step 4: Update existing framework tests to use the new helper

Refactor `KeyAuthorizationTest` and `APIKeyParameterTest` (and any other framework test files that use the `createTestAPIKey()` + `addMethod()` pattern) to use `createTestAPIKeyForMethod()` instead. This validates the helper works and demonstrates the recommended pattern.

**Important:** `test_methodAccessDenied()` in `KeyAuthorizationTest` must **not** be refactored. This test deliberately creates an API key *without* calling `addMethod()` to verify the Tier 1 `ERROR_METHOD_NOT_GRANTED` denial path — replacing it with `createTestAPIKeyForMethod()` would grant the method and silently invert the test's semantics. Restrict the refactoring to tests that call `addMethod()` to grant access (positive-path and right-enforcement test cases).

### Step 5: Enrich `authorize()` log messages

**File:** `src/classes/Application/API/BaseMethods/BaseAPIMethod.php` (Framework)

Update the `authorize()` method (L151–188) to add context to both denial paths:

**Method-not-granted path** (L164–L168) — add a log call before the error response:

```php
if (!$key->getMethods()->hasMethod($this->getMethodName())) {
    $this->log(
        'API authorization denied: key [%s] is not granted access to method [%s].',
        $key->getID(),
        $this->getMethodName()
    );
    $this->errorResponse(APIMethodInterface::ERROR_METHOD_NOT_GRANTED)
        ->makeForbidden()
        ->setErrorMessage('API key is not authorized to access this method.')
        ->send();
}
```

**Insufficient-rights path** (L172–L178) — enrich the existing log call:

```php
if ($requiredRight !== null && !$key->getPseudoUser()->hasRight($requiredRight)) {
    $this->log(
        'API authorization denied: key [%s] pseudo-user [%s] lacks required right [%s] for method [%s].',
        $key->getID(),
        $key->getPseudoUser()->getID(),
        $requiredRight,
        $this->getMethodName()
    );
    $this->errorResponse(APIMethodInterface::ERROR_INSUFFICIENT_RIGHTS)
        ->makeForbidden()
        ->setErrorMessage('Insufficient privileges to access this method.')
        ->send();
}
```

### Step 6: Add HTTP 403 to `APIMethodDetailTmpl::resolveHTTPStatusCodes()`

**File:** `src/themes/default/templates/api/APIMethodDetailTmpl.php` (Framework)

Extend the `resolveHTTPStatusCodes()` method to conditionally include HTTP 403 for `APIKeyMethodInterface` methods. The method needs access to the API method instance — check whether the template already has a reference to it, and use `instanceof APIKeyMethodInterface` to gate the addition:

```php
private function resolveHTTPStatusCodes() : array
{
    $codes = array(
        Connectors_ResponseCode::HTTP_OK => 'OK (successful request)',
        Connectors_ResponseCode::HTTP_BAD_REQUEST => 'Bad request (missing or invalid parameters)',
        Connectors_ResponseCode::HTTP_INTERNAL_SERVER_ERROR => 'Internal server error',
    );

    if ($this->method instanceof APIKeyMethodInterface) {
        $codes[Connectors_ResponseCode::HTTP_FORBIDDEN] = 'Forbidden (insufficient rights to call this method)';
    }

    $result = array();
    foreach($codes as $code => $desc) {
        $result[] = sb()->code($code)->italic($desc);
    }

    return $result;
}
```

The exact property name holding the method instance must be verified from the template class — it likely already stores the `APIMethodInterface` instance for rendering.

**Import note:** `APIKeyMethodInterface` is not currently imported in `APIMethodDetailTmpl.php`. Add `use Application\API\Clients\API\APIKeyMethodInterface;` to the file's import block alongside the existing `use Application\API\APIMethodInterface;` line. Without this, PHP will raise a fatal class-not-found error the first time the template is rendered for a key-authenticated method.

### Step 7: Implement `$knownUsers` write path in `Application::createUser()`

**File:** `src/classes/Application/Application.php` (Framework)

Add a single line after the user object is created and validated — store it in the cache before returning:

```php
public static function createUser(int $userID): Application_User
{
    if (isset(self::$knownUsers[$userID])) {
        return self::$knownUsers[$userID];
    }

    $userClass = self::getUserClass();

    $user = new $userClass($userID, self::getUserData($userID));

    if ($user instanceof Application_User) {
        self::$knownUsers[$userID] = $user;
        return $user;
    }

    throw new ApplicationException(
        'Invalid user class',
        // ...
    );
}
```

Also consider adding a static `clearUserCache()` method for test isolation, following the `FileInfo::clearCache()` pattern. Tests that manipulate user rights in-memory may need to clear the cache between test cases.

**Step 7.2: Write `tests/AppFrameworkTests/Application/CreateUserCacheTest.php` (new file)**

Create a test class with a single assertion: `assertSame(Application::createUser($id), Application::createUser($id))` — calling `createUser()` twice with the same ID must return the same object instance (proving the cache write path works). PHPUnit discovers test files in `tests/AppFrameworkTests/` by directory scanning; no `composer dump-autoload` is required after creating this file.

### Step 8: Remove redundant `register()` calls from test fixtures

**Files:**
- `tests/application/assets/classes/TestDriver/API/TestAPIKeyMethod.php` (Framework)
- `tests/application/assets/classes/TestDriver/API/TestAPIKeyMethodWithRight.php` (Framework)

Remove the `init()` override entirely from both classes (or remove just the `manageParamAPIKey()->register()` call if other code exists in `init()`). The registration is already performed by `initReservedParams()` in the `BaseAPIMethod` constructor before `init()` is called.

For `TestAPIKeyMethod.php` (L63–L66):
```php
// REMOVE:
protected function init(): void
{
    $this->manageParamAPIKey()->register();
}
```

For `TestAPIKeyMethodWithRight.php` (L80–L83):
```php
// REMOVE:
protected function init(): void
{
    $this->manageParamAPIKey()->register();
}
```

### Step 9: Run tests and static analysis

1. **Framework tests:** Run `composer test-filter -- BaseAPIMethod` and `composer test-filter -- APIKey` to validate Steps 3–5 and 8. Run `composer test-file -- tests/AppFrameworkTests/Application/CreateUserCacheTest.php` to validate Step 7 (AC-08).
2. **Framework PHPStan:** Run `composer analyze` to ensure no regressions.
3. **HCP Editor tests:** Run `composer test-file-unit -- tests/MailEditorTests/Mails/API/SetMailingStateAPITest.php` to validate Steps 1–2.
4. **HCP Editor PHPStan:** Run `composer analyze` to ensure no regressions.

## Dependencies
- Steps 1, 1a, and 2 (HCP Editor) are independent of Steps 3–8 (Framework).
- Step 1a must be completed before Step 2 (prerequisite test setup must be in place before writing the Tier 2 denial assertion).
- Step 4 depends on Step 3 (helper must exist before refactoring tests to use it).
- Steps 7 and 7.2 are independent of all other steps.
- Step 8 is independent of all other steps.
- Step 9 depends on all preceding steps.

## Required Components

### Application Framework
- `src/classes/Application/API/BaseMethods/BaseAPIMethod.php` — authorize() log enrichment
- `src/classes/Application/Application.php` — createUser() cache fix
- `src/themes/default/templates/api/APIMethodDetailTmpl.php` — HTTP 403 addition
- `tests/AppFrameworkTestClasses/API/APIClientTestTrait.php` — new helper method
- `tests/AppFrameworkTests/API/Keys/KeyAuthorizationTest.php` — refactor to use helper
- `tests/AppFrameworkTests/API/Parameters/APIKeyParameterTest.php` — refactor to use helper
- `tests/application/assets/classes/TestDriver/API/TestAPIKeyMethod.php` — cleanup
- `tests/application/assets/classes/TestDriver/API/TestAPIKeyMethodWithRight.php` — cleanup

### HCP Editor
- `assets/classes/Maileditor/Mails/API/Methods/SetMailingStateAPI.php` — Tier 2 check
- `tests/MailEditorTests/Mails/API/SetMailingStateAPITest.php` — new Tier 2 test cases

## Assumptions
- `APIMethodDetailTmpl` has access to the method instance (likely via a class property) to perform the `instanceof` check.
- The `log()` method's variadic args are passed through to `sprintf()` formatting.
- Removing `init()` from test fixtures does not affect test behavior since the `register()` call is idempotent and already performed in the constructor.

## Constraints
- Error messages in API responses must NOT include right names (security convention from WP-003).
- Internal log messages CAN include right names (not exposed to callers).
- The HCP Editor is currently in DEV mode — `composer switch-prod` must be run before committing HCP Editor changes (pre-commit hook enforces this).

## Out of Scope
- `getRequiredRight()` non-final enforcement (PHPStan rule) — design limitation, intentional flexibility for now.
- DEV/PROD mode switching — operational concern, not a code change.
- Adding `createTestAPIKeyForMethod()` usage in HCP Editor tests — can be done in a future pass; the existing `GetRequiredRightTest` and `SetMailingStateAPITest` tests can be refactored later.

## Acceptance Criteria

- AC-01: `SetMailingStateAPI::handleFinalize()` rejects finalization when the pseudo-user lacks `RIGHT_FINALIZE_MAILS`, returning HTTP 403 / `ERROR_INSUFFICIENT_RIGHTS` (183006).
- AC-02: A pseudo-user with both `RIGHT_EDIT_MAILS` and `RIGHT_FINALIZE_MAILS` can finalize mailings successfully.
- AC-03: `createTestAPIKeyForMethod(string $methodName)` exists on `APIClientTestTrait` and returns an `APIKeyRecord` with the specified method granted.
- AC-04: At least one existing framework test uses `createTestAPIKeyForMethod()` instead of the manual `createTestAPIKey()` + `addMethod()` pattern.
- AC-05: Both denial paths in `authorize()` produce log messages that include the API key ID and method name.
- AC-06: The insufficient-rights denial log additionally includes the pseudo-user ID and required right name.
- AC-07: `APIMethodDetailTmpl::resolveHTTPStatusCodes()` includes HTTP 403 for methods implementing `APIKeyMethodInterface`.
- AC-08: `Application::createUser()` caches created user instances in `$knownUsers`, preventing duplicate object creation for the same user ID within a request lifecycle.
- AC-09: `TestAPIKeyMethod::init()` and `TestAPIKeyMethodWithRight::init()` no longer contain redundant `manageParamAPIKey()->register()` calls.
- AC-10: PHPStan passes with zero regressions in both framework and HCP Editor.

## Testing Strategy

Testing follows a targeted approach:

- **Tier 2 rights** — dedicated test cases in `SetMailingStateAPITest` covering denial and success paths.
- **Test helper** — existing framework tests refactored to use it prove correctness. No separate unit test needed for a one-line wrapper.
- **Log enrichment** — verified by inspection (log output is not easily asserted in unit tests without mocking the logger). The `KeyAuthorizationTest` already covers that `authorize()` denies correctly; the enriched log messages are additive.
- **HTTP 403 template** — manual verification in the API documentation UI. If a template rendering test exists, extend it.
- **Dead cache** — existing `createUser()` callers benefit automatically. Add a targeted assertion that calling `createUser()` twice with the same ID returns the same instance.
- **Test fixture cleanup** — existing tests that use `TestAPIKeyMethod` and `TestAPIKeyMethodWithRight` verify no behavioral regression.

## Test Plan

- `tests/MailEditorTests/Mails/API/SetMailingStateAPITest.php::test_handleFinalize_deniedWithoutFinalizeRight` — new test: pseudo-user with `RIGHT_EDIT_MAILS` only → HTTP 403 / 183006 on finalization — covers AC-01.
- `tests/MailEditorTests/Mails/API/SetMailingStateAPITest.php::test_handleFinalize_allowedWithFinalizeRight` — new test: pseudo-user with both rights → successful finalization — covers AC-02.
- `tests/AppFrameworkTests/API/Keys/KeyAuthorizationTest.php` — refactor existing tests to use `createTestAPIKeyForMethod()` — covers AC-03, AC-04.
- `tests/AppFrameworkTests/API/Parameters/APIKeyParameterTest.php` — refactor to use `createTestAPIKeyForMethod()` — covers AC-03.
- `tests/AppFrameworkTests/Application/CreateUserCacheTest.php` — new test: assert `createUser($id) === createUser($id)` (same instance) — covers AC-08.
- Existing `KeyAuthorizationTest` tests — run to verify AC-05, AC-06 (behavioral correctness; log content is additive).
- Existing `TestAPIKeyMethod` / `TestAPIKeyMethodWithRight` tests — run to verify AC-09 (no regression from removing redundant calls).
- `composer analyze` on both framework and HCP Editor — covers AC-10.

## Documentation Updates

- `docs/agents/project-manifest/testing.md` (Framework) — update the API Key Method Tests section to reference `createTestAPIKeyForMethod()` as the recommended pattern, replacing the current manual `addMethod()` example.
- `docs/agents/project-manifest/constraints.md` (Framework) — update the Known Gotchas section to note that the `Application::createUser()` dead cache has been fixed (remove or update the gotcha).
- `src/classes/Application/API/README.md` (Framework) — update the authorization section to document Tier 2 rights pattern with the `SetMailingStateAPI` example, and note the enriched log messages.
- `assets/classes/Maileditor/API/README.md` (HCP Editor) — update the two-tier rights model section to document the `SetMailingStateAPI` Tier 2 check as a concrete example.
- KN-0001 (HCP Editor insight) — mark as resolved after implementation.
- KN-0006 (Framework insight) — update to reference `createTestAPIKeyForMethod()` as implemented.
- KN-0007 (Framework insight) — update to note the `$knownUsers` write path fix.

## Deferred Items

| # | Deferred Item | Origin | Reason Deferred | Notes |
|---|---|---|---|---|
| 1 | `getRequiredRight()` non-final weakness — an override can silently weaken a parent's non-null right back to null | Synthesis deferred #7 (Security Auditor, WP-002) | Design limitation; the strengthening-only contract is documented but not enforced. A PHPStan rule would be needed and is a separate tooling effort. | Reconsider if a weakening override is discovered in production use. |
| 2 | DEV/PROD mode switch reminder before HCP Editor commits | Synthesis deferred #8 (Developer, WP-005) | Operational concern — the pre-commit Git hook already enforces this gate. Not a code change. | The hook provides sufficient protection. |

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **Tier 2 check breaks existing finalization tests** | Two prerequisite fixes are required before the new Tier 2 denial test (Step 2) can demonstrate the intended behaviour. First, the `callSetMailingState()` helper must be updated to call `getMethods()->addMethod(SetMailingStateAPI::METHOD_NAME)` on the test API key — without this grant, `authorize()` short-circuits at Tier 1 with `ERROR_METHOD_NOT_GRANTED` (HTTP 403 / error 183005) and never reaches the Tier 2 check. Second, any finalization test path must also grant `RIGHT_FINALIZE_MAILS` to the pseudo-user. Apply these two prerequisite fixes before writing the Tier 2 denial assertion. |
| **`$knownUsers` cache causes test isolation issues** | Add a `clearUserCache()` method and call it in test teardown if any test modifies user rights in-memory. The `APIKeyRecord::getPseudoUser()` per-record cache remains as a second layer. |
| **`APIMethodDetailTmpl` may not have method instance access** | Verify the template class stores a reference to the method. If not, pass the `instanceof` result as a boolean parameter from the calling code. |

## Recommended Workflow
- **Workflow:** standalone
- **Rationale:** All six changes are small, well-scoped modifications within established patterns — no new architecture, no cross-cutting concerns beyond what the original project already introduced. A single developer session with self-review is adequate.
