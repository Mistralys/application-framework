## Synthesis

### Completion Status
- Date: 2026-04-29
- Status: COMPLETE
- Completed by: Standalone Developer Agent

### Implementation Summary
- Fixed `TESTS_ROOT` double-define warning in `tests/bootstrap.php` by replacing the compile-time `const` with a `defined()`-guarded `define()` call. This eliminates the `E_WARNING` emitted when `seed-test-db.php` pre-defines the constant.
- Fixed logic inversion bug in `BaseErrorRenderer::analyzeQuery()`: changed `if(!$errors)` to `if($errors)` so the "Placeholders have inconsistencies." note is printed only when inconsistencies are actually detected.
- Added `@param array<string,mixed> $values` PHPDoc to `analyzeQuery()` to resolve PHPStan bare `array` type warning.
- Hardened `TestSuiteBootstrap::configureUsers()` with an early guard that throws `BootException(175002)` if `Application::getSystemUserIDs()` returns an empty array, preventing silent "System users verified." messages when zero users were checked.
- Replaced `die()` in `TestSuiteBootstrap::configurePaths()` with a `BootException(175003)` throw, making the failure structured, consistent with the rest of the bootstrap, and testable.
- Added missing `defined()` guards for `APP_DB_TESTS_NAME`, `APP_DB_TESTS_USER`, `APP_DB_TESTS_PASSWORD`, and `APP_DB_TESTS_HOST` in `TestSuiteBootstrap::configureDatabase()`. If any are absent a `BootException(175004)` is thrown naming the missing constant(s), matching the existing pattern for `APP_DB_TESTS_PORT`.
- Added three new error code constants to `TestSuiteBootstrap`: `ERROR_NO_SYSTEM_USERS_CONFIGURED = 175002`, `ERROR_TESTS_FOLDER_NOT_FOUND = 175003`, `ERROR_TEST_DB_CONSTANTS_MISSING = 175004`.

### Documentation Updates
- No documentation updates were required because all changes are internal implementation fixes with no interface, behaviour, or setup changes visible to callers outside the bootstrap.

### Verification Summary
- Tests run: `composer test-filter -- TestSuiteBootstrap` — returned "No tests executed" (no dedicated test class exists for `TestSuiteBootstrap`; the plan documents this as a known testing gap). This is expected — normal test runs do not trigger the new guards because the test environment is correctly configured.
- Static analysis run: `composer analyze` (PHPStan against full codebase, 2129 files)
- Result: PASS — `[OK] No errors`

### Code Insights
- [low] (debt) `tests/bootstrap.php`: The remaining `const APP_ROOT`, `const APP_INSTALL_FOLDER`, `const APP_VENDOR_PATH`, `const APP_FRAMEWORK_TESTS` declarations are also compile-time `const` at file scope. If `seed-test-db.php` or any other entry point were ever to pre-define these constants, they would produce identical `E_WARNING` messages to the one fixed in this plan. A follow-up pass to convert them all to `defined()`-guarded `define()` calls would eliminate this risk class entirely.
- [low] (convention) ✅ DONE `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php`: The inline comment above `ERROR_TEST_DB_NOT_SEEDED` (`// Thrown when system users are missing...`) is the only one of its kind — the three new constants have no analogous comment. Fixed by adding brief inline comments for each of the three new constants.
- [low] (improvement) ✅ DONE `src/classes/DBHelper/Exception/BaseErrorRenderer.php`: `analyzeQuery()` silently does nothing when `$paramNames` is empty (no `:name` placeholders in the SQL). In that case it still outputs a "Query placeholders:" heading with no entries below it, which could be confusing in the rendered error output. Fixed by adding an early-return that outputs "(none)" when both `$paramNames` and `$values` are empty.

### Additional Comments
- The `analyzeQuery()` inversion fix (Step 2) is the only functional bug in the set; all other changes are hardening or consistency improvements.
- Error codes `175002`–`175004` were confirmed unused before assignment (no grep hits in the codebase).
