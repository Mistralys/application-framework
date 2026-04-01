## Synthesis

### Completion Status
- Status: COMPLETE
- Completed by: Standalone Developer Agent

### Implementation Summary
- Added a generic output string replacement mechanism to `OpenAPIGenerator` via a new `$outputReplacements` property, a fluent `addOutputReplacement(string $search, string $replace): self` setter, a private `applyOutputReplacements(string $json): string` helper, and a call to that helper inside `generate()` between JSON serialisation and file writing.
- Wired the `APP_URL → {APPLICATION_URL}` substitution in `APIManager::generateOpenAPISpec()`, guarded by `defined('APP_URL')` to remain safe in environments where the constant is absent.
- Regenerated `api/openapi.json` in the Maileditor project via `composer build-dev`: 22 former `APP_URL` occurrences were replaced with `{APPLICATION_URL}`; zero occurrences of the local base URL remain; the JSON remains syntactically valid.
- Added 3 new unit tests to `OpenAPIGeneratorTest`: `test_addOutputReplacement_returnsSelf`, `test_generate_outputReplacementIsApplied`, and `test_generate_multipleOutputReplacementsAreAllApplied`.

### Documentation Updates
- No documentation updates were required because `addOutputReplacement()` is a small infrastructure method on `OpenAPIGenerator` whose behaviour is fully described by the doc-comment attached to it. The existing module context documentation will be refreshed automatically on the next `composer build` run.

### Verification Summary
- Tests run: `composer test-file -- tests/AppFrameworkTests/API/OpenAPI/OpenAPIGeneratorTest.php` → **30 tests, 48 assertions, OK**
- Static analysis run: `composer analyze` → **6 errors (all pre-existing, none in modified files)**
- Maileditor `api/openapi.json`: **22 replacements applied, 0 residual `127.0.0.1` occurrences, JSON valid**
- Result: Pass (no regressions introduced)

### Code Insights
- [medium] (debt) `tests/AppFrameworkTests/API/OpenAPI/MethodConverterTest.php`: **Fixed.** `test_externalDocs_includedWhenDocUrlNonEmpty` was asserting the absolute URL (`https://example.com/api/TestMethod`) but `MethodConverter::buildDocumentationUrl()` was already changed (on this branch) to return a relative URL (`documentation.php?method=TestMethod`). Updated the assertion to match the new behaviour. **DONE**.
- [low] (convention) `src/classes/Application/API/OpenAPI/OpenAPIGenerator.php`: The `$conversionErrors` and `$outputReplacements` properties are both `private array`, but `$conversionErrors` carries a distinct lifecycle note (reset on each `toArray()` call) that is only communicated via its doc-comment. Consider adding a comparable lifecycle note to similar stateful properties for consistency when the class grows.

### Additional Comments
- The `build-dev` output showed pre-existing `relatedModules` asymmetry warnings from `ModulesOverviewGenerator`. These are unrelated to this implementation and should be addressed separately.
