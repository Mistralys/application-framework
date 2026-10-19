## v7.1.0 - OpenAPI Generator & API Cache (Breaking-S)
- API: Added automatic OpenAPI 3.1 spec generation from registered API methods.
- API: New `GetOpenAPISpec` method serves the generated spec as raw JSON.
- API: Added `HtaccessGenerator` for API URL rewriting via `.htaccess`.
- API: Added response schema inference from PHP return arrays.
- API: OpenAPI spec link added to the API methods meta navigation.
- API: Added response schemas to `GetAppCountriesAPI` and `GetAppLocalesAPI`.
- API Cache: Renamed `FixedDurationStrategy` duration constants to underscore format.
- API Cache: Added matching short-duration constants to `FixedDurationStrategy`.
- API Cache: `readFromCache()` now logs corrupt cache files before auto-removing.
- API Cache: Hardened `readFromCache()` error logging against logger failures.
- API Cache: Improved `readFromCache()` PHPDoc for corrupt-cache return case.
- API Cache: Added `@throws` annotation to `invalidateMethod()`.
- AI Cache: Added explicit `filemtime()` false-guard in `FixedDurationStrategy`.
- Deepl: Fixed incorrect `makeError()` call, replaced with `makeDangerous()`.
- Deepl: Removed deprecated language code workaround, now handled upstream.
- Deepl: Fixed double-instantiation in test screen.
- Docs: Added PHPStan baseline regeneration rule to constraints.
- Docs: Documented YAML colon+space constraint for module keyword values.
- Docs: Added "Trait Consumer Policy" to coding constraints.
- Composer: Build now generates `openapi.json` and API `.htaccess`.
- Composer: Fixed build artefacts output path for framework-internal builds.
- Tests: Added 11 test files covering the OpenAPI module.
- Tests: Migrated 19 legacy test files to namespaced `*Test.php` convention.
- Tests: Fixed `HtaccessGeneratorTest` assertions for empty default rewrite base.
- Tests: Fixed `RecordTieInTest` inter-test pollution via `tearDown()` cleanup.
- Tests: Added `live-http` PHPUnit group excluding live-server tests by default.
- Tests: Added `CountryRequestScreen` as `CountryRequestTrait` consumer.
- Dependencies: Bumped up AppUtils Collections to [v1.2.2](https://github.com/Mistralys/application-utils-collections/releases/tag/1.2.2).

### Breaking Changes

`FixedDurationStrategy` duration constants were renamed from compressed format
(`DURATION_1MIN`) to underscore-separated (`DURATION_1_MIN`). Find-and-replace
all consumer references.
