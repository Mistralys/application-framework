## v7.3.7 - Countries & Locales Cache API

**Patch for test suite failures caused by cross-test stale collection records.**

- DBHelper: Added `clearRecordCache()` in collections as a soft reset without disposing objects.
- Locales: Added `clearLocaleCache()` to allow resetting the locale cache after a countries collection cache clear.
- API: Added empty-string bypass in regex validation to pass clear-field signals through unmodified.
- Tests: Fixed cross-test isolation failures in the wizard and DBHelper disposer test cases.
- Docs: Added the module context reference guide.

## v7.3.6 - ClearableString Validation Bypass

**Clearing a field via the API no longer gets blocked by its own format validators.** 

- API: Added a validation bypass for the empty-string clear signal in `ClearableStringParameter`.
- AppConfigMode: Added the webserver info section to the settings screen.
- Tests: Fixed foreign-key check state leaking across test cases after transaction rollback.

## v7.3.5 - Global Docs Export
- ModulesOverview: The main `context.yaml` now supports defining global docs to export.
- ModulesOverview: Added support for `projectMetaData.exportDocs`.

## v7.3.4 - Module Export & API Fixes
- API: Fixed required validation treating empty arrays as absent in JSON and list parameters.
- Lookup: Fixed WHERE statement pollution in search queries.
- ModulesOverview: Added `additional-docs` resolution to the JSON export generator.
- Composer: Added CTX file statistics stripping to the build pipeline.
- PropertiesGrid: Added return type declarations to `ifEmpty()` and `addButton()`.
- Docs: Expanded the no-enums constraint with the autoloading failure rationale.
- Docs: Documented the PHPStan memory-limit configuration.
- Skills: Added the `framework-changelog` skill.
- Skills: Added the `framework-release-check` skill.

## v7.3.3 - LookupItem Search API
- LookupItems: Made `addWhere()` public for external constraint injection.
- LookupItems: Added `setLimit()`, `findMatchingIDs()`, and `reset()` fluent methods to `BaseLookupItem`.
- LookupItems: Fixed WHERE clause accumulation bug in `findMatchesBySearch()`.
- Tests: Added `BaseLookupItemTest` covering the new search API (14 tests).

## v7.3.2 - Wizard Preselection API
- Wizard: Added `WizardPreselection` value-object for pre-populating wizard steps before redirect.
- Wizard: Added `WizardConfigurator` to orchestrate a pre-populated wizard session and return the redirect URL.
- Wizard: Added type-safe step value preselection by step class name.
- Wizard: Fixed step defaults not being applied on first visit when preselection values are present.

## v7.3.1 - String Parameter Max Length Validation
- API Parameters: Added `StringParameter::setMaxLength()` for fluent max-length configuration.
- API Parameters: Added a `ClearableStringParameter` to handle setting empty values.

## v7.3.0 - Override Attribute Consistency (Breaking-S)
- API: Added `#[Override]` attributes where relevant.
- API: Fixed typo in `getResponseKeyDescriptions()`.

### Breaking Changes
- The API method `getReponseKeyDescriptions()` has been renamed to `getResponseKeyDescriptions()` to fix a typo. Any callers in applications will have to update their call sites.
