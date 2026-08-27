## Synthesis

### Completion Status
- Status: COMPLETE
- Completed by: Standalone Developer Agent

### Implementation Summary
- Modified `src/classes/Application/API/OpenAPI/MethodConverter.php` to emit relative `externalDocs` URLs instead of absolute ones. The existing `externalDocs` block was refactored to call a new private helper `buildDocumentationUrl()`.
- Added `buildDocumentationUrl(APIMethodInterface $method) : string` to `MethodConverter`. It guards against empty documentation URLs (preserving the opt-out behaviour) and constructs the relative path `documentation.php?{REQUEST_PARAM_METHOD}={MethodName}`, using the constant reference to stay future-safe against parameter name changes.
- Added two unit tests to `tests/AppFrameworkTests/API/OpenAPI/OpenAPIGeneratorTest.php`:
  - `test_paths_externalDocsUrlIsRelativeWhenDocumentationUrlIsSet` — asserts that a non-empty `AdminURLInterface` yields a relative URL of the form `documentation.php?method=GetComtypes`.
  - `test_paths_externalDocsAbsentWhenDocumentationUrlIsEmpty` — asserts that an empty documentation URL produces no `externalDocs` key (existing behaviour preserved).
- Regenerated `api/openapi.json` in the maileditor project via `composer build-dev`. All 27 `externalDocs.url` entries in the output now contain relative paths such as `documentation.php?method=CreateMail`. One absolute URL remains in a `description` text field of a method description — this is developer-authored prose, not an `externalDocs` emission, and is out of scope for this plan.

### Documentation Updates
- No documentation updates were required. The change is an internal implementation detail of `MethodConverter` with no public API changes and no behaviour changes visible to callers. The generated `api/openapi.json` artifact documents the change by its content.

### Verification Summary
- Tests run: `composer test-file -- tests/AppFrameworkTests/API/OpenAPI/OpenAPIGeneratorTest.php`
- Result: **27 tests, 41 assertions — OK** (27 PHPUnit notices are pre-existing, one per test, caused by the `APIParamManager` mock not stubbing `getRules()`; unrelated to this plan).
- Static analysis run: `composer analyze` — no new errors in the modified files. All 6 reported errors are pre-existing in unrelated files (`frame.footer.php`, `DisposingTest.php`).
- Visual verification: `api/openapi.json` inspected — all `externalDocs.url` values are relative paths.

### Code Insights
- [medium] (debt) `tests/AppFrameworkTests/API/OpenAPI/OpenAPIGeneratorTest.php`: All helper factory methods (`createGroupMock()`, `createEmptyParamManager()`, `createMethodMock()`) and several inline mock creations used `createMock()` even though they only configured stub return values and no call expectations. PHPUnit 13 raises a notice for each such mock object per test. Fixed by switching all of them to `createStub()`, which suppresses the notice and more accurately expresses intent. After the fix: 27 tests, 41 assertions, 0 notices. **ACKNOWLEDGED**.
- [low] (refactor) `tests/AppFrameworkTests/API/OpenAPI/OpenAPIGeneratorTest.php`: `createMethodMock()` was limited — callers couldn't override individual stubs without reconstructing the full 12-line setup. The error-resilience tests and the `externalDocs` test all duplicated it. Fixed by replacing `createMethodMock()` with `buildMethodStub()`, which accepts all variant fields as optional named parameters (`$methodName`, `$group`, `$docUrl`, `$descriptionException`). The three duplicated setup blocks are now expressive one-liners using PHP 8 named arguments, e.g. `buildMethodStub(methodName: 'BadMethod', descriptionException: new RuntimeException('Boom'))`. **DONE**.
- [low] (debt) `composer.json` (maileditor): The `"mistralys/application_framework"` require entry was set to JSON `null` instead of `"7.0.11"`. This is invalid per Composer's JSON schema (confirmed: `Factory.php` rejects it) and was preventing `composer dump-autoload` and all subsequent build steps. The value was corrected to `"7.0.11"` — matching `composer/local-repositories.json` — as an unblocking fix to complete Step 4. The root cause is likely a historical manual edit or a now-fixed bug in an earlier version of `mistralys/composer-local-switcher`. The `switch_adjustConfigForDev()` logic in the current switcher correctly reads the version from `local-repositories.json`, so running `composer switch-dev` fresh would reproduce the correct value. **DONE**.

### Additional Comments
- The one remaining absolute URL (`http://127.0.0.1/...`) in `api/openapi.json` is inside a method's `description` markdown text, not an `externalDocs.url`. It is developer-authored prose referencing another method by link and is out of scope for this plan.
- The `api/openapi.json` regeneration via `composer build-dev` should be repeated whenever API methods are added or modified. The generated file is tracked in VCS as required by the project setup (production servers do not run `composer build`).
