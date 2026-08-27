## Synthesis

### Completion Status
- Date: 2026-05-19
- Status: COMPLETE
- Completed by: Standalone Developer Agent

### Implementation Summary
- Fixed `GetOpenAPISpec::resolveSpecPath()` to use `APP_ROOT` instead of `APP_INSTALL_FOLDER`. The runtime path now resolves to `APP_ROOT/api/openapi.json`, matching the path where `composer build` writes the file.
- Fixed `OpenAPIGenerator` constructor default output path to use `APP_ROOT` for the same reason.
- Added `generate-openapi-spec` and `generate-htaccess` script entries to `composer.json`, wiring the already-existing `ComposerScripts::generateOpenAPISpec()` and `ComposerScripts::generateHtaccess()` methods that were previously unreachable as standalone Composer commands.

### Documentation Updates
- No documentation updates were required because this is a bug fix with no public API or behavioral changes — only the wrong constant was replaced with the correct one.

### Verification Summary
- Tests run: `composer test-filter -- OpenAPI` (218 tests, 492 assertions)
- Static analysis run: none — no new code paths were introduced; the change is a one-line constant substitution in each file.
- Result: PASS — 218 passed, 0 failed, 1 skipped (expected), 2 warnings (pre-existing)

### Code Insights
- [low] (debt) `GetOpenAPISpec.php` / `OpenAPIGenerator.php`: Both files referenced `APP_INSTALL_FOLDER` for application-relative paths. This is a conceptual mismatch: `APP_INSTALL_FOLDER` points to the framework library source tree (`vendor/mistralys/application_framework/src`), never a writable output directory. Any future path that must exist alongside application assets should use `APP_ROOT`. A code review of other constants used for file I/O paths would be a worthwhile follow-up to catch similar misuses.
- [low] (improvement) `composer.json` (scripts): The `generate-openapi-spec` and `generate-htaccess` methods were already implemented in `ComposerScripts` and called from `build`, but had no standalone Composer script entries. Any similar future build sub-steps should have their Composer script entry added at the same time as their implementation to keep the surface discoverable.

### Additional Comments
- The error message already shown to users in `GetOpenAPISpec::collectResponseData()` referenced `composer generate-openapi-spec`. Adding that Composer script makes the message accurate.
- Manual verification (HTTP 200 from `/api/GetOpenAPISpec`) requires a running HCP Editor instance with a generated `api/openapi.json` — this was not possible in the automated test environment but the path logic is confirmed correct by the test suite and constant-definition verification.
