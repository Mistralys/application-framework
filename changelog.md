# Application Framework Changelog

## v7.2.2 - Test Infrastructure Improvements
- Composer: Added `seed-tests` command to populate the test database with system users.
- Tests: `TestSuiteBootstrap` now validates required DB constants and seeds system users during boot.
- Tests: Added shutdown handler to roll back any open transaction on unexpected process exit.
- Tests: Replaced fatal `die()` with a typed `BootException` when the tests root folder is missing.
- DBHelper: `BaseErrorRenderer` now reports the active database connection, not the boot-time default.
- DBHelper: Fixed empty parameter block being printed for queries with no bound parameters.

## v7.2.1 - Multi-Countries API Trait
- Countries: Added a trait for selecting multiple countries by ID or ISO.

## v7.2.0 - Module Metadata Exporter & Icon Builder
- Composer: Added `ModuleJsonExportGenerator` to export module info as JSON.
- Composer: Added `ModuleInfoParser` for unified module context extraction.
- Composer: Added `ReadmeOverviewParser` to extract brief descriptions from readmes.
- Composer: Extracted glossary generation into `KeywordGlossaryBuilder`.
- Composer: Added `rebuild-icons` command.
- Composer: Added `IconBuilder` to sync PHP and JS icon methods with JSON definitions.
- Icons: Added `IconCollection` and `IconInfo` for programmatic access to icon properties.
- Icons: Fixed misnamed icons in `IconCollection` and `icons.json`.
- Docs: Added CTX documentation for the Icon Builder.

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

## v7.0.13 - Markdown Renderer Docs
- MarkdownRenderer: Added full module documentation and CTX integration.

## v7.0.12 - DeepL Target Language Fix
- DeeplHelper: Fixed deprecated language codes causing exceptions; now resolved to regional locales.
- Tests: Added `DeeplHelperTest` integration test to verify correct target language code resolution.
- Deepl: Fixed an exception in the test screen.
- Dependencies: Updated DeepL XML Translator to [v3.0.1](https://github.com/Mistralys/deepl-xml-translator/releases/tag/3.0.1).

## v7.0.12-dev - User-Scoped API Response Caching
- API: Added user-scoped caching via `UserScopedCacheInterface` and trait.
- API: User scope key injected automatically, preventing silent omission.
- API: Empty user identifier now throws instead of silent fallback.

## v7.0.11-dev - API Response Caching
- API: Added file-based response caching for API methods.
- API: Strategies: `FixedDurationStrategy` (TTL) and `ManualOnlyStrategy`.
- API: Caching opt-in via interface + trait, matching existing patterns.
- API: Cache location registered in admin CacheControl UI via event handler.

## v7.0.11 - Deepl Settings Overrides
- Deepl: Added DB-based app setting overrides for the API key and proxy configuration.
- Deepl: Driver settings now take precedence over boot constants for API key and proxy.
- Deepl: Errors are now much more detailed in the test screen.
- Admin: Fixed broken subnavigation in devel area screens.
- CTX: Added PHPStan scripts guard to agent instructions.
- Dependencies: Updated DeepL XML Translator to [v3.0.0](https://github.com/Mistralys/deepl-xml-translator/releases/tag/3.0.0).

## v7.0.10 - Module Doc Generators
- Docs: Added a generated modules overview.
- Docs: Added a keyword glossary.
- Composer: build now includes documentation generation.
- Composer: Added a message collection system to summarize notices, warnings, and errors during build.
- CTX: Now including all generated docs for agent discovery.
- Dependencies: Added `symfony/yaml`.

## v7.0.9 - Agent Docs
- CTX: Added related modules for all known modules.
- Composer: Added test and analysis scripts.
- Dependencies: Updated AppUtils Core to [v2.5.0](https://github.com/Mistralys/application-utils-core/releases/tag/2.5.0).
- Dependencies: Updated AppUtils to [v3.2.0](https://github.com/Mistralys/application-utils/releases/tag/3.2.0).

## v7.0.8 - Button Fix
- UI: Buttons: Fixed `disable()` not having any effect without specifying a reason.
- UI: Buttons: Fixed `disable()` allowing the button to act as submit when within a form.
- UI: Buttons: Fixed `setTitle()` not working as expected.

## v7.0.7 - UI Agent Docs
- CTX: Fully documented the UI layer.
- UI: Added a test app reference for disabled buttons.

## v7.0.6 - Agent Docs
- CTX: Improved `module-context.yaml` with module metadata.
- CTX: Reviewed and fixed missing module docs (DBHelper, Connectors).

## v7.0.5 - Minor Enhancements
- UI: Added the clientside class `CSSClasses` that mirrors the server-side class.
- UI: Added support for `select2` filterable selects via element classes and FormHelper method.
- Composer: Added the CSS classes file generation to the build process.
- Dependencies: Added `select2/select2` as a dependency for the filterable select feature.

## v7.0.4 - Bug Fix
- Exceptions: Fixed a leftover type error.

## v7.0.3 - Bug Fix
- Exceptions: Fixed the exception page displaying a blank page.
- Exceptions: Added a DEV "Display exception page" button in the error log screen.

## v7.0.2 - Minor Enhancements
- PropertiesGrid: Added null support for all column values.

## v7.0.1 - Bug Fix
- Exceptions: Fixed an error while rendering the exception screen.

## v7.0.0 - Screen Loading and Agent Support (Breaking-XXL)

This major release paves the way for agentic coding and a more
flexible way to structure applications. Admin screens can now
be placed alongside their modules, a sitemap generator with
auto-discovery will register them automatically on build.

### Breaking Changes

Many core classes have been renamed and namespaced. Major adjustments will be necessary to upgrade to this version of
the framework.

### Database update

- The SQL file [2025-12-19-app-sets.sql](/docs/sql/2025-12-19-app-sets.sql) must be imported 
  to create the necessary tables for the updated application sets feature.

### Detailed commit summary

#### PHPStan Support
- Removed PHPStan batch files.
- Added PHPStan Composer commands: `analyze` and `analyze-write`.

#### Admin Screen System
- Added sitemap rendering and developer screen for reviewing the sitemap
- Screen index now stores subscreen classes with getter methods
- Admin screens now support relative folder paths and folder location logging
- Added `RegisterAdminScreenFolders` event listener to register screen folders
- Areas do not have parent screens
- Fixed non-unique screen IDs and added counting for content screens
- Added `getAdminScreensFolder()` and admin URL instances support
- Admin index now uses the `AppFolder` class for media screens
- Updated admin screens for dynamic loading (users, news, media, time tracker, translations)
- Removed obsolete admin screen registry events and legacy screen classes

#### MCP Server & AI Integration
- Added MCP server script and classes for AI integration
- Added CTX (Context as Code) integration with context YAML generation
- Added AI tools for Countries module
- Added HTTP MCP server plan and agent documentation
- Integrated `php-mcp/server` dependency
- Added cache event listener for AI system
- Fixed exception when cache folder not present and `getSize()` throwing exception

#### Events System Refactoring
- Events and listener classes moved thematically
- Added offline event indexing with auto-discovery of event/listener classes
- Events must extend `BaseOfflineEvent`, listeners provide event name
- Added event names where applicable and removed unused event parameters
- Added clearing of event history
- Updated offline event handling in Bootstrap and CacheManager

#### AppSets Feature Enhancement
- Converted to database storage with DBHelper collection structure
- Updated admin screens with improved value display
- Added comprehensive tests and `final` class keywords
- Added `hasFilterSettings()` method
- Fixed value compilation and wrong return values
- Added README documentation

#### Database & DBHelper Improvements
- Added base record settings class with README
- Added PDO primitive type return values
- Added checking parent record functionality
- Fixed WHERE clause with empty primaries
- Moved record data loading to dedicated method
- Added `idExists()` without final flag
- Added `UncachedQuery` attribute for marking uncached queries

#### Code Organization & Structure
- Moved numerous classes into thematically related folders
- Moved `ApplicationException`, `FilterSettingsInterface`, `UI`, `Environments`, `Connectors` classes
- Moved API method classes to dedicated folders
- Added `AppFolder` utility class for path management
- Added `getRootFolder()` and `getRootClassesFolder()` to Driver
- Moved `AppFramework` class file to new location
- Split agent guide into subdocuments

#### UI & User Interface
- Added big selection separators
- Buttons: Added conditional make layout methods
- Added interface for admin URL containers
- Welcome screen refactored with manager class and quick nav event
- Added `adminURL()` and `appConfiguration()` helper methods
- Metanav: Updated read news link

#### Exception Handling & Error Management
- Added static utility methods to Exceptions
- More useful exception rendering
- CLI now shows previous exception output
- Added multiple error codes and constants
- Fixed error check when file does not exist on disk

#### Type Safety & Code Quality
- Added extensive type hints across codebase
- Added PHPDocs throughout
- Added Rector for code quality
- PHPStan fixes and typing enhancements
- Added constant type hints
- Strict typing enabled in multiple classes
- Added return types and fixed return type declarations

#### Dependencies & Build
- Updated dependencies including `Psr\Log` downgrade
- Added framework docs as DEV dependency
- Manually added PHPCAS classmap to Composer
- Fixed tests and added various new test cases
- Added JSON handling details
- Updated project files

#### Media Library
- Upgraded screens to use admin index
- Renamed media library classes
- Added `getAdminScreensFolder()` with listener registration
- Improved image handling in WhatsNew feature

#### Miscellaneous
- FilterSettings: Added type-specific setting methods and `addValue()`, `getGrid()`
- WhatsNew: Improved image handling
- Fixed filter hidden variables
- Fixed and future-proofed paths
- Deepl: Added testing screen and moved helper class
- Countries: Made `collectCountry()` public
- Removed deprecated exception usages and obsolete test cases
- TimeTracker: Fixed collection mixup and updated screens
- Added global folder structure documentation
- Fixed bootstrap path after moving files
- Wording improvements and documentation updates



---
Older changelog entries can be found in the `docs/changelog-history` folder.
