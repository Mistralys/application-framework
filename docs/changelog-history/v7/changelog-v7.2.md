## v7.2.5 - OpenAPI Path & Build Scripts
- OpenAPI: Fixed in-app specification path resolution.
- Composer: Added `generate-openapi-spec` and `generate-htaccess` standalone build scripts.

## v7.2.4 - Connector Cache Hardening
- Connectors: Cache file writing now better handles concurrent read/write operations.

## v7.2.3 - Developer Setup & Seed Rework
- CLI: Added interactive `composer setup` command for local environment configuration.
- CLI: Added developer menu (`menu.sh` / `menu.cmd` / `php tools/menu.php`) for common tasks.
- Seed: Replaced monolithic `ComposerScripts::seedTests()` with process-isolated two-step pipeline.
- Seed: Added locale and country seeding to `TestSuiteBootstrap`.
- Seed: Removed `tests/sql/testsuite.sql` in favor of `docs/sql/pristine.sql`.
- Composer: Swapped `shark/simple_html_dom` for `mistralys/simple_html_dom`.
- PHPStan: Added CLI utility stubs for static analysis.
- CTX: Updated generated documentation.

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
