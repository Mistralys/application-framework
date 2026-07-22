# Plan

## Plan Audit Cycles
- Audits: 6 — Plan Auditor v1.6.0
- Architectural Reviews: 1 — Plan Architect Reviewer v2.1.0

## Summary

Add mandatory authorization enforcement to the API method execution pipeline. Currently, API methods that require an API key authenticate the caller (valid key?) but never authorize the request — they do not check whether the key is granted access to the specific method, nor whether the key's pseudo-user has the necessary domain rights. This plan closes both gaps by adding an `authorize()` step to `BaseAPIMethod::_process()`, extending `APIKeyMethodInterface` with a `getRequiredRight()` declaration, adding `makeForbidden()` to `ErrorResponse`, and implementing the required right in all 20 HCP Editor API methods. It also wires up `APIKeyRecord::updateLastUsed()` in the pipeline, which is currently never called.

## Architectural Context

The API method execution pipeline lives in `BaseAPIMethod::_process()` (`src/classes/Application/API/BaseMethods/BaseAPIMethod.php`). The current flow is: `validate()` → `getActiveVersion()` → `collectRequestData()` → `collectResponseData()` → `sendSuccessResponse()`. Parameter validation (including API key presence) runs in `validate()`, but no authorization check follows.

The `APIKeyMethodInterface` (`src/classes/Application/API/Clients/API/APIKeyMethodInterface.php`) is the opt-in interface for methods requiring API key authentication. Its companion `APIKeyMethodTrait` provides the `manageParamAPIKey()` implementation. 20 API method classes in the HCP Editor implement this interface.

`APIKeyRecord` (`src/classes/Application/API/Clients/Keys/APIKeyRecord.php`) provides `getPseudoUser()` which returns a full `Application_User` with real rights assignments, and `getMethods()` which returns `APIKeyMethods` with `hasMethod()` for per-method whitelist checking.

The admin UI already enforces rights via `AllowableMigrationInterface::getRequiredRight(): ?string` on every screen class. The API authorization will follow the same pattern.

## Approach / Architecture

**Two-check authorization gate in the pipeline:**

1. **Method-access check**: Verify the API key has been granted access to this specific method via `APIKeyMethods::hasMethod()`. This enforces the existing `api_key_methods` database whitelist that is currently configured but never checked.

2. **User-rights check**: Verify the key's pseudo-user has the domain right declared by the method via `getRequiredRight()`. This connects the established user rights system (22+ rights interfaces, 100+ individual rights) to the API layer.

Both checks run in a new `authorize()` method inserted into `_process()` between `validate()` and `getActiveVersion()`. The method only activates for `APIKeyMethodInterface` instances — non-key methods are unaffected.

**Interface change**: `getRequiredRight(): ?string` is added to `APIKeyMethodInterface` with a `null`-returning default in `APIKeyMethodTrait`. This forces every key-authenticated method to have a rights declaration, while allowing opt-out via `null` for methods that don't map to a specific user right.

**Error semantics**: Authorization failures return HTTP 403 (Forbidden) via a new `ErrorResponse::makeForbidden()` method, correctly distinguishing authorization (403) from authentication (401) and validation (400).

## Rationale

- **Co-locating `getRequiredRight()` with `APIKeyMethodInterface`** rather than a separate interface: No API key → no pseudo-user → no rights to check. The coupling is semantically correct and eliminates the risk of forgetting to implement a second interface on new methods.
- **Dedicated `authorize()` pipeline step** rather than embedding in param validation: Authorization is semantically distinct from validation (authn ≠ authz), and the HTTP status should be 403, not 400.
- **Single `?string` return type** rather than `array`: The grant chain handles cascading within a domain. Cross-domain access is a domain concern handled in `collectRequestData()`. If a method genuinely needs multiple unrelated rights at the gate, it should be split.
- **`null` opt-out** mirrors the established `AllowableMigrationInterface` pattern used by 100+ admin screens.

## Considered Alternatives

| Decision | Chosen Shape | Alternatives Considered | Trade-Off Summary |
|----------|--------------|-------------------------|-------------------|
| Rights declaration location | Add `getRequiredRight()` to `APIKeyMethodInterface` | Separate `APIMethodRightsInterface` (opt-in), PHP 8 attributes | Co-location eliminates forgotten-interface bugs; attributes have no precedent in codebase |
| Pipeline position | Dedicated `authorize()` step after `validate()` | Embed in param validation, event-based hook | Param validation returns 400 (wrong semantics); events make authorization optional (security risk) |
| Return type | `?string` (single right) | `array` (multiple rights) | Grant chain cascading makes single right sufficient; multiple rights is a design smell indicating the method does too much |
| Usage tracking position | `updateLastUsed()` after `authorize()` in `_process()` | Inside `authorize()`, in `collectRequestData()` | Keeps `authorize()` a pure decision gate without side effects |

## Pattern Alignment

- **`getRequiredRight(): ?string`** follows `AllowableMigrationInterface` — `src/classes/Application/Interfaces/AllowableMigrationInterface.php`. Same pattern, same return type, same `null` opt-out semantics.
- **Interface + Trait pairing** follows `APIKeyMethodInterface` / `APIKeyMethodTrait` — `src/classes/Application/API/Clients/API/`. Adding the method to the existing pair rather than creating a new pair.
- **Error response builder pattern** follows `makeBadRequest()` / `makeInternalServerError()` — `src/classes/Application/API/ErrorResponse.php` L139–L147.
- **Error constant numbering** follows the 183000 range — `src/classes/Application/API/APIMethodInterface.php` L28–L31.
- **Pipeline step pattern** follows `validate()` — a private method in `BaseAPIMethod` that either returns (success) or calls `errorResponse()->send()` (failure, exits).

## Detailed Steps

### Step 1: Add error constants to `APIMethodInterface`

Add two new error constants to `src/classes/Application/API/APIMethodInterface.php`:

```php
public const int ERROR_METHOD_NOT_GRANTED = 183005;
public const int ERROR_INSUFFICIENT_RIGHTS = 183006;
```

These follow the existing sequential numbering (183001–183004).

### Step 2: Add `makeForbidden()` to `ErrorResponse`

Add a `makeForbidden()` convenience method to `src/classes/Application/API/ErrorResponse.php`, following the pattern of `makeBadRequest()` and `makeInternalServerError()`:

```php
public function makeForbidden() : self
{
    return $this->setHTTPStatusCode(Connectors_ResponseCode::HTTP_FORBIDDEN);
}
```

### Step 3: Add `getRequiredRight()` to `APIKeyMethodInterface`

Add the method declaration to `src/classes/Application/API/Clients/API/APIKeyMethodInterface.php`:

```php
/**
 * Returns the name of the user right required to call this API method.
 *
 * When a non-null value is returned, the framework checks whether the
 * API key's pseudo-user has this right before executing the method.
 * Return `null` if this method does not require a specific user right
 * (the method-access whitelist check still applies).
 *
 * @return string|null The right name, or `null` if no right is required.
 */
public function getRequiredRight() : ?string;
```

### Step 4: Add default `getRequiredRight()` implementation to `APIKeyMethodTrait`

Add to `src/classes/Application/API/Clients/API/APIKeyMethodTrait.php`:

```php
public function getRequiredRight() : ?string
{
    return null;
}
```

This provides backward compatibility — all existing implementations will compile and will skip the user-rights check until they override this method with their specific right.

### Step 5: Add `authorize()` method to `BaseAPIMethod`

Add a private `authorize()` method to `src/classes/Application/API/BaseMethods/BaseAPIMethod.php`:

```php
/**
 * Performs authorization checks for API key-authenticated methods.
 *
 * Two checks are performed:
 * 1. Method-access: Is the API key granted access to this method?
 * 2. User-rights: Does the key's pseudo-user have the required right?
 *
 * Non-key methods skip both checks entirely.
 */
private function authorize() : void
{
    if(!$this instanceof APIKeyMethodInterface) {
        return;
    }

    $key = $this->manageParamAPIKey()->requireValue();

    // 1. Method-access check: Is the key granted access to this method?
    // NOTE: hasMethod() already handles the grant-all case internally
    // via getMethodNames(), which returns all available methods when
    // areAllGranted() is true.
    if(!$key->getMethods()->hasMethod($this->getMethodName())) {
        $this->errorResponse(APIMethodInterface::ERROR_METHOD_NOT_GRANTED)
            ->makeForbidden()
            ->setErrorMessage('The API key does not have access to this method.')
            ->send();
    }

    // 2. User-rights check: Does the key's pseudo-user have the required right?
    $requiredRight = $this->getRequiredRight();

    if($requiredRight !== null && !$key->getPseudoUser()->can($requiredRight)) {
        // Log the specific right name internally — do NOT include it in the API
        // response body, as it enumerates the internal permission model to callers
        // who have no actionable use for it. ERROR_INSUFFICIENT_RIGHTS (183006)
        // is the caller's machine-readable diagnostic signal.
        $this->log(sprintf(
            'API key [%s] denied: pseudo-user lacks required right [%s].',
            $key->getID(),
            $requiredRight
        ));
        $this->errorResponse(APIMethodInterface::ERROR_INSUFFICIENT_RIGHTS)
            ->makeForbidden()
            ->setErrorMessage('The API key does not have the required access rights to call this method.')
            ->send();
    }
}
```

### Step 6: Insert `authorize()` and `updateLastUsed()` into `_process()`

Modify the `_process()` method in `BaseAPIMethod.php` to insert the authorization step and usage tracking between `validate()` and `getActiveVersion()`:

```php
private function _process(): void
{
    $this->time = Microtime::createNow();

    $this->validate();
    $this->authorize();

    if($this instanceof APIKeyMethodInterface) {
        $this->manageParamAPIKey()->requireValue()->updateLastUsed();
    }

    $version = $this->getActiveVersion();
    // ... rest unchanged
}
```

This keeps `authorize()` a pure decision gate (no side effects) and makes usage tracking a visible, separate pipeline concern.

### Step 7: Implement `getRequiredRight()` in HCP Editor API methods

Override the default `null` return in each of the 20 API method classes. The mapping is:

**Comtype methods** (in `assets/classes/Maileditor/Comtypes/API/Methods/`):

| Class | Right |
|---|---|
| `CreateComtypeAPI` | `ComtypeRightsInterface::RIGHT_EDIT_COMTYPES` |
| `UpdateComtypeAPI` | `ComtypeRightsInterface::RIGHT_EDIT_COMTYPES` |
| `DeleteComtypeAPI` | `ComtypeRightsInterface::RIGHT_DELETE_COMTYPES` |
| `AddComtypeCountryAPI` | `ComtypeRightsInterface::RIGHT_EDIT_COMTYPES` |
| `RemoveComtypeCountryAPI` | `ComtypeRightsInterface::RIGHT_EDIT_COMTYPES` |
| `UpdateComtypeCountryAPI` | `ComtypeRightsInterface::RIGHT_EDIT_COMTYPES` |
| `CreateComtypeVariableAPI` | `ComtypeRightsInterface::RIGHT_EDIT_COMTYPES` |
| `DeleteComtypeVariableAPI` | `ComtypeRightsInterface::RIGHT_DELETE_COMTYPES` |
| `UpdateComtypeVariableAPI` | `ComtypeRightsInterface::RIGHT_EDIT_COMTYPES` |
| `UpdateComtypeOptionsAPI` | `ComtypeRightsInterface::RIGHT_EDIT_COMTYPES` |
| `UpdateComtypeSendingModesAPI` | `ComtypeRightsInterface::RIGHT_EDIT_COMTYPES` |
| `CreateValueVariationAPI` | `ComtypeRightsInterface::RIGHT_EDIT_COMTYPES` |
| `DeleteValueVariationAPI` | `ComtypeRightsInterface::RIGHT_DELETE_COMTYPES` |
| `UpdateValueVariationAPI` | `ComtypeRightsInterface::RIGHT_EDIT_COMTYPES` |
| `SetValueVariationValuesAPI` | `ComtypeRightsInterface::RIGHT_EDIT_COMTYPES` |

**Mail methods** (in `assets/classes/Maileditor/Mails/API/Methods/`):

| Class | Right |
|---|---|
| `CreateMailAPI` | `MailRightsInterface::RIGHT_CREATE_MAILS` |
| `UpdateMailingAPI` | `MailRightsInterface::RIGHT_EDIT_MAILS` |
| `DeleteMailingAPI` | `MailRightsInterface::RIGHT_DELETE_MAILS` |
| `SetMailingStateAPI` | `MailRightsInterface::RIGHT_EDIT_MAILS` |
| `CreateMailAudienceAPI` | `MailRightsInterface::RIGHT_EDIT_MAILS` |

Each implementation is a single method override:

```php
#[Override]
public function getRequiredRight() : ?string
{
    return ComtypeRightsInterface::RIGHT_EDIT_COMTYPES; // or appropriate constant
}
```

> **Note:** The `SetMailingStateAPI` declares `RIGHT_EDIT_MAILS` at the gate level. The finalization-specific right (`RIGHT_FINALIZE_MAILS`) should be checked in its `collectRequestData()` when the target state is "finalized" — this is a Tier 2 (method-logic) authorization concern, not a gate concern.

### Step 8: Create `TestAPIKeyMethodWithRight` test stub

Create `tests/application/assets/classes/TestDriver/API/TestAPIKeyMethodWithRight.php` — a minimal subclass of `TestAPIKeyMethod` that overrides `getRequiredRight()` to return a specific test right constant. This stub is required by the framework-level authorization tests (Step 9) so that `test_user_rights_denied` and `test_user_rights_granted` can call `processReturn()` on a method that exercises the user-rights branch of `authorize()`. Without this stub, both tests would call `processReturn()` on `TestAPIKeyMethod` which inherits the default `null` return from the trait and never reaches the user-rights branch.

```php
declare(strict_types=1);

class TestAPIKeyMethodWithRight extends TestAPIKeyMethod
{
    public const string METHOD_NAME = 'TestAPIKeyWithRight';
    public const string TEST_RIGHT = 'test.right.dummy';

    #[Override]
    public function getMethodName() : string
    {
        return self::METHOD_NAME;
    }

    #[Override]
    public function getRequiredRight() : ?string
    {
        return self::TEST_RIGHT;
    }
}
```

Register the stub in `tests/application/storage/api/method-index.json` by adding an entry under the key `"TestAPIKeyWithRight"` pointing to `TestAPIKeyMethodWithRight`. Run `composer dump-autoload` after creating the file.

### Step 9: Add framework-level authorization tests

Create `tests/AppFrameworkTests/API/Keys/KeyAuthorizationTest.php` extending `APIClientTestCase`. Test cases:

1. **Method-access denied**: Create a key with no methods granted, attempt to call an API method → assert 403 with `ERROR_METHOD_NOT_GRANTED`.
2. **Method-access granted (individual)**: Create a key, grant the specific method → assert authorization passes.
3. **Method-access granted (grant-all)**: Create a key with `grantAll()` → assert authorization passes for any method.
4. **User-rights denied**: Create a key with method granted but pseudo-user lacking the required right → assert 403 with `ERROR_INSUFFICIENT_RIGHTS`.
5. **User-rights granted**: Create a key with method granted and pseudo-user having the required right → assert authorization passes.
6. **Null right skips check**: A method returning `null` from `getRequiredRight()` → assert authorization passes regardless of user rights (only method-access check applies).
7. **Non-key method skips authorization**: A method without `APIKeyMethodInterface` → assert `authorize()` is a no-op.

These tests should use `processReturn()` to capture the response without exiting, similar to existing API tests.

### Step 10: Update OpenAPI specification output

Update `ResponseConverter::convertResponses()` in `src/classes/Application/API/OpenAPI/ResponseConverter.php` to include `403 Forbidden` as a documented response for all API methods that implement `APIKeyMethodInterface`. `MethodConverter::convertMethod()` delegates response generation to `ResponseConverter`, so the branch must be added there, not in `MethodConverter`.

Concrete changes:

1. Add an `HTTP_403 = '403'` entry to the constant or local mapping used in `convertResponses()`.
2. Add a conditional branch in `convertResponses()`: when the method is an instance of `APIKeyMethodInterface`, include a `'403'` entry describing both `ERROR_METHOD_NOT_GRANTED` and `ERROR_INSUFFICIENT_RIGHTS`.
3. Add a `HTTP_403 = '403'` class constant to `ResponseConverter` alongside the existing `HTTP_200`, `HTTP_400`, and `HTTP_500` constants, so the 403 key is referenced by constant everywhere in the class rather than as a magic string.
4. Update the `@return` PHPDoc annotation on `convertResponses()` to use an optional-key shape for `'403'` (the key is only present for `APIKeyMethodInterface` methods). Replace the existing annotation with: `@return array{'200': array<string,mixed>, '400': array<string,mixed>, '500': array<string,mixed>, '403'?: array<string,mixed>}`. Using `'403'?:` (optional key) rather than `'403':` (required key) prevents a PHPStan type mismatch in callers that receive the result when the key is absent.

### Step 11: Add `api-methods` test suite to HCP Editor `phpunit.xml`

Add a named `<testsuite>` entry for the new `tests/MailEditorTests/API/` directory to **both** `phpunit.xml` and `phpunit-unit.xml` in the HCP Editor project, following the same pattern used by all other `MailEditorTests/` subdirectory entries:

```xml
<testsuite name="api-methods">
    <directory suffix=".php">tests/MailEditorTests/API/</directory>
</testsuite>
```

The entry must appear in both files because `composer test-suite` reads `phpunit.xml` while `composer test-suite-unit` reads `phpunit-unit.xml`. `GetRequiredRightTest.php` has no external-service dependencies and qualifies as unit-safe, so the entry belongs in both configs. Without the `phpunit-unit.xml` entry, `composer test-suite-unit -- api-methods` will fail to find the suite even though the rationale sentence above references that command.

## Dependencies

- Steps 1–2 are independent prerequisites.
- Step 3 is independent of steps 1–2 (it only adds `getRequiredRight()` to the interface; the docblock contains no error-constant references).
- Step 4 depends on step 3 (trait implements interface method).
- Step 5 depends on steps 1, 2, 3 (uses error constants from step 1, `makeForbidden()` from step 2, and `getRequiredRight()` from step 3 in `authorize()`).
- Step 6 depends on step 5.
- Step 7 depends on steps 3, 4 (methods must compile against the updated interface).
- Step 8 depends on steps 3–4 (the new stub overrides `getRequiredRight()` from the updated interface and trait).
- Step 9 depends on steps 1–6 and step 8 (authorization test cases require both the gate implementation and the rights-aware stub to exercise the user-rights branch).
- Step 10 depends on step 1 (needs error constant names).
- Step 11 depends on step 9 (suite entry is added once the test file exists).

**Sequencing**: Steps 1–2 can be parallel → Steps 3–4 sequential → Step 5 → Step 6 → Steps 7–8 (can be parallel) → Step 9 → Step 10 → Step 11.

## Required Components

### Framework (application-framework)

- `src/classes/Application/API/APIMethodInterface.php` — add error constants
- `src/classes/Application/API/ErrorResponse.php` — add `makeForbidden()`
- `src/classes/Application/API/Clients/API/APIKeyMethodInterface.php` — add `getRequiredRight()`
- `src/classes/Application/API/Clients/API/APIKeyMethodTrait.php` — add default implementation
- `src/classes/Application/API/BaseMethods/BaseAPIMethod.php` — add `authorize()`, modify `_process()`
- `tests/AppFrameworkTests/API/Keys/KeyAuthorizationTest.php` — new test file
- `tests/application/assets/classes/TestDriver/API/TestAPIKeyMethodWithRight.php` — new test stub returning a non-null right (required by Step 9 tests 4 and 5)
- `tests/application/storage/api/method-index.json` — register `TestAPIKeyMethodWithRight` stub under key `"TestAPIKeyWithRight"`

### HCP Editor (hcp-editor)

- 15 files in `assets/classes/Maileditor/Comtypes/API/Methods/` — add `getRequiredRight()` override
- 5 files in `assets/classes/Maileditor/Mails/API/Methods/` — add `getRequiredRight()` override

## Assumptions

- `APIKeyHandler::requireValue()` returns the validated `APIKeyRecord` after `validate()` has run. Since `authorize()` runs after `validate()`, the key is guaranteed to be resolved.
- The pseudo-user's `can()` method is available at API request time (no session or admin-context dependency that would prevent it from working in API mode).
- The rights mapping in Step 7 is correct based on the research paper's analysis. Final verification against the grant chain hierarchy should be done during implementation.
- The `VariationAPITraitStub` test class in `tests/MailEditorTestClasses/Stubs/API/` will compile without changes because `APIKeyMethodTrait` provides the default `null` return.
- The framework-level `TestAPIKeyMethod` stub (`tests/application/assets/classes/TestDriver/API/TestAPIKeyMethod.php`) also uses `APIKeyMethodTrait` and will compile without changes for the same reason. PHPStan analysis must be run against both the framework and the HCP Editor to catch any type-level regressions.

## Constraints

- **Array syntax**: Always `array()`, never `[]`.
- **No constructor promotion**: Assign properties explicitly.
- **`declare(strict_types=1)`** in every new file.
- **Classmap autoloading**: Run `composer dump-autoload` after adding new test files.
- **Breaking interface change**: All `APIKeyMethodInterface` implementors must be updated in the same commit (or rely on the trait default).

## Out of Scope

- **Protecting read-only API methods**: Methods like `GetMailingsAPI` that don't implement `APIKeyMethodInterface` are not affected. Whether they should require API key authentication is a product decision.
- **`api_key_method_groups` table**: Group-based method grants are deferred — the table exists in the DB but no PHP code references it. Individual method grants and user-rights checks provide sufficient authorization for now.
- **Admin UI changes**: The API key administration screens already allow configuring method grants. No UI changes are needed.
- **Rate limiting or throttling**: Separate concern from authorization.
- **Tier 2 (method-logic) authorization**: Fine-grained, parameter-dependent rights checks within `collectRequestData()` (e.g., `SetMailingStateAPI` checking `RIGHT_FINALIZE_MAILS` for finalization) are the responsibility of each method's existing implementation, not this plan.

## Acceptance Criteria

- AC-01: `BaseAPIMethod::_process()` calls `authorize()` after `validate()` and before `getActiveVersion()`.
- AC-02: An API key without the target method in its method whitelist receives HTTP 403 with error code `183005`.
- AC-03: An API key whose pseudo-user lacks the required right receives HTTP 403 with error code `183006`.
- AC-04: An API key with `grantAll()` and sufficient user rights can call any method without authorization failure.
- AC-05: API methods returning `null` from `getRequiredRight()` skip the user-rights check (only method-access check applies).
- AC-06: API methods that do not implement `APIKeyMethodInterface` are completely unaffected by the authorization step.
- AC-07: All 20 HCP Editor `APIKeyMethodInterface` methods declare the correct domain right via `getRequiredRight()`.
- AC-08: `APIKeyRecord::updateLastUsed()` is called in the pipeline after `authorize()` for every successful key-authenticated request.
- AC-09: `ErrorResponse::makeForbidden()` sets HTTP 403 status correctly.
- AC-10: Error constants `ERROR_METHOD_NOT_GRANTED` (183005) and `ERROR_INSUFFICIENT_RIGHTS` (183006) are defined on `APIMethodInterface`.
- AC-11: All new and modified code passes PHPStan analysis without regressions.

## Testing Strategy

Testing is split between framework-level authorization behavior (in the application-framework test suite) and HCP Editor method-level rights mapping verification (in the HCP Editor test suite).

Framework-level tests use `processReturn()` to capture error responses without process exit, and `APIClientTestCase` infrastructure for API key creation and transaction wrapping. Tests verify both the method-access and user-rights checks independently, plus edge cases (null rights, non-key methods, grant-all keys).

HCP Editor tests verify that each API method returns the expected rights constant.

## Test Plan

- `tests/AppFrameworkTests/API/Keys/KeyAuthorizationTest.php::test_method_access_denied` — Asserts 403 + `ERROR_METHOD_NOT_GRANTED` when key lacks method grant — AC-02
- `tests/AppFrameworkTests/API/Keys/KeyAuthorizationTest.php::test_method_access_granted_individual` — Asserts authorization passes when method is individually granted — AC-02
- `tests/AppFrameworkTests/API/Keys/KeyAuthorizationTest.php::test_method_access_granted_all` — Asserts authorization passes when key has `grantAll()` — AC-04
- `tests/AppFrameworkTests/API/Keys/KeyAuthorizationTest.php::test_user_rights_denied` — Uses `TestAPIKeyMethodWithRight` stub (Step 8); asserts 403 + `ERROR_INSUFFICIENT_RIGHTS` when pseudo-user lacks `TestAPIKeyMethodWithRight::TEST_RIGHT` — AC-03
- `tests/AppFrameworkTests/API/Keys/KeyAuthorizationTest.php::test_user_rights_granted` — Uses `TestAPIKeyMethodWithRight` stub (Step 8); asserts authorization passes when pseudo-user has `TestAPIKeyMethodWithRight::TEST_RIGHT` — AC-03
- `tests/AppFrameworkTests/API/Keys/KeyAuthorizationTest.php::test_null_right_skips_user_rights_check` — Asserts that methods returning `null` from `getRequiredRight()` only check method-access — AC-05
- `tests/AppFrameworkTests/API/Keys/KeyAuthorizationTest.php::test_non_key_method_skips_authorization` — Asserts that methods without `APIKeyMethodInterface` pass through `authorize()` — AC-06
- `tests/AppFrameworkTests/API/Keys/KeyAuthorizationTest.php::test_update_last_used_called_after_authorize` — Asserts `updateLastUsed()` runs after successful authorization — AC-08. Assert via DB state: call `$key->getUsageCount()` before and after a successful `processReturn()` and assert the count incremented by 1. The `APIClientTestCase` transaction wrapper keeps the write isolated without permanent DB side effects.
- `tests/AppFrameworkTests/API/OpenAPI/ResponseConverterTest.php::test_key_method_includes_403_response` — Asserts that a method implementing `APIKeyMethodInterface` produces a `'403'` key in the response map returned by `convertResponses()` — covers Step 10
- `tests/AppFrameworkTests/API/OpenAPI/ResponseConverterTest.php::test_non_key_method_excludes_403_response` — Asserts that a method not implementing `APIKeyMethodInterface` does not produce a `'403'` key in the response map — covers Step 10
- `tests/MailEditorTests/API/GetRequiredRightTest.php::test_comtype_methods_declare_required_right` — Instantiates each of the 15 comtype API method classes and asserts that `getRequiredRight()` returns the exact `ComtypeRightsInterface` constant specified in the Step 7 mapping table — AC-07
- `tests/MailEditorTests/API/GetRequiredRightTest.php::test_mail_methods_declare_required_right` — Instantiates each of the 5 mail API method classes and asserts that `getRequiredRight()` returns the exact `MailRightsInterface` constant specified in the Step 7 mapping table — AC-07

## Documentation Updates

- `src/classes/Application/API/Clients/API/APIKeyMethodInterface.php` — PHPDoc for new `getRequiredRight()` method (included in Step 3)
- `src/classes/Application/API/BaseMethods/BaseAPIMethod.php` — PHPDoc for new `authorize()` method (included in Step 5)
- `.context/` (application-framework) — In the `application-framework/` root, run `composer build` (or `composer generate-docs`) after completing Steps 1–6, 8–10 to regenerate the framework's CTX documentation under its own `.context/` directory.
- `.context/` (hcp-editor) — In the `hcp-editor/` root, run `composer build` (or `composer generate-docs`) after completing Steps 7 and 11 to regenerate the HCP Editor's CTX documentation under its own `.context/` directory.
- OpenAPI spec — Update to include 403 responses (Step 9)
- `docs/agents/project-manifest/constraints.md` — Add a section describing the two-tier API authorization model: the `getRequiredRight()` contract on `APIKeyMethodInterface`, the `null` opt-out semantics, and the `authorize()` gate in `_process()`. Per the framework AGENTS.md Manifest Maintenance Rules, a "New coding convention established" event mandates a `constraints.md` update; this new authorization pattern qualifies.
- `hcp-editor/phpunit.xml` and `hcp-editor/phpunit-unit.xml` — Add `<testsuite name="api-methods">` entry for `tests/MailEditorTests/API/` to both files (Step 11).
- `hcp-editor/docs/agents/guides/guide-adding-api-methods.md` — Amend to document the `getRequiredRight()` contract: what to return, the `null` opt-out semantics, and the expectation that every new key-authenticated method declares the correct domain right.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **Breaking existing API consumers** — Keys that currently work may be rejected if they lack method grants or user rights | The method-access check respects `areAllGranted()`, so keys with "grant all" continue to work. The user-rights check defaults to `null` (skip) via the trait, so no method rejects until its `getRequiredRight()` is explicitly overridden. Deploy framework changes first, then HCP Editor changes. |
| **Incorrect rights mapping** — A method mapped to the wrong right constant could over- or under-restrict access | The mapping was derived from the research paper's domain analysis. Verify during implementation against the `register*Rights()` grant chain methods to confirm cascading behavior. |
| **`requireValue()` call after `validate()`** — If validation didn't fully resolve the API key, `requireValue()` could fail unexpectedly | `validate()` is guaranteed to have run before `authorize()`. The `APIKeyHandler` param is required (not optional), so validation failure exits before `authorize()` is reached. |
| **Performance — additional DB queries per request** — `hasMethod()` loads granted methods; `getPseudoUser()->can()` may query the DB | `hasMethod()` caches loaded methods in memory. `Application_User::can()` uses the in-memory rights cache loaded during user resolution. No additional DB round-trips for repeat checks. |
| **PHPStan regressions** — Interface change could cause type-level issues | The trait provides the default implementation. Run `composer analyze` after all changes. |
