# Plan

## Plan Audit Cycles
- Audits: 2 (Sonnet 5.5 ×2) — Plan Auditor v1.12.0
- Architectural Reviews: 1 (Sonnet 5.5 ×1) — Plan Architect Reviewer v2.3.4

## Prior Project Context
- **Origin:** HCP Editor plan `2026-10-16-layout-area-role-param-rename` renamed `mailLayoutAreaID` to `mailLayoutRoleID` and had to hand-write a guard (`GlobalContentsListAPITrait::rejectRemovedAreaRoleParam()`), because the framework silently ignores undeclared request parameters. Its backlog entry "Framework Removed-Parameter Declaration" (`hcp-editor/docs/agents/project-manifest/deferred-topics.md`) asks for this capability in a framework plan.
- **Lessons from the guard:** it had to send the error directly. A thrown exception inside `collectRequestData()` collapses into the generic `ERROR_REQUEST_DATA_EXCEPTION` (183001, HTTP 500).
- **2026-08 API hardening series** (error boundary, redaction, key authorization): it established that `BaseAPIMethod` has exactly one execution boundary, that framework rejections send stable `183xxx` codes from private lifecycle steps, and that request-derived content never goes through `ErrorResponse::addData()`. This plan follows all three.
- **Strategic vision:** the mid-term goals are better test coverage and stricter typing. The feature ships with typed value objects, unit tests, and a live-HTTP regression test.

## Summary
Add a framework-level removed-parameter declaration to the API module. In `init()`, an API method declares request parameter names it has removed. Each declaration can carry an optional replacement name, the version it was removed in (`removedSince`), and a free-text note. Before parameter validation, the framework rejects any request that uses a declared removed name. It sends a new standard error, `APIMethodInterface::ERROR_REMOVED_PARAMETER_USED` (183009), with HTTP 400 and a message that names the replacement. This works the same way in `process()` and `processReturn()` modes. Removed names appear in the generated OpenAPI spec through an operation-level `x-removed-parameters` extension and a code-bearing 400 response description, and in the HTML method documentation. After the framework is released, applications such as the HCP Editor can replace their hand-written guards with one declaration line.

## Architectural Context
- **Lifecycle:** `src/classes/Application/API/BaseMethods/BaseAPIMethod.php::_process()` runs `validate()` → `authorize()` → `updateLastUsed()` → `getActiveVersion()` → cache read → `collectRequestData()` → `collectResponseData()` → cache write → success. `process()` and `processReturn()` share the private `executeWithThrowableReporting()` boundary. `APIResponseDataException` carries return-mode payloads unchanged. Any other throwable becomes 183008 (HTTP 500), and throwables raised inside the two collectors become 183001/183002.
- **Direct-send rejections:** the private `authorize()` sends 401/403 with `errorResponse(code)->make…()->setErrorMessage()->send()`. `sendErrorResponse()` throws `APIResponseDataException` in return mode and writes the HTTP status line otherwise. A step built this way gives a stable code in both modes.
- **Parameters:** `src/classes/Application/API/Parameters/APIParamManager.php` (one per method, reached through `APIMethodInterface::manageParams()`) registers parameters and rules and memoizes validation. Declaration errors throw `APIParameterException` (`1831xx`). Registered parameter values resolve from `$_REQUEST` through `Application_Request`. Header parameters resolve from headers. A JSON body is parsed only later, in `JSONRequestTrait::collectRequestData()`.
- **Versioning:** `APIVersionParameter` is a reserved parameter validated by enum against `getVersions()`, so a dropped version fails with 183003. Parameters are registered once in `init()`, independent of the active version.
- **OpenAPI:** `src/classes/Application/API/OpenAPI/MethodConverter.php::buildOperation()` adds the optional extensions `x-changelog`, `x-related-methods`, and `x-validation-rules`, each omitted when empty. `ResponseConverter::convertResponses()` builds the 200/400/500 descriptions, plus a code-bearing 403 description for API-key methods.
- **HTML docs:** `src/themes/default/templates/api/APIMethodDetailTmpl.php::generateDocs()` renders the parameter, rules, and changelog sections.

## Approach / Architecture

### 1. Declaration model (new `Parameters/Removed/` sub-folder)
- **New** `src/classes/Application/API/Parameters/Removed/RemovedParameter.php`: an immutable-name value object with a fluent setter API.
  - `__construct(string $name)`
  - `setReplacement(string $name) : self`, `getReplacement() : ?string`
  - `setRemovedSince(string $version) : self`, `getRemovedSince() : ?string`
  - `setNote(string|StringableInterface $note) : self`, `getNote() : ?string`
  - `getName() : string`
  - `getErrorMessage() : string`, which composes: ``The request parameter `{name}` has been removed[ in v{since}].[ Use `{replacement}` instead.][ {note}]``
  - `toArray() : array{name:string, replacement:?string, removedSince:?string, note:?string}`, with `KEY_*` constants for each key.
  - The setters validate eagerly. They reject an empty replacement, version, or note, and a replacement identical to the removed name.
  - The constructor validates the name eagerly. It rejects an empty name, and a name PHP cannot deliver verbatim as a `$_REQUEST` key: one containing `.`, a space, `[`, or a NUL byte. PHP rewrites `.` and space (and an unmatched `[`) to `_`, turns `name[…]` into an array key, and truncates at NUL, so such a declaration could never match in `detectUsed()` and would silently never fire. The forbidden characters live in a `RemovedParameter::INVALID_NAME_CHARACTERS` constant (`array('.', ' ', '[', "\0")`), so the rule is named and testable in one place. The same rule applies to `setReplacement()`, because a mangled replacement name would point clients at a name no active parameter can receive.
- **New** `src/classes/Application/API/Parameters/Removed/RemovedParamCollection.php`: owns storage and logic for one method.
  - `add(string $name) : RemovedParameter`
  - `has(string $name) : bool`
  - `getAll() : RemovedParameter[]`, sorted by name
  - `isEmpty() : bool`
  - `detectUsed(array $requestParams) : RemovedParameter[]`, a pure function. It reports a name as used when the name exists as a key in the passed request array (`array_key_exists`), so an empty value such as `?oldName=` also counts.
  - `toArray() : array<int,array>`
- **Modified** `APIParamManager`:
  - `addRemovedParam(string $name) : RemovedParameter` and `getRemovedParams() : RemovedParamCollection`.
  - Conflict checks run in both directions, so the declaration order inside `init()` does not matter:
    - `addRemovedParam()` rejects a name that is reserved (`RESERVED_PARAM_NAMES`), already registered as an active parameter, or already declared removed.
    - `registerParam()` additionally rejects a name that is already declared removed.
  - Both calls go through `requireNotValidated()`.
- **Modified** `BaseAPIMethod`: adds a `final protected function addRemovedParam(string $name) : RemovedParameter` shortcut that mirrors `addParam()`.

Usage in an API method's `init()`:

```php
$this->addRemovedParam('mailLayoutAreaID')
    ->setReplacement('mailLayoutRoleID')
    ->setRemovedSince('2.0')
    ->setNote('Use a role ID from GetMailingLayoutAreaRoles.');
```

### 2. Rejection step in the lifecycle
- **New private step** `BaseAPIMethod::rejectRemovedParams() : void`, called in `_process()` right after `$this->time` is set and **before** `validate()`.
  - It calls `manageParams()->getRemovedParams()->detectUsed($this->request->getParams())`. If no declared removed name is present, it returns.
  - Otherwise it sends `errorResponse(APIMethodInterface::ERROR_REMOVED_PARAMETER_USED)->makeBadRequest()` with:
    - an error message made of the `getErrorMessage()` sentences of every offending declaration, joined with a space. It is passed as an argument, never as the format string: `setErrorMessage('%s', $message)`. `ErrorResponse::setErrorMessage()` runs `sprintf($message, ...$args)` (`src/classes/Application/API/ErrorResponse.php` L74–L78), and the composed text contains author-supplied note, version, and name text. A note such as "50% of clients" or one containing `%s` would otherwise throw `ValueError`/`ArgumentCountError` inside the boundary, which surfaces as 183008 instead of 183009, or would garble the message;
    - `addData([APIMethodInterface::RESPONSE_KEY_REMOVED_PARAMETERS => [...toArray() of each offending declaration]])`;
    - then `->send()`.
  - **Side effect of building the payload:** `ErrorResponse::getErrorData()` always adds `validationMessages` and `validationErrors` from `$this->method->getValidationResults()` (`ErrorResponse.php` L129–L141). Sending the 183009 response therefore runs parameter validation as a by-product, which also locks the param manager. This does not change the outcome: the rejection decision and its error code are fixed before `validate()` runs, and validation results never replace the code. It does mean the 183009 `errorData` carries `validationMessages`/`validationErrors` next to `removedParameters` (for example, listing the missing required `newName` when only `oldName` was sent). The contract this plan defines covers the `removedParameters` key only; tests must assert on that key, not on exact `errorData` equality.
- **Why before `validate()`:**
  - A rename usually makes the new name required. An old client that sends only the old name would otherwise get the generic 183003 ("missing required parameter") and lose the actionable "use X instead" message.
  - It also places the check ahead of the cache read, so a cached success response can never answer a request that uses a removed name.
- **Mode coverage:** the step sends directly, so it runs inside `executeWithThrowableReporting()`.
  - In `processReturn()` mode, `sendErrorResponse()` throws `APIResponseDataException`, which the boundary re-throws unchanged. `processReturn()` then returns an `ErrorResponsePayload` with code 183009.
  - In `process()` mode, it sends HTTP 400 with the JSON body.
  - No new `try`/`catch` is added, as `constraints.md` L261 requires.
- **Redaction:** the response data contains only metadata the method author declared, never submitted values. Using `addData()` is therefore correct and does not bypass the `addRequestData()` redaction channel.

### 3. Error code and response key (`APIMethodInterface`)
- `ERROR_REMOVED_PARAMETER_USED = 183009`: the next free number in the existing client-facing `183xxx` block (grep-verified unused). Its docblock documents HTTP 400, the pre-validation timing, and the `removedParameters` data key.
- `RESPONSE_KEY_REMOVED_PARAMETERS = 'removedParameters'`.
- These are constants only. No new interface method is added, so existing `APIMethodInterface` implementers and test doubles are unaffected.

### 4. Declaration error codes (`APIParameterException`)
These continue the `1831xx` block. 183105 is a historical gap and is not reused.
- `ERROR_REMOVED_PARAM_IS_REGISTERED = 183107`: a removed name is also registered as an active parameter, in either declaration order.
- `ERROR_REMOVED_PARAM_RESERVED_NAME = 183108`: a reserved name (`method`, `apiVersion`) is declared removed.
- `ERROR_REMOVED_PARAM_ALREADY_DECLARED = 183109`: the same name is declared removed twice.
- `ERROR_INVALID_REMOVED_PARAM_CONFIG = 183110`: an empty name, replacement, version, or note, a replacement equal to the removed name, or a name or replacement containing a character PHP mangles in request keys (`.`, space, `[`, NUL).

### 5. Versioning semantics
- A removal is enforced for **every** requested `apiVersion`. `removedSince` is informational: it appears in the message, the OpenAPI spec, and the HTML documentation.
- `removedSince` is **not** validated against `getVersions()`. Versions are later dropped from `getVersions()`, and such validation would then make a valid declaration throw on every request.
- **Interaction with `apiVersion` validation:**
  - The removed-parameter check runs first. A request with both a removed name and a dropped `apiVersion` gets 183009. Once the client fixes the parameter, the next request gets 183003 for the version.
  - A request with only a dropped `apiVersion` still gets 183003, unchanged.
  - The two mechanisms remain independent.

### 6. OpenAPI documentation
- `MethodConverter::buildOperation()` adds `x-removed-parameters` when the collection is not empty. It contains one object per declaration: `name`, `replacement`, `removedSince`, and `note` (null keys omitted), plus `errorCode: 183009`. When the collection is empty, the extension is omitted, matching the existing `x-` conventions.
- Removed names are **not** emitted into `parameters` or `requestBody`, and are **not** marked `deprecated`.
- `ResponseConverter::convertResponses()` appends a sentence to the 400 description for methods with removed parameters. The sentence names error 183009 and lists the removed names, following the code-bearing 403 precedent. Methods without declarations keep the existing description byte-for-byte.

### 7. HTML documentation
- `APIMethodDetailTmpl` gets a new private `generateRemovedParamList()`, called from `generateDocs()` right after `generateParamList()`. It renders a "Removed parameters" grid (name, replacement, removed since, note) with an abstract explaining that requests using these names are rejected with error 183009. It is skipped when the collection is empty.

## Rationale
- **Declaration through the param manager:** `APIMethodInterface::manageParams()` already exposes the manager to every consumer that needs it: the lifecycle, the OpenAPI converters, and the HTML template. Hanging the declaration there adds no interface method and fits where parameters already live. The `BaseAPIMethod::addRemovedParam()` shortcut keeps authoring symmetric with `addParam()`.
- **Dedicated `Removed/` types:** the collection owns storage, conflict lookups, detection, and serialization. It has four current consumers: the manager, `BaseAPIMethod`, the OpenAPI converters, and the HTML template. The value object has a named growth path, since a value-alias or auto-mapping mode could later be added to the same declaration. This also keeps `APIParamManager` from growing a fourth responsibility.
- **Pre-validation, direct-send rejection:** this is the only placement that is both stable (never collapsed into 183001/183008) and actionable (never overshadowed by 183003 for the now-required replacement). It reuses the proven `authorize()` pattern.
- **One standard code:** clients get one predictable code for "you used a removed parameter" across every method and application. The structured `removedParameters` data gives machine-readable detail without parsing messages.
- **Version-agnostic enforcement:** parameters are not registered per version in this framework, so scoping a removal to a version range would describe behaviour the method cannot actually provide.

## Considered Alternatives

| Decision | Chosen Shape | Alternatives Considered | Trade-Off Summary |
|----------|--------------|-------------------------|-------------------|
| Where the declaration lives | `APIParamManager::addRemovedParam()` backed by a new `RemovedParamCollection`, plus a `BaseAPIMethod::addRemovedParam()` shortcut | (a) New `getRemovedParameters()` method on `APIMethodInterface`; (b) a `RemovedParameter` type registered through `registerParam()` as a pseudo parameter; (c) a PHP attribute on the method class | (a) changes the interface and breaks every implementer and test double; (b) would make removed names show up in parameter lists, validation, and OpenAPI `parameters`; (c) keeps the declaration away from `init()`, where every other parameter concern lives, and needs reflection. The manager is already reachable from every consumer. |
| Declaration API shape | Fluent value object: required name; optional replacement, `removedSince`, note | Positional arguments `addRemovedParam($name, $replacement, $since, $message)`; plain array config | Optional fields as positional nulls are unreadable and hard to extend. Arrays are untyped. The fluent object matches `ParamTypeSelector`/`RuleTypeSelector` and gives a typed growth point. |
| Message field | Optional `note` appended to a framework-composed sentence | Fully custom message replacing the framework sentence | A fully custom message lets each method drop the replacement hint, which breaks the guarantee that the message always names the replacement. Appending keeps the standard sentence and still allows context. |
| Lifecycle placement | Private `rejectRemovedParams()` before `validate()` | (a) After `validate()`, before `authorize()`; (b) inside `validate()` as a `ParamValidationResults` entry; (c) hook in `collectRequestData()` | (a) and (b) produce 183003 or mix the error into generic validation, losing the stable code when the replacement is required. (c) collapses thrown errors into 183001 and repeats the HCP workaround. |
| Error code | One standard `183009` on `APIMethodInterface` | Per-declaration custom error code (for example to keep HCP's 185603) | Per-declaration codes defeat the "standard error" goal and keep `1831xx`/app codes scattered. Applications keep their old codes reserved and can document the change. |
| Detection semantics | Key present in `$_REQUEST` (`array_key_exists`), including empty values | Only non-empty values; `getParam() !== null` | An empty value is still a sign of an unmigrated client. `array_key_exists` on `getParams()` is exact and independent of request-param registration. It matches the HCP guard's behaviour for every value `$_REQUEST` can hold. |
| Version coupling | Enforced in all versions; `removedSince` informational and unvalidated | Enforce only when active version ≥ `removedSince`; validate `removedSince` against `getVersions()` | Parameters are version-agnostic, so a version-scoped removal would accept a name that no code reads. Validating against `getVersions()` breaks once old versions are dropped. |
| OpenAPI representation | Operation-level `x-removed-parameters` plus a code-bearing 400 description | Removed names in `parameters` with `deprecated: true`; appending to the method `description` | `deprecated` means "still accepted" and would leak the name into generated SDKs. Mutating the author's description mixes generated text into authored content. Extension plus 400 description follows existing patterns. |
| Removed-name format | Reject names (and replacements) containing `.`, space, `[`, or NUL with 183110 at declaration time | (a) Non-empty check only; (b) normalise the declared name the way PHP does (`.`/space → `_`) before matching; (c) a strict allowlist such as `^[A-Za-z0-9_-]+$` | (a) lets a declaration such as `legacy.flag` silently never fire, which is the failure class this feature exists to remove. (b) makes detection and the error message disagree on the name and would also catch clients legitimately sending `legacy_flag`. (c) is stricter than anything active parameters enforce and could reject names applications already use. The denylist matches PHP's verified mangling rules exactly and fails fast in `init()`, like every other declaration error. |
| Detection scope | Request parameters (`$_REQUEST`) only | Also scan JSON request bodies | Registered parameters are resolved only from `$_REQUEST`, and the JSON body is method-owned data parsed after validation. Scanning bodies would declare behaviour the active parameter does not have. Recorded in Deferred Items. |

## Pattern Alignment
- **Follows** the direct-send private lifecycle step pattern of `BaseAPIMethod::authorize()` (`src/classes/Application/API/BaseMethods/BaseAPIMethod.php` L212–L253).
- **Follows** the single execution boundary rule (`docs/agents/project-manifest/constraints.md` L261): no new `try`/`catch`.
- **Follows** the redaction channel rule (`constraints.md` L262): only declared metadata goes through `addData()`, and request values are never echoed.
- **Follows** the `183xxx` client-facing and `1831xx` declaration-error code blocks (`APIMethodInterface.php` L28–L77, `APIParameterException.php` L11–L15) and the error-number increment convention (`docs/agents/exception-usage.md`).
- **Follows** one concern per `Parameters/` sub-folder, each with its own `module-context.yaml` document (`src/classes/Application/API/Parameters/module-context.yaml`).
- **Follows** fluent configuration objects returned by manager calls (`ParamTypeSelector`, `RuleTypeSelector`) and `final protected` shortcuts on `BaseAPIMethod` (`addParam()`).
- **Follows** optional `x-` OpenAPI extensions omitted when empty (`MethodConverter.php` L109–L144) and code-bearing response descriptions (`ResponseConverter.php` L55–L63).
- **Follows** `array()` syntax, classmap autoloading, and namespaced classes (`AGENTS.md` §4).
- **Departure:** the first lifecycle step now runs before `validate()`. Until now `validate()` was always the first gate. This is justified because the step only inspects names the method author declared and returns HTTP 400 like validation does. Its output is already public through OpenAPI, so running it before `authorize()` reveals nothing new.

## Structural Improvements

| Structure | Observation | Decision | Reason |
|-----------|-------------|----------|--------|
| `src/classes/Application/API/BaseMethods/BaseAPIMethod.php::_process()` | No seam exists ahead of `validate()` for pre-validation request checks | Promoted to step 4 | The rejection step is that seam. It gets its own private, documented method rather than inline code, so future pre-validation checks have a clear home. |
| `src/classes/Application/API/Parameters/APIParamManager.php` | Already mixes registration, rules, and validation; adding removed-name logic inline would grow it further | Promoted to step 2 | Logic goes into the dedicated `RemovedParamCollection`, and the manager only delegates. |
| `BaseAPIMethod::getParam()` | Raw access to undeclared names is the only way to detect removed names today | Rejected | `getParam()` is a `final protected` API used by applications for legitimate raw access. Restricting or deprecating it is outside this feature's blast radius and would be a breaking change. The declaration removes the need to use it for removed names. |
| `src/classes/Application/API/Parameters/BaseAPIParameter.php` / `APIParamManager::registerParam()` | Active parameter names are not format-validated either, so an active parameter named with `.` or a space would also never resolve from `$_REQUEST` | Rejected | Adding the check to active parameters could make existing application `init()` code throw on upgrade, which turns a non-breaking minor release into a breaking one. The removed-name rule is applied only to the new declaration type, where no existing code depends on it. Extending it to active parameters is a separate, breaking-change decision. |
| `src/classes/Application/API/Parameters/APIParameterException.php` | Code gap at 183105 | Rejected | The gap may belong to a retired code that clients or logs still reference. New codes continue at 183107, and the gap stays. |
| `src/classes/Application/API/OpenAPI/ResponseConverter.php::convertResponses()` | Fixed 400 description, while 403 is already conditional | Promoted to step 6 | The 400 description becomes conditional in the same way, for methods with removed parameters only. |
| `src/classes/Application/API/ErrorResponsePayload.php` | Drops the HTTP status of the wrapped `ErrorResponse`, so `processReturn()` callers cannot see whether an error was a 400, 401, 403 or 500 | Promoted to step 1 | The removed-parameter contract promises HTTP 400 in both modes. Without the status on the payload, that promise cannot be observed or tested in return mode. The fix is a non-breaking getter backed by a private property, not an array key, so the serialized payload is unchanged. |
| `src/themes/default/templates/api/APIMethodDetailTmpl.php` | HTML docs cannot represent removed names | Promoted to step 7 | Documentation parity with OpenAPI. It is in the blast radius because it renders the same manager data. |

## Detailed Steps

1. **Add the error and response-key constants.** In `src/classes/Application/API/APIMethodInterface.php`, add `ERROR_REMOVED_PARAMETER_USED = 183009` with a docblock in the style of its neighbours (HTTP 400, raised before parameter validation, data key `removedParameters`) and `RESPONSE_KEY_REMOVED_PARAMETERS = 'removedParameters'`. In `src/classes/Application/API/Parameters/APIParameterException.php`, add `ERROR_REMOVED_PARAM_IS_REGISTERED = 183107`, `ERROR_REMOVED_PARAM_RESERVED_NAME = 183108`, `ERROR_REMOVED_PARAM_ALREADY_DECLARED = 183109`, and `ERROR_INVALID_REMOVED_PARAM_CONFIG = 183110`. In `src/classes/Application/API/ErrorResponsePayload.php`, capture `$response->getHttpStatusCode()` in a new private `int $httpStatusCode` property in the constructor, and expose it through `getHttpStatusCode() : int`. Do not add it to the array data, so the serialized payload stays unchanged.
2. **Create the declaration types** (new sub-folder `src/classes/Application/API/Parameters/Removed/`):
   - **New** `RemovedParameter.php` and **new** `RemovedParamCollection.php`, namespace `Application\API\Parameters\Removed`, with the API described in Approach §1.
   - Invalid configuration throws `APIParameterException::ERROR_INVALID_REMOVED_PARAM_CONFIG`, and a duplicate declaration throws `ERROR_REMOVED_PARAM_ALREADY_DECLARED`. Invalid configuration includes a name or replacement containing any character in `RemovedParameter::INVALID_NAME_CHARACTERS` (`.`, space, `[`, NUL); the developer details name the offending character and explain that PHP rewrites it in request keys. Each exception has a user-facing message and developer details that name the method and parameter, per `docs/agents/exception-usage.md`.
   - `detectUsed()` takes the request array as an argument so it stays pure and unit-testable.
   - Use `array()` syntax and full docblocks, and run `composer dump-autoload`.
3. **Wire the collection into the manager.** In `src/classes/Application/API/Parameters/APIParamManager.php`:
   - Add a lazily created `RemovedParamCollection`.
   - Add `addRemovedParam(string $name) : RemovedParameter`. It calls `requireNotValidated()`, rejects reserved names (183108) and names already in `$params` (183107), then delegates to the collection.
   - Add `getRemovedParams() : RemovedParamCollection`.
   - In `registerParam()`, add a check that throws 183107 when the name is already declared removed.
   - Update the class docblock with a "Removed parameters" section.
4. **Add the lifecycle rejection step.** In `src/classes/Application/API/BaseMethods/BaseAPIMethod.php`:
   - Add `final protected function addRemovedParam(string $name) : RemovedParameter` (delegates to the manager).
   - Add `private function rejectRemovedParams() : void` as described in Approach §2. Its docblock explains the pre-validation placement, the direct-send requirement, and the redaction rationale.
   - Pass the composed message as `setErrorMessage('%s', $message)`, never as the format string, and say why in an inline comment (author-supplied text may contain `%`; see Approach §2).
   - Call it in `_process()` immediately after `$this->time = Microtime::createNow();` and before `$this->validate();`.
   - Update the `executeWithThrowableReporting()` docblock list of pre-collector steps and the `init()` docblock (removed names are declared there).
5. **Add the test fixture method.**
   - **New** `tests/application/assets/classes/TestDriver/API/TestRemovedParamMethod.php` (`METHOD_NAME = 'TestRemovedParam'`, versions `1.0` and `2.0`, current `2.0`; shape of `TestErrorResponseMethod`, but returning a success payload).
   - It registers a required string parameter `newName` and declares:
     - `oldName`, with replacement `newName`, `removedSince('2.0')` and a note that contains literal `%` and `%s` sequences (for example `'Applies to 100% of clients; %s is not a placeholder.'`), so the lifecycle tests prove the message is not run through `sprintf` as a format string;
     - `legacyFlag`, with no replacement, no version and no note.
   - Run `composer dump-autoload`, then `composer test-file -- tests/AppFrameworkTests/API/Collection/MethodIndexEntryTest.php`, and confirm that `tests/application/storage/api/method-index.json` contains the fixture entry (per `docs/agents/project-manifest/testing.md` L235–L237).
6. **Extend OpenAPI generation.**
   - In `src/classes/Application/API/OpenAPI/MethodConverter.php::buildOperation()`, add `x-removed-parameters` from `manageParams()->getRemovedParams()` when the collection is not empty, through a new private `buildRemovedParamsData()` helper. Each entry carries `name`, its non-null optional keys, and `errorCode` set to `APIMethodInterface::ERROR_REMOVED_PARAMETER_USED`.
   - Update the class docblock's composition list.
   - In `src/classes/Application/API/OpenAPI/ResponseConverter.php::convertResponses()`, extend the 400 description for methods with removed parameters: `'Validation error or invalid parameters. Requests using a removed parameter ({names}) are rejected with error 183009.'`. Other methods keep the exact current string.
   - **Update the existing mock helpers in the same step**, because the converters now call `manageParams()->getRemovedParams()` on every method. An unstubbed PHPUnit double returns an auto-generated `RemovedParamCollection` double whose `isEmpty()` returns `false`, which would add an empty `x-removed-parameters` and change the 400 description in every existing test case:
     - `tests/AppFrameworkTests/API/OpenAPI/MethodConverterTest.php::createEmptyParamManager()` (L65–L70): also stub `getRemovedParams()` to return a real, empty `new RemovedParamCollection()`. `RemovedParamCollection` is a plain class with no dependencies, so a real instance is used rather than a double.
     - `tests/AppFrameworkTests/API/OpenAPI/ResponseConverterTest.php`: add a shared private helper `createParamManagerStub(?RemovedParamCollection $removed = null) : APIParamManager` that stubs `getRemovedParams()` (default: a real empty collection), and stub `manageParams()` with it in `createBasicMethodMock()`, `createJsonMethodMock()`, `createKeyMethodMock()`, and the inline `createStub(JSONResponseInterface::class)` at L173.
     - `tests/AppFrameworkTests/API/OpenAPI/OpenAPIGeneratorTest.php::createEmptyParamManager()` (L77–L82, returned from `manageParams()` in `buildMethodStub()` at L124): also stub `getRemovedParams()` to return a real, empty `new RemovedParamCollection()`.
     - `tests/AppFrameworkTests/API/OpenAPI/SecurityAndValidationTest.php::createParamManagerMock()` (L46–L52, returned from `manageParams()` in `configureMethodStubs()` at L96): also stub `getRemovedParams()` to return a real, empty `new RemovedParamCollection()`, next to the existing `getParams()`/`getRules()` stubs.
     - The new test cases that need declarations pass a real `RemovedParamCollection` populated through `add()`, instead of stubbing `isEmpty()`/`getAll()`.
   - Every pre-existing case in all four files (`MethodConverterTest`, `ResponseConverterTest`, `OpenAPIGeneratorTest`, `SecurityAndValidationTest`) must pass with unchanged assertions. That is the acceptance gate for AC-09 (step 13 runs all four files in full).
7. **Extend the HTML documentation.** In `src/themes/default/templates/api/APIMethodDetailTmpl.php`, add a private `generateRemovedParamList()` called from `generateDocs()` after `generateParamList()`. It renders a grid with `t()`-wrapped column titles (Parameter, Replacement, Removed since, Note) and an abstract naming error 183009, and is skipped when the collection is empty.
8. **Write the unit and integration tests** listed in the Test Plan.
9. **Write the live-HTTP regression test** (`tests/AppFrameworkTests/API/RemovedParamLiveHTTPTest.php`, `#[Group('live-http')]`). It is modelled on `UnexpectedThrowableLiveHTTPTest` and dispatches `TestRemovedParam` with `oldName` through `tests/application/api/index.php`.
10. **Update the module documentation:**
    - `src/classes/Application/API/README.md`: new Key Concepts bullet "Removed parameters".
    - `src/classes/Application/API/Parameters/README.md`: Key Concepts bullet plus a Folder Structure row for `Removed/`.
    - `src/classes/Application/API/OpenAPI/README.md`: operation-fields table row for `x-removed-parameters`, a new section next to "Validation rules (`x-validation-rules`)", and the 400 description note.
    - `src/classes/Application/API/Parameters/module-context.yaml`:
      - add `Removed/` to the core document's `excludePatterns`;
      - add a new document `modules/api/parameters/architecture-removed.md` for `./Removed` with the same `php-content-filter` options;
      - add the keyword `removed parameter (name a method has dropped; requests using it are rejected with 183009)` and mention the feature in the module description.
11. **Update the manifest convention.** In `docs/agents/project-manifest/constraints.md` § "API Methods", add the rule: "When a request parameter is renamed or removed, declare it with `addRemovedParam()` in `init()`; never hand-write a `getParam()` guard in `collectRequestData()`." Include the reason: thrown errors there collapse into 183001, and validation of the new name would otherwise pre-empt the message.
12. **Add the changelog entry.** In `changelog.md`, add a new top entry `## v7.6.0 - API Removed-Parameter Declaration` (a minor bump, because this is a new, non-breaking feature; adjust the number if the user's release numbering differs). Write it in the house style: bold one-line summary, then `- API: …` and `- OpenAPI: …` bullets. Do not edit `VERSION` (it is auto-generated).
13. **Run static analysis and the scoped tests:**
    - `composer analyze`
    - `composer test-filter -- RemovedParam`
    - `composer test-file -- tests/AppFrameworkTests/API/OpenAPI/MethodConverterTest.php`
    - `composer test-file -- tests/AppFrameworkTests/API/OpenAPI/ResponseConverterTest.php`
    - `composer test-file -- tests/AppFrameworkTests/API/OpenAPI/OpenAPIGeneratorTest.php`
    - `composer test-file -- tests/AppFrameworkTests/API/OpenAPI/SecurityAndValidationTest.php`
      (all four OpenAPI test files with `APIParamManager` doubles run in full, not only the new cases, so a missing `getRemovedParams()` stub in a pre-existing case surfaces here)
    - `composer test-file -- tests/AppFrameworkTests/API/ProcessReturnTest.php`
    - `composer test-filter -- API` (regression across the API module)
    - `composer test-group -- live-http`, when a web server is available at `APP_URL`

    Do not include regenerated `.context/` files in the change set. They are regenerated on `main` during the pre-release flow.

## Dependencies
- Step 1 must come before steps 2 to 4 (they reference the constants).
- Step 2 must come before step 3, and step 3 before steps 4, 6, and 7.
- Step 5 depends on step 4 (the fixture uses `addRemovedParam()`).
- Steps 8 and 9 depend on steps 4 to 7. Step 9 also needs a reachable `APP_URL`. Without one, the test is skipped by the default suite's `live-http` exclusion, and this is reported as not run.
- Steps 10 to 12 depend on the final API shape (steps 1 to 7).
- Step 13 comes last.
- No cross-repository dependency: the plan changes only `application-framework`.

## Required Components
- `src/classes/Application/API/APIMethodInterface.php` (modified)
- `src/classes/Application/API/Parameters/APIParameterException.php` (modified)
- `src/classes/Application/API/ErrorResponsePayload.php` (modified: `getHttpStatusCode()`)
- `src/classes/Application/API/Parameters/Removed/RemovedParameter.php` (**new**)
- `src/classes/Application/API/Parameters/Removed/RemovedParamCollection.php` (**new**)
- `src/classes/Application/API/Parameters/APIParamManager.php` (modified)
- `src/classes/Application/API/BaseMethods/BaseAPIMethod.php` (modified)
- `src/classes/Application/API/OpenAPI/MethodConverter.php` (modified)
- `src/classes/Application/API/OpenAPI/ResponseConverter.php` (modified)
- `src/themes/default/templates/api/APIMethodDetailTmpl.php` (modified)
- `tests/application/assets/classes/TestDriver/API/TestRemovedParamMethod.php` (**new**)
- `tests/application/storage/api/method-index.json` (regenerated)
- `tests/AppFrameworkTests/API/Parameters/RemovedParameterTest.php` (**new**)
- `tests/AppFrameworkTests/API/Parameters/RemovedParamManagerTest.php` (**new**)
- `tests/AppFrameworkTests/API/RemovedParamRejectionTest.php` (**new**)
- `tests/AppFrameworkTests/API/RemovedParamLiveHTTPTest.php` (**new**)
- `tests/AppFrameworkTests/API/OpenAPI/MethodConverterTest.php` (modified)
- `tests/AppFrameworkTests/API/OpenAPI/ResponseConverterTest.php` (modified)
- `tests/AppFrameworkTests/API/OpenAPI/OpenAPIGeneratorTest.php` (modified: `createEmptyParamManager()` stub, new fixture case)
- `tests/AppFrameworkTests/API/OpenAPI/SecurityAndValidationTest.php` (modified: `createParamManagerMock()` stub)
- Documentation files listed under Documentation Updates

## Assumptions
- Error numbers 183009 and 183107–183110 can be allocated by incrementing within the existing blocks, with no call to the external error-code service (the convention allows incrementing within a class block). The grep verified they are unused in the framework.
- Applications may use the same `183xxx` numbers only through the framework constants. No consuming application defines 183009 itself.
- `$_REQUEST` (through `Application_Request::getParams()`) is the complete source of registered request parameter values. This holds for `BaseAPIParameter`, `StringParameter`, and `ClearableStringParameter`.
- PHP's request-key mangling (`.`, space, and unmatched `[` become `_`, `[…]` creates an array key, NUL truncates) is the same on the project's PHP 8.4 target as on the PHP 8.5.11 CLI where it was verified with `parse_str()`. The rule has been stable across PHP 8 releases, and the data-provider test in `RemovedParameterTest` pins the framework's matching denylist.
- The test environment's development-mode error data does not affect the asserted `removedParameters` data, because it goes through `addData()`, which is never redacted.

## Constraints
- PHP 8.4, `array()` syntax only, namespaced classes, classmap autoloading (`composer dump-autoload` after adding classes).
- No second `try`/`catch` around `_process()`. No request-derived values in `addData()`.
- No changes to `APIMethodInterface` method signatures. Only constants are added.
- Behaviour of methods without declarations must stay byte-identical, including OpenAPI output.
- No changes in `hcp-editor` or any other repository. No release, tag, or version-constraint change. Regenerated `.context/` files are not committed from the feature branch.

## Out of Scope
- Any change in the HCP Editor: replacing `rejectRemovedAreaRoleParam()`, `TemplateAPIInterface::PARAM_REMOVED_AREA_ROLE_ID`, or `TemplateAPIException::ERROR_REMOVED_AREA_ROLE_PARAM` (see Human Actions).
- Rejecting unknown or undeclared parameters in general (strict mode). This plan only covers names the method explicitly declares.
- Detecting removed keys inside JSON request bodies (see Deferred Items).
- Removed or renamed HTTP header parameters.
- Removing or changing the `apiVersion` enum validation (183003 for dropped versions stays as it is).
- Regenerating the git-tracked `tests/application/api/openapi.json`. No test reads it, and generated artefacts are refreshed in the pre-release flow.

## Human Actions

| # | Action | When | Why an agent cannot do it |
|---|--------|------|---------------------------|
| 1 | Release the framework version containing this feature (changelog heading proposed as v7.6.0) | After the run | Releases, tags, and publishing belong to the user |
| 2 | In the HCP Editor, raise the framework constraint and schedule a follow-up plan that replaces `GlobalContentsListAPITrait::rejectRemovedAreaRoleParam()` with `addRemovedParam(TemplateAPIInterface::PARAM_REMOVED_AREA_ROLE_ID)->setReplacement(TemplateAPIInterface::PARAM_AREA_ROLE_ID)->setNote(…)` in both affected methods. That plan should also decide how to announce the client-visible code change from 185603 to 183009, keep 185603 reserved as its deferred-topic constraint requires, and update `deferred-topics.md` | After the run | It depends on the framework release (human action 1), and raising a consumer's version constraint is a user decision. It also needs its own HCP Editor plan |

## Acceptance Criteria
- AC-01: An API method can declare a removed parameter in `init()` via `$this->addRemovedParam($name)`, with optional `setReplacement()`, `setRemovedSince()`, and `setNote()`. The declaration can be read back through `manageParams()->getRemovedParams()`.
- AC-02: A request that contains a declared removed name (including with an empty value) is rejected with error code `183009` (`APIMethodInterface::ERROR_REMOVED_PARAMETER_USED`) and HTTP status 400 via `process()`, and with an `ErrorResponsePayload` whose `getErrorCode()` is 183009 and whose new `getHttpStatusCode()` is 400 via `processReturn()`.
- AC-03: The error message names the removed parameter. When a replacement is declared, it names the replacement ("Use `newName` instead."). It includes `removedSince` and the note when they are declared. A note containing literal `%` or `%s` appears verbatim, and the response is still 183009 (never 183008).
- AC-04: The error data contains `removedParameters`: one entry per offending name, with `name`, `replacement`, `removedSince`, and `note`. It never contains the submitted value. This criterion concerns the `removedParameters` key only; the standard `validationMessages`/`validationErrors` keys that `ErrorResponse::getErrorData()` always adds may also be present.
- AC-05: The rejection happens before parameter validation. A request that sends only the removed name, while the replacement is required, receives 183009, not 183003. A request with a removed name never receives a cached response, even when a cache entry for the same method and version exists.
- AC-06: Requests without any declared removed name behave exactly as before. A request with only an invalid or dropped `apiVersion` still receives 183003.
- AC-07: Misconfigured declarations throw `APIParameterException` at declaration time with distinct codes: removed name registered as an active parameter in either order (183107), reserved name (183108), duplicate declaration (183109), invalid or empty fields, a replacement equal to the name, or a name or replacement containing `.`, space, `[`, or NUL (183110). Declaring after validation throws `APIException::ERROR_CANNOT_MODIFY_AFTER_VALIDATION`.
- AC-08: The generated OpenAPI operation of a method with removed parameters contains `x-removed-parameters`, one entry per declaration, including `errorCode: 183009`. Its 400 response description names error 183009 and the removed names. Removed names do not appear in `parameters` or `requestBody`.
- AC-09: For methods without removed parameters, the OpenAPI operation has no `x-removed-parameters` key and the 400 description is unchanged. Every pre-existing case in `MethodConverterTest`, `ResponseConverterTest`, `OpenAPIGeneratorTest`, and `SecurityAndValidationTest` passes with unchanged assertions.
- AC-10: The HTML method documentation shows a "Removed parameters" section for methods with declarations, and no such section otherwise.
- AC-11: `composer analyze` reports no new errors, and the API test suite (`composer test-filter -- API`) passes.
- AC-12: The module READMEs, the Parameters `module-context.yaml`, the manifest `constraints.md`, and `changelog.md` document the feature, including error code 183009 and the authoring rule.

## Testing Strategy
- **Unit level:** test the value object and collection in isolation. `detectUsed()` is pure and receives synthetic request arrays.
- **Manager level:** test declaration conflicts in both orders, reserved names, duplicates, and the post-validation lock, using a real `APIParamManager` bound to the fixture method.
- **Lifecycle level:** in-process `processReturn()` tests against the `TestRemovedParam` fixture cover the code, HTTP status, message, data shape, pre-validation precedence, `apiVersion` interaction, and the unaffected success path.
- **Transport level:** a live-HTTP test proves the `process()` path returns HTTP 400 JSON with code 183009.
- **OpenAPI level:** extend the mock-based `MethodConverterTest`/`ResponseConverterTest` with real `RemovedParamCollection` instances returned from stubbed `getRemovedParams()` calls, update their shared mock helpers (and those of `OpenAPIGeneratorTest` and `SecurityAndValidationTest`) so every existing case gets an empty collection, and run the real-method generation against the fixture.
- **Assertion rule for error data:** assert on the `removedParameters` key (and on the absence of submitted values), never on exact `errorData` equality, because `ErrorResponse::getErrorData()` always adds validation keys.
- **Regression:** the full API test filter and PHPStan.

## Test Plan
- `tests/AppFrameworkTests/API/Parameters/RemovedParameterTest.php` (**new**) — `getErrorMessage()` for each combination: name only, plus replacement, plus since, plus note. A note containing `%` and `%s` is returned verbatim by `getErrorMessage()` and `toArray()`. `toArray()` keys and null handling. Setters reject empty values and a replacement equal to the name (183110). A data provider of malformed names (`legacy.flag`, `legacy flag`, `legacy[x`, `legacy[]`, a name containing `"\0"`) throws 183110 from both the constructor and `setReplacement()`, while names PHP preserves verbatim (`legacy_flag`, `legacy-flag`, `a]b`) are accepted. — AC-01, AC-03, AC-07
- `RemovedParameterTest::test_detectUsed*` (in the same file, or in a `RemovedParamCollectionTest` section) — `detectUsed()` matches a present key, including an empty-string value, ignores absent keys, returns several matches sorted by name, and returns `[]` for an empty collection. — AC-02, AC-06
- `tests/AppFrameworkTests/API/Parameters/RemovedParamManagerTest.php` (**new**) — `addRemovedParam()` after `registerParam()` of the same name throws 183107, and `registerParam()` after `addRemovedParam()` throws 183107. Reserved `method`/`apiVersion` throw 183108. A duplicate throws 183109. Declaring after `getValidationResults()` throws `APIException::ERROR_CANNOT_MODIFY_AFTER_VALIDATION`. `getRemovedParams()` reflects the fixture's declarations. — AC-01, AC-07
- `tests/AppFrameworkTests/API/RemovedParamRejectionTest.php` (**new**, extends `APITestCase`):
  - `test_removedParamIsRejectedWithStandardCode` — `processReturn()` with `newName` + `oldName` gives code 183009 and `getHttpStatusCode() === 400`. — AC-02
  - `test_emptyValueOfRemovedParamIsRejected` — `oldName=''` gives 183009. — AC-02
  - `test_messageNamesReplacementVersionAndNote` — the message contains `oldName`, `newName`, `2.0`, and the note, with the note's literal `%` and `%s` sequences intact, and the code is 183009 (not 183008). For `legacyFlag`, the message has no "instead" sentence. — AC-03
  - `test_errorDataListsOffendersWithoutValues` — `removedParameters` has entries for both `oldName` and `legacyFlag` when both are sent, and a sentinel submitted value never appears in the serialized payload. Assert on the `removedParameters` key only, not on exact `errorData` equality (the validation keys are also present). — AC-04
  - `test_rejectionPrecedesRequiredValidation` — only `oldName` sent (required `newName` missing) gives 183009, not 183003. The `validationErrors` key may list `newName`; the test asserts only the code and `removedParameters`. — AC-05
  - `test_removedParamNeverServedFromCache` — uses an anonymous subclass of `tests/application/assets/classes/TestDriver/API/TestCacheableMethod.php` whose `init()` declares `oldName` as removed. A first `processReturn()` without `oldName` warms the cache (`getCollectCount() === 1`). A second instance called with `oldName` set returns an `ErrorResponsePayload` with code 183009 rather than the cached `ResponsePayload`, and its `getCollectCount()` stays 0. `tearDown()` calls `APICacheManager::clearAll()`, as `tests/AppFrameworkTests/API/Cache/APICacheIntegrationTest.php` does. — AC-05
  - `test_removedParamWinsOverInvalidApiVersion` and `test_invalidApiVersionAloneStillReturns183003` — documents the ordering and confirms the `apiVersion` validation is unchanged. — AC-05, AC-06
  - `test_requestWithoutRemovedParamsSucceeds` — `newName` only gives a success payload. — AC-06
- `tests/AppFrameworkTests/API/ProcessReturnTest.php` (modified) — `test_errorPayloadExposesHttpStatusCode`: the `TestErrorResponse` payload reports the status of its `ErrorResponse`, and its serialized array keys are unchanged (`errorCode`, `errorMessage`, `errorData`). — AC-02
- `tests/AppFrameworkTests/API/RemovedParamLiveHTTPTest.php` (**new**, `#[Group('live-http')]`) — real dispatch with `oldName` gives HTTP 400, JSON content type, `code === 183009`, and `data.removedParameters[0].replacement === 'newName'`. — AC-02, AC-04
- `tests/AppFrameworkTests/API/OpenAPI/MethodConverterTest.php` (modified):
  - `createEmptyParamManager()` updated to stub `getRemovedParams()` with a real empty `RemovedParamCollection`; all pre-existing cases pass unchanged. — AC-09
  - `test_xRemovedParameters_includedWhenDeclared` — entries carry `name`/`replacement`/`removedSince`/`note`/`errorCode` and omit null keys. — AC-08
  - `test_xRemovedParameters_omittedWhenNone` — AC-09
  - `test_removedParams_notInParametersOrRequestBody` — AC-08
- `tests/AppFrameworkTests/API/OpenAPI/ResponseConverterTest.php` (modified):
  - New shared `createParamManagerStub()` helper wired into `createBasicMethodMock()`, `createJsonMethodMock()`, `createKeyMethodMock()`, and the inline stub at L173; all pre-existing cases pass unchanged. — AC-09
  - `test_400Description_mentionsRemovedParamsCode` — AC-08
  - `test_400Description_unchangedWithoutRemovedParams` — asserts the exact existing string. — AC-09
- `tests/AppFrameworkTests/API/OpenAPI/OpenAPIGeneratorTest.php` (modified) — `test_fixtureMethodDocumentsRemovedParameters`: generates the spec to `sys_get_temp_dir()` and asserts that the `/api/TestRemovedParam` operation contains `x-removed-parameters` with `oldName` and `legacyFlag`. — AC-08
  - `createEmptyParamManager()` updated to stub `getRemovedParams()` with a real empty `RemovedParamCollection`; all pre-existing cases pass unchanged. — AC-09
- `tests/AppFrameworkTests/API/OpenAPI/SecurityAndValidationTest.php` (modified) — `createParamManagerMock()` updated to stub `getRemovedParams()` with a real empty `RemovedParamCollection`; all pre-existing cases (including the `x-validation-rules` cases) pass unchanged. — AC-09
- `tests/AppFrameworkTests/API/RemovedParamRejectionTest::test_documentationRendersRemovedParamSection` — renders the method documentation for `TestRemovedParam` and asserts that the "Removed parameters" heading and `oldName` are present, and that the heading is absent for `TestErrorResponse`. If template rendering cannot be instantiated in-process, fall back to a test of a small public, side-effect-free accessor the template uses, and record that in the synthesis. — AC-10
- `composer analyze` and `composer test-filter -- API` — AC-11
- Documentation review during code review against the Documentation Updates list — AC-12

## Documentation Updates
- `src/classes/Application/API/README.md` — new Key Concepts bullet "Removed parameters": the declaration API, pre-validation placement and its reason, 183009 with HTTP 400 in both modes, the `removedParameters` data shape, version-agnostic enforcement, the name-format rule (no `.`, space, `[`, or NUL, because PHP mangles them in request keys), and the redaction note. Extend the "Response Payload" bullet with `ErrorResponsePayload::getHttpStatusCode()`. Add the `Parameters/Removed/` mention to the folder table row for `Parameters/`.
- `src/classes/Application/API/Parameters/README.md` — Key Concepts bullet "Removed Parameters", plus a Folder Structure row `Removed/` (`RemovedParameter`, `RemovedParamCollection`).
- `src/classes/Application/API/Parameters/module-context.yaml` — `Removed/` added to the core document's `excludePatterns`, a new `architecture-removed.md` document, and the description and keyword update.
- `src/classes/Application/API/OpenAPI/README.md` — operation-fields table row `x-removed-parameters`, a new "Removed parameters (`x-removed-parameters`)" section with a JSON example, and the 400 description behaviour. Update the `ResponseConverter` row if it lists descriptions.
- `docs/agents/project-manifest/constraints.md` — "API Methods" rule: declare removed or renamed parameters with `addRemovedParam()`, and never hand-write `getParam()` guards.
- `changelog.md` — new top entry (proposed `v7.6.0 - API Removed-Parameter Declaration`) in house style.
- `docs/agents/project-manifest/testing.md` — add `RemovedParamLiveHTTPTest` to the live-HTTP test table (L404–L407).
- `.context/` — not edited by hand and not committed from the feature branch. It is regenerated by `composer build` on `main` in the pre-release flow.

## Deferred Items

| # | Deferred Item | Origin | Reason Deferred | Notes |
|---|---------------|--------|-----------------|-------|
| 1 | Detect removed keys inside JSON request bodies (`JSONRequestTrait` methods) | This plan, Considered Alternatives "Detection scope" | Registered parameters are resolved only from `$_REQUEST`, and JSON bodies are method-owned data parsed after validation. Detecting body keys would need a separate "removed body key" concept. | Reconsider when a JSON-body method renames a body key; the `RemovedParamCollection::detectUsed()` seam takes any array |
| 2 | Optional strict mode that rejects all undeclared request parameters | Root cause noted in the request ("framework silently ignores undeclared parameters") | A breaking behaviour change for every consumer, which needs an opt-in design and a consumer audit | Removed-name declarations cover the rename case without breaking anyone |

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **The pre-validation placement changes which error an invalid request gets** | Only requests that contain a declared removed name are affected, and only methods with declarations have any. AC-06 and dedicated tests lock the unchanged behaviour (including 183003 for `apiVersion`). |
| **Running before `authorize()` reveals removed names to unauthenticated callers** | The names are already public in OpenAPI and the HTML docs. No request values or key data are returned. The security audit stage should confirm this. |
| **A removed name collides with an active parameter and silently shadows it** | Two-way conflict checks at declaration time (183107) and a test for each order. |
| **A removed name that PHP mangles in request keys (for example `legacy.flag`) never matches, so the declaration silently never fires** | The constructor and `setReplacement()` reject `.`, space, `[`, and NUL with 183110 at declaration time, matching PHP's verified mangling rules. A data-provider test covers each forbidden character and the verbatim-preserved neighbours. |
| **The code collides with an application-defined 183009 or 1831xx** | Grep-verified unused in the framework. The codes sit in framework-owned blocks, which applications reach only through constants. |
| **`composer dump-autoload` wipes `method-index.json`, so the fixture "method not found" error appears** | Step 5 rebuilds it through `MethodIndexEntryTest` and verifies the entry, per `testing.md`. |
| **The live-HTTP test cannot run in the agent environment** | It is excluded from the default suite by design. In-process `processReturn()` tests cover the logic, and the synthesis reports whether the live run happened. |
| **Author-supplied text containing `%` breaks `sprintf` in `ErrorResponse::setErrorMessage()`, turning 183009 into 183008** | The composed message is always passed as `setErrorMessage('%s', $message)` (Approach §2, step 4). The fixture note contains `%` and `%s`, and `test_messageNamesReplacementVersionAndNote` asserts both the verbatim text and code 183009. |
| **Existing OpenAPI converter tests silently change output, because unstubbed `getRemovedParams()` doubles look non-empty** | Step 6 updates the shared mock helpers in `MethodConverterTest`, `ResponseConverterTest`, `OpenAPIGeneratorTest` (`createEmptyParamManager()`), and `SecurityAndValidationTest` (`createParamManagerMock()`) to return a real empty `RemovedParamCollection`, and step 13 runs all four files in full as the AC-09 gate. |
| **The HTML template is hard to test in-process** | A fallback test obligation is defined in the Test Plan. The template change is small and covered by code review. |
| **The HCP migration changes a client-visible code (185603 to 183009)** | Recorded as part of the Human Actions follow-up decision. 185603 stays reserved. |

## Recommended Workflow
- **Workflow:** ledger
- **Rationale:** The change cuts across the API lifecycle, the parameter subsystem, OpenAPI generation, and HTML docs. It introduces a new lifecycle step that precedes validation and authorization and a new client-facing error code, which warrants formal QA, a security audit (pre-authorization placement, redaction), and code review stages.
