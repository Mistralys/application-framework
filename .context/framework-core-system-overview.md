# Application Framework - Core System Overview
_SOURCE: Framework Documentation_
# Framework Documentation
```
// Structure of documents
└── docs/
    └── agents/
        └── array-handling.md
        └── coding-guidelines.md
        └── coding-patterns.md
        └── exception-usage.md
        └── file-handling.md
        └── folder-structure.md
        └── implementation-history/
            ├── 2026-03-13-api-caching-core-rework-1/
            │   ├── plan.md
            │   ├── synthesis.md
            │   ├── work.md
            │   ├── work/
            │   │   └── WP-001.md
            │   │   └── WP-002.md
            │   │   └── WP-003.md
            │   │   └── WP-004.md
            │   │   └── WP-005.md
            ├── 2026-03-13-api-caching-core/
            │   ├── plan.md
            │   ├── synthesis.md
            │   ├── work.md
            │   ├── work/
            │   │   └── WP-001.md
            │   │   └── WP-002.md
            │   │   └── WP-003.md
            │   │   └── WP-004.md
            │   │   └── WP-005.md
            │   │   └── WP-006.md
            │   │   └── WP-007.md
            ├── 2026-03-13-api-caching-dbhelper-invalidation/
            │   └── plan.md
        └── json-handling.md
        └── plans/
            ├── 2026-02-09-upgrade-helper-work.md
            ├── 2026-02-09-upgrade-helper.md
            ├── 2026-03-13-api-caching-core-rework-1-rework-1/
            │   └── plan.md
            │   └── synthesis.md
            │   └── work.md
            │   └── work/
            │       └── WP-001.md
            │       └── WP-002.md
            │       └── WP-003.md
            │       └── WP-004.md
            │       └── WP-005.md
            │       └── WP-006.md
            │       └── WP-007.md
        └── project-manifest/
            ├── README.md
            ├── constraints.md
            ├── context-documentation.md
            ├── module-glossary.md
            ├── modules-overview.md
            ├── testing.md
        └── projects/
            ├── api-caching-system.md
        └── readme.md
        └── references/
            ├── module-context-reference.md
        └── research/
            ├── 2026-03-13-api-caching-system.md
        └── ui-localization.md
        └── whatsnew-editing.md

```
###  Path: `/docs/agents/array-handling.md`

```md
# Array Handling in the Framework

When working with associative arrays in the framework and applications built
using the framework, it is recommended to use the provided utility 
class `ArrayDataCollection`. It offers type-safety and utility methods.

Example:

```php
use AppUtils\ArrayDataCollection;

$data = ArrayDataCollection::create(array(
    'name' => 'John Doe',
    'integer' => 45,
    'booleanString' => 'true', // can also use `yes` and `no`,
    'float' => 3.14,
));

$name = $data->getString('name');
$age = $data->getInt('integer');
$isActive = $data->getBool('booleanString');
$piValue = $data->getFloat('float');
```

```
###  Path: `/docs/agents/coding-guidelines.md`

```md
# Coding Guidelines

## General Conventions

- **Null checks**: prefer `isset($this->property)` over strict `null` comparisons.
- **Exception Workflow**: 
- **Keep It Left**: minimize nesting by returning early from functions.
- **Avoid long functions**: break down complex logic into smaller private methods.
- **Consistent naming**: snake_case for DB fields; camelCase for PHP properties/methods.
- **Short acronyms like "ID"**: Always keep uppercase, e.g., `getID()`,  `getUserID()`.
- **Array Initialization**: Always define arrays with the verbose `array()` syntax.

```
###  Path: `/docs/agents/coding-patterns.md`

```md
## Coding Patterns

## Checking instance types

Use the `ClassHelper` static methods to check for class types. This will throw
an exception if the type does not match, and guarantees that static analyzers
like PHPStan can infer the correct type after the check.

```php
<?php
use AppUtils\ClassHelper;

$campaign = ClassHelper::requireObjectInstanceOf(
    CampaignRecord::class,
    $record
);
```

## Creating Objects Lazily

Use `isset()` for the property to initialize it as needed.

```php
<?php

class CampaignService 
{
    private ?CampaignManager $campaignManager = null;

    public function getCampaignManager() : CampaignManager 
    {
        if (!isset($this->campaignManager)) {
            $this->object = new CampaignManager();
        }
        
        return $this->campaignManager;
    }
}
```

```
###  Path: `/docs/agents/exception-usage.md`

```md
## Exception Usage

## Exception Code Flow

As a general rule:

- Return success values (including null) as early as possible.
- Throw exceptions at the end of the function.

### Error Code Constants

#### Unique Error Codes Service

All error codes are defined as constants in the relevant exception class.
The integer values are created using a dedicated error code service to ensure
that they are unique across the entire framework and all applications.

The service is available at:

```
https://mistralys.eu/ErrorcodesService?pw={APPLICATION_IDENTIFIER}&counter=appframework-manager
```

Where `{APPLICATION_IDENTIFIER}` is a unique identifier for the application
that has to be requested from Mistralys.

The service returns a single unique integer per request.

> NOTE: The service enforces a rate limit. New codes can be generated after
> a short delay (historically around 1 code per minute).

#### Error Code Format

The code always has the following structure: **Unique number + error number**.

Example: `505001`. 

In this number, `505` is the unique error identifier, and `001` is the error 
number. The error number can be incremented for multiple error codes within 
the same exception class.

The size of the error number portion is always three digits, with leading zeros
if necessary. This gives a maximum of 999 error codes per exception class,
which is sufficient for all practical purposes.

### Exception arguments

Exceptions in the framework all take four parameters:

- user-facing message - Does not contain any technical details.
- developer details - Contains all context needed to debug the issue, ideally with all relevant data included.
- code - Numeric error code for programmatic handling.
- previous exception

The message is designed to be shown to the user, and must not contain and system
information. Developer details are for logging and debugging purposes, and can
contain system information.

Example:

```php
use Application\Exception\ApplicationException;

throw new ApplicationException(
    'User-facing error message.',
    sprintf(
        'Detailed developer message with context: %s',
        $contextInfo
    ),
    1001,
    $previousException
);
```

### Throwing exceptions

In function paths that can fail, return success values first, and throw exceptions
at the end. This keeps the "happy path" less nested and easier to read.

```
###  Path: `/docs/agents/file-handling.md`

```md
# File Handling

## Working with Folders

Use the `FolderInfo` class to interact with folders.

```php
use AppUtils\FileHelper\FolderInfo;

$folder = FolderInfo::factory('/path/to/folder');

// List all files in the folder
foreach($folder->getSubFiles() as $file) {
    echo $file->getName() . PHP_EOL;
}
```

## Working with Files

Use the `FileInfo` class to interact with files.

```php
use AppUtils\FileHelper\FileInfo;

$file = FileInfo::factory('/path/to/file.txt');

// Read the contents of the file
$content = $file->getContents();
````

## Working with JSON files

Use the `JsonFile` class to read and write JSON files.

```php
use AppUtils\FileHelper\JSONFile;

$jsonFile = JSONFile::factory('/path/to/file.json');

// Load the data from the file into an associative array
$data = $jsonFile->getData();

// Save the data back to the file
$data['newKey'] = 'newValue';

$jsonFile->putData($data);
```


```
###  Path: `/docs/agents/folder-structure.md`

```md
# Framework Folder Structure

## Main Folders

- `docs`: This folder contains internal documentation and resource files.
- `localization`: This folder contains the UI for editing localizable UI strings.
- `src`: This folder contains all the PHP source files.
  - `classes`: 
    - `_deprecated`: Deprecated classes are stored here until they are removed. 
    - `Application`: All application-related classes (See [Application Class Folders](#application-class-folders)).
    - `Connectors`: Classes used to connect to external services and APIs.
    - `DBHelper`: Database abstraction and data collection handling classes (See [DBHelper](/src/classes/DBHelper/_readme.md)).
    - `DeeplHelper`: Utility classes to connect to DeepL services.
    - `Examples`: Example classes used in the Framework Test Application.
    - `TypeHinter`: Utility class to run PHP code type hinting adjustments.
    - `UI`: User Interface related management classes and object-oriented UI components.
    - `Utilities`: Global utility classes.
    - `AppFactory.php`: Factory for most framework functions and modules.
    - `Application.php`: Main Application management class.
    - `PackageInfo.php`: Information about this app framework package. 
  - `functions`
      - `functions.php`: Collection of global functions.
      - `code-discovery-constants.php`: Constants used for IDE Intellisense. Not loaded or used in the framework.
  - `localization`: Storage for the localized strings for supported locales.
  - `themes`: UI theming and template classes.
    - `default`: The default theme.
      - `css`: CSS stylesheet files.
      - `fonts`: Font files.
      - `img`: Image files.
      - `js`: JavaScript files and classes.
      - `templates`: PHP-based template files and classes.
      - `icons.json`: List of FontAwesome icons available for applications.
      - `theme.php`: Theme configuration class.
- `tests`: Test suites and which includes the Framework Test Application, built using the framework.
  - `AppFrameworkIntegrationTests`: Integration tests.
  - `AppFrameworkTestClasses`: Abstract test case classes, traits and mock classes.
  - `AppFrameworkTests`: Unit tests.
  - `application`: The test application, a working implementation of the framework.
  - `assets`: Test-related asset files.
  - `files`: Test-related files.
  - `phpstan`: CLI scripts to run the static PHPStan code analysis.
  - `sql`: Source SQL files to initialize the test database.
- `tools`: CLI tools and batch scripts for developer use.

## Application Class Folders

- `Admin`: The admin UI screen system. Management classes and scaffolding for app implementations.
- `Ajax`: The AJAX system. AJAX method management classes for app-internal AJAX requests.
- `AjaxMethods`: Bundled framework AJAX method classes.
- `API`: The API system. Management classes and scaffolding for public request methods.
- `AppFactory`: Support classes for the `AppFactory` class.
- `AppSets`: Enabled features system. Management classes for selecting which features of the application to enable for this installation.
- `AppSettings`: Management classes for application settings stored in the DB.
- `Bootstrap`: Bootstrapper system and entry classes for the application's render / process modes.
- `CacheControl`: Cache storage handling system.
- `Changelog`: Changelog management classes.
- `Collection`: Interfaces and base classes for the record collection system.
- `Composer`: Composer dependency-loading integration classes.
  - `ComposerScripts` — static methods bound to Composer scripts in `composer.json`. `build()` runs the full build sequence (caches, event indexing, admin screen indexing, API method index, CSS classes JS, context date, module documentation). `doUpdateModuleDocumentation()` is public and callable from application `ComposerScripts` subclasses to add documentation generation to an application's own build script — it does not call `init()` so no application bootstrap is required.
  - `BuildMessages` — static registry that collects notices, warnings, and errors during a Composer build run and prints a highlighted summary at the end.
  - `KeywordGlossary/` — classes for the keyword-glossary generator; includes value objects and an offline event system for custom sections:
    - `KeywordGlossaryGenerator` — orchestrates keyword-glossary generation: parses YAML files, fires `DecorateGlossaryEvent` to collect `GlossarySection[]` from listeners, and passes them to `KeywordGlossaryRenderer` to write the output Markdown file. Requires a `symfony/yaml` dependency.
    - `KeywordGlossaryRenderer` — renders the Markdown glossary document from keyword entries and any custom `GlossarySection[]` contributed by offline event listeners.
    - `KeywordParser` — parses `"TERM (context)"` strings into structured entries.
    - `KeywordEntry` — immutable value object for a single parsed keyword with its module associations.
    - `GlossarySection` — immutable value object for a named glossary section with column headers and rows.
    - `GlossarySectionEntry` — immutable value object for a single row in a `GlossarySection`.
    - `Events/` — offline event system allowing application modules to contribute custom sections to the generated glossary:
      - `DecorateGlossaryEvent` — fired after building the keyword table; listeners receive it and call `addSection()` to append `GlossarySection` instances.
      - `BaseDecorateGlossaryListener` — abstract base class for glossary decoration listeners; extend and implement `handleGlossaryDecoration()`.
  - `ModulesOverview/` — discovers `module-context.yaml` files, parses module metadata, and renders the Markdown modules-overview document (written to `docs/agents/project-manifest/modules-overview.md`):
    - `ModulesOverviewGenerator` — orchestrates the full generation workflow: discovers files, parses metadata, renders and writes the output. Requires a `symfony/yaml` dependency.
    - `ModuleContextFileFinder` — discovers `module-context.yaml` files by following `context.yaml` import chains.
    - `ModuleInfo` — immutable value object for parsed module metadata (label, keywords, related modules, Composer package name).
    - `ModulesOverviewRenderer` — renders the Markdown overview document from a list of `ModuleInfo` objects.
- `ConfigSettings`: Configuration constant access and management classes.
- `CORS`: Management classes for CORS headers.
- `Countries`: Country collection classes.
- `CustomProperties`: Management classes to add custom property support to records.
- `DBDumps`: DB dump generation classes.
- `DeploymentRegistry`: Classes to handle the registry for application deployments.
- `Development`: Management classes for developer-only functionality.
- `Disposables`: The disposable system. Management classes and scaffolding for implementations.
- `Driver`: Application driver management classes (driver = application entry point).
- `Environments`: The environment handling system. Detection logic and scaffolding.
- `ErrorDetails`: Error parser and rendering for displaying app exception details.
- `ErrorLog`: Error log management classes.
- `EventHandler`: Event handling system classes.
- `Exception`: Framework exception classes.
- `Feedback`: UI user feedback collection and management classes.
- `FilterCriteria`: Record filtering system. Management classes and scaffolding for implementations.
- `FilterSettings`: Companion to the filtering system: Management and form generation for user-selected filtering settings.
- `Formable`: Form generation and management classes, built around HTML_QuickForm2.
- `Framework`: Framework-related utility and information classes.
- `HealthMonitor`: Management classes for the app health monitoring feature and scaffolding for implementations.
- `Installer`: (WIP) application installer feature.
- `Interfaces`: General-purpose interfaces.
- `Languages`: Language collection with available languages inferred from available countries.
- `LDAP`: LDAP connectivity management classes.
- `Locales`: Locales collection with available locales inferred from available countries.
- `Localization`: UI localization support classes.
- `LockManager`: Admin screen locking system management classes.
- `Logger`: Logging classes.
- `LookupItems`: Management classes for the UI's "Quick Lookup" feature to find records by ID or name.
- `Mail`: Classes used for system emails.
- `Maintenance`: The maintenance mode management classes.
- `MarkdownRenderer`: Framework-flavored Markdown document rendering based on the `league/commonmark` library.
- `Media`: Media library management classes.
- `Messagelogs`: Application DB message log management classes.
- `Messaging`: Management classes for user to user messages (via the LockManager system).
- `NewsCentral`: The news articles system for application news and detailed release notes.
- `OAuth`: OAuth implementation classes.
- `Ratings`: Management classes for user-provided ratings of admin screens.
- `Renamer`: Classes for the "DB Renamer" tool used to rename strings in supported database tables.
- `Request`: HTTP Request-handling classes.
- `RequestLog`: The request logging system.
- `Revisionable`: Scaffolding and collection classes for records that support versioning.
- `Session`: Session management classes.
- `SourceFolders`: Registry classes for folders from which classes can be loaded dynamically.
- `StateHandler`: Record state handling classes (draft, finalized, inactive...).
- `Stubs`: General-purpose stub classes, mainly used for static code analysis.
- `SystemMails`: Classes that handle known framework system emails.
- `Tags`: The tagging system. Management and collection classes and scaffolding for tag handling.
- `TimeTracker`: The time tracking module for people to store time worked on projects.
- `Traits`: General-purpose traits.
- `Updaters`: Management classes for application update scripts (via CLI and HTTP).
- `Uploads`: Companion management to the `Media` classes to handle new uploaded documents.
- `User`: Authenticated user management classes.
- `Users`: Known users collection and management classes.
- `Validation`: Utility classes for validating records and data types.
- `WhatsNew`: Classes to manage the integrated `What's new?` dialog for new application versions.

```
###  Path: `/docs/agents/implementation-history/2026-03-13-api-caching-core-rework-1/plan.md`

```md
# Plan — API Caching Core: Post-Phase-1 Hardening & Alignment

## Summary

This plan addresses the strategic recommendations from the Phase 1 synthesis report (`2026-03-13-api-caching-core/synthesis.md`). It covers three areas: **(A)** enforcing cache user isolation at the type system level so implementors of user-scoped API methods cannot accidentally serve cross-user cached data, **(B)** defensive hardening of `APICacheManager` and `CacheableAPIMethodTrait` against edge cases, and **(C)** code quality alignment between the API cache and AI cache strategy systems (strategy IDs, duration constants, PHPDoc, PHPStan annotations).

## Architectural Context

### API Cache Module

- **Location:** `src/classes/Application/API/Cache/`
- **Key files:**
  - `APICacheStrategyInterface.php` — strategy contract (`getID()`, `isCacheFileValid()`)
  - `Strategies/FixedDurationStrategy.php` — TTL-based strategy with 7 duration constants
  - `Strategies/ManualOnlyStrategy.php` — manual-invalidation-only strategy
  - `APICacheManager.php` — static cache folder manager
  - `CacheableAPIMethodInterface.php` — opt-in caching contract for API methods
  - `CacheableAPIMethodTrait.php` — cache-aside implementation (cache key, read, write, invalidate)
  - `APIResponseCacheLocation.php` — CacheControl admin UI integration
- **Integration point:** `BaseAPIMethod::_process()` (lines ~153–174) performs two `instanceof CacheableAPIMethodInterface` checks for transparent cache read/write.

### Extensibility Pattern

The caching system follows the `APIKeyMethodInterface` / `APIKeyMethodTrait` composition pattern:
- Interface extends `APIMethodInterface` to declare capability
- Trait provides default implementations
- `BaseAPIMethod` detects the interface via `instanceof` and acts accordingly
- `initReservedParams()` auto-registers parameters for detected interfaces

### AI Cache Module (Alignment Target)

- **Location:** `src/classes/Application/AI/Cache/`
- **Strategy interface:** `AICacheStrategyInterface` (extends `StringPrimaryRecordInterface`)
- **Strategies:** `FixedDurationStrategy` (4 duration constants, `STRATEGY_ID = 'fixed_duration'`), `UncachedStrategy` (`STRATEGY_ID = 'uncached'`)
- **Base class:** `BaseAICacheStrategy` (abstract, provides file management)

### User Authentication in API Methods

- API methods run outside normal session context (`APIBootstrap` calls `disableAuthentication()`)
- User identification happens via API keys (`APIKeyMethodInterface`), not sessions
- `collectRequestData()` is **bypassed entirely on cache hits** — this is the security-critical gap

## Approach / Architecture

### A. User Isolation Enforcement (Security — High Priority)

Introduce a dedicated `UserScopedCacheInterface` / `UserScopedCacheTrait` pair that makes it **structurally impossible** for user-scoped methods to omit user-identifying data from cache keys.

**Design:**

1. A new `UserScopedCacheInterface` extends `CacheableAPIMethodInterface` and adds a single method: `getUserCacheIdentifier(): string`. This method must return a user-identifying value (e.g., pseudo user ID from API key, or an application-defined user scope token).

2. A new `UserScopedCacheTrait` (which uses `CacheableAPIMethodTrait`) overrides `getCacheKeyParameters()` to **automatically inject** the user identifier into the cache key parameters before hashing. This means:
   - The implementing method defines domain-specific params via `getUserScopedCacheKeyParameters()` (renamed from `getCacheKeyParameters()` in user-scoped context).
   - The trait's `getCacheKeyParameters()` merges the user identifier with the domain params.
   - There is no way to "forget" the user identifier — the trait enforces it.

3. `BaseAPIMethod::_process()` needs **no changes** — the `instanceof CacheableAPIMethodInterface` check already covers the subinterface.

**Result:** Two-tier system:
- `CacheableAPIMethodInterface` + `CacheableAPIMethodTrait` — for stateless, non-user-scoped methods (e.g., `GetAppLocalesAPI`)
- `UserScopedCacheInterface` + `UserScopedCacheTrait` — for methods returning user-specific data (enforces user isolation by construction)

### B. Defensive Hardening (Medium Priority)

1. **`APICacheManager::getMethodCacheFolder()` input validation** — add a precondition guard rejecting empty strings and strings containing directory separators. The `invalidateMethod('') === clearAll()` scenario is the most dangerous.

2. **`CacheableAPIMethodTrait::readFromCache()` corrupt-cache resilience** — wrap `JSONFile::parse()` in a try-catch. On parse failure, delete the corrupt file and return `null` (transparent fallback to fresh computation).

### C. Code Quality & AI Cache Alignment (Low Priority)

1. **Add `STRATEGY_ID` constants** to `FixedDurationStrategy` and `ManualOnlyStrategy` using **PascalCase** (project norm for strategy IDs), e.g., `'FixedDuration'`, `'ManualOnly'`.
2. **Change `serialize()` to `json_encode()`** in `CacheableAPIMethodTrait::getCacheKey()`.
3. **Add explicit `filemtime() === false` guard** in `FixedDurationStrategy::isCacheFileValid()`.
4. **Add `@phpstan-require-implements CacheableAPIMethodInterface`** to `CacheableAPIMethodTrait`.
5. **Add `@phpstan-require-implements UserScopedCacheInterface`** to the new `UserScopedCacheTrait`.
6. **Add class-level PHPDoc** (`@package`, `@subpackage`) to `FixedDurationStrategy` and `ManualOnlyStrategy`.
7. **Fix `@package` annotation** on `RegisterAPIResponseCacheListener` to align with sibling listeners.
8. **Align duration constants** — add `DURATION_1MIN`, `DURATION_5MIN`, `DURATION_15MIN` to AI cache's `FixedDurationStrategy` for parity, or document the intentional divergence.

## Rationale

- **Interface + trait enforcement vs. docblock warning:** A docblock warning is advisory — developers may not read it, or may read it and still forget. A dedicated interface with a trait that automatically injects user identifiers into the cache key makes the security constraint structural. This follows the `APIKeyMethodInterface` pattern already established in the framework.
- **Subinterface rather than modifying `CacheableAPIMethodInterface`:** Not all cacheable methods are user-scoped. `GetAppLocalesAPI` and `GetAppCountriesAPI` are stateless — forcing them to provide a user identifier would be wrong. The two-tier approach cleanly separates concerns.
- **PascalCase for strategy IDs:** Aligns with the codebase's `PascalCase` convention for API method names and constant identifiers. The AI cache's `snake_case` strategy IDs should also be migrated to PascalCase for consistency.
- **`json_encode()` over `serialize()`:** More portable, avoids `__sleep()` edge cases with complex objects, and makes the intent (scalar-only params) explicit.
- **Corrupt-cache resilience:** A transparent cache layer must never turn a working API method into a broken one because of a stale file. Silent fallback to fresh computation is the only acceptable behavior.

## Detailed Steps

### Step 1 — UserScopedCacheInterface

Create `src/classes/Application/API/Cache/UserScopedCacheInterface.php`:

```php
interface UserScopedCacheInterface extends CacheableAPIMethodInterface
{
    /**
     * Returns a unique identifier for the current user context.
     * This value is automatically injected into the cache key
     * by {@see UserScopedCacheTrait} to ensure per-user cache
     * isolation.
     *
     * @return string A non-empty user-identifying value
     */
    public function getUserCacheIdentifier() : string;

    /**
     * Returns the method-specific cache key parameters,
     * excluding user identification (which is handled
     * automatically by the trait).
     *
     * @return array<string,mixed>
     */
    public function getUserScopedCacheKeyParameters() : array;
}
```

### Step 2 — APICacheException

Create `src/classes/Application/API/Cache/APICacheException.php`:

- Extend `Application\API\APIException` (inherits the framework 4-parameter exception signature).
- Define error code constants for all cache-specific error conditions (e.g., `ERROR_EMPTY_USER_CACHE_IDENTIFIER`, `ERROR_INVALID_METHOD_NAME`, `ERROR_CACHE_FILE_CORRUPT`).

### Step 3 — UserScopedCacheTrait

Create `src/classes/Application/API/Cache/UserScopedCacheTrait.php`:

- Use `CacheableAPIMethodTrait` internally.
- Override `getCacheKeyParameters()` to:
  1. Call `$this->getUserCacheIdentifier()`.
  2. **Hard fail** if the return value is empty (`throw new APICacheException(...)`) — a user-scoped method must always provide a user identifier; silent fallback is not acceptable.
  3. Merge `'_userScope' => $identifier` with the result of `getUserScopedCacheKeyParameters()`.
- Add `@phpstan-require-implements UserScopedCacheInterface`.

### Step 4 — APICacheManager::getMethodCacheFolder() Input Validation

In `APICacheManager::getMethodCacheFolder()`:
- Add precondition: throw `APICacheException` if `$methodName` is empty or contains `DIRECTORY_SEPARATOR` or `/` or `..`.
- This prevents the `invalidateMethod('') === clearAll()` scenario and blocks path traversal.

### Step 5 — readFromCache() Corrupt-Cache Resilience

In `CacheableAPIMethodTrait::readFromCache()`:
- Wrap `$cacheFile->parse()` in a try-catch for `\Throwable`.
- On exception: delete the corrupt file (best-effort, ignore deletion failures) and return `null`.

### Step 6 — serialize() → json_encode() in getCacheKey()

In `CacheableAPIMethodTrait::getCacheKey()`:
- Replace `serialize($params)` with `json_encode($params, JSON_THROW_ON_ERROR)`.
- The `JSON_THROW_ON_ERROR` flag ensures non-encodable values fail fast rather than producing silent `false`.

### Step 7 — Add STRATEGY_ID Constants (PascalCase)

- `FixedDurationStrategy`: add `public const string STRATEGY_ID = 'FixedDuration';` and update `getID()` to return `self::STRATEGY_ID`.
- `ManualOnlyStrategy`: add `public const string STRATEGY_ID = 'ManualOnly';` and update `getID()` to return `self::STRATEGY_ID`.

### Step 8 — filemtime() Explicit Guard in FixedDurationStrategy

In `FixedDurationStrategy::isCacheFileValid()`:
- Explicitly check `filemtime()` return value. If `false`, return `false` (expired). This prevents arithmetic on `false` and satisfies strict PHPStan levels.

### Step 9 — @phpstan-require-implements on CacheableAPIMethodTrait

Add `@phpstan-require-implements CacheableAPIMethodInterface` to the class-level docblock of `CacheableAPIMethodTrait`.

### Step 10 — PHPDoc Alignment

- Add `@package API` and `@subpackage Cache` class-level docblocks to `FixedDurationStrategy` and `ManualOnlyStrategy`.
- Fix `@package` annotation on `RegisterAPIResponseCacheListener` to match sibling `RegisterAPIIndexCacheListener`.

### Step 11 — AI Cache Strategy ID Migration to PascalCase

In `src/classes/Application/AI/Cache/Strategies/`:
- `FixedDurationStrategy`: change `STRATEGY_ID` from `'fixed_duration'` to `'FixedDuration'`.
- `UncachedStrategy`: change `STRATEGY_ID` from `'uncached'` to `'Uncached'`.
- Search for any code referencing the old string values and update.

### Step 12 — Update Module Documentation

- Update `src/classes/Application/API/Cache/README.md` to document:
  - The two-tier caching pattern (stateless vs. user-scoped)
  - `UserScopedCacheInterface` / `UserScopedCacheTrait` usage
  - Strategy ID constants
- Update `src/classes/Application/API/Cache/module-context.yaml` to include the new files.

### Step 13 — Tests

- **Unit tests for UserScopedCacheTrait:** Verify that `getCacheKeyParameters()` always includes the user identifier. Verify different user identifiers produce different cache keys. Verify that an empty user identifier throws `APICacheException` (hard failure — user-scoped methods must always provide a user identifier).
- **Unit test for APICacheManager input validation:** Verify that empty string, strings with `/`, `..`, or `DIRECTORY_SEPARATOR` throw `APICacheException`.
- **Unit test for corrupt-cache resilience:** Create a file with invalid JSON, verify `readFromCache()` returns `null` and removes the file.
- **Unit test for `json_encode` cache key:** Verify deterministic hashing with the new serialization.
- **Regression:** Re-run existing 19 tests to confirm no breakage.

### Step 14 — Run composer dump-autoload and Build

- Run `composer dump-autoload` (new class files added).
- Run `composer build` to regenerate CTX documentation.

## Dependencies

- Phase 1 implementation must be complete and merged (it is — per synthesis).
- No external dependencies.

## Required Components

### New Files
- `src/classes/Application/API/Cache/APICacheException.php`
- `src/classes/Application/API/Cache/UserScopedCacheInterface.php`
- `src/classes/Application/API/Cache/UserScopedCacheTrait.php`

### Modified Files
- `src/classes/Application/API/Cache/APICacheManager.php` (input validation)
- `src/classes/Application/API/Cache/CacheableAPIMethodTrait.php` (corrupt-cache resilience, json_encode, @phpstan-require-implements)
- `src/classes/Application/API/Cache/Strategies/FixedDurationStrategy.php` (STRATEGY_ID, filemtime guard, PHPDoc)
- `src/classes/Application/API/Cache/Strategies/ManualOnlyStrategy.php` (STRATEGY_ID, PHPDoc)
- `src/classes/Application/API/Events/RegisterAPIResponseCacheListener.php` (@package fix)
- `src/classes/Application/AI/Cache/Strategies/FixedDurationStrategy.php` (STRATEGY_ID PascalCase)
- `src/classes/Application/AI/Cache/Strategies/UncachedStrategy.php` (STRATEGY_ID PascalCase)
- `src/classes/Application/API/Cache/README.md` (documentation update)
- `src/classes/Application/API/Cache/module-context.yaml` (add new files)

### New Test Files
- `tests/AppFrameworkTests/API/Cache/UserScopedCacheTest.php`
- `tests/AppFrameworkTests/API/Cache/APICacheManagerValidationTest.php`
- `tests/AppFrameworkTests/API/Cache/CorruptCacheResilienceTest.php`

### Modified Test Files
- `tests/AppFrameworkTests/API/Cache/APICacheStrategyTest.php` (strategy ID constant tests, json_encode cache key test)

## Assumptions

- The Phase 1 code is on a feature branch that has not yet been merged to main. All changes in this plan are additive to that branch.
- API methods that are user-scoped always have access to a user-identifying value (e.g., via `APIKeyMethodInterface` or application-specific context).
- No existing code references the AI cache strategy ID strings `'fixed_duration'` or `'uncached'` as stored/persisted values. If they are persisted in cache files, the migration is a no-op (cache files can be cleared).

## Constraints

- Always use `array()` syntax, never `[]`.
- Follow existing `@package` / `@subpackage` annotation conventions.
- No enums, no readonly properties (PHP 8.4+ project rules).
- New class files require `composer dump-autoload` (classmap autoloading).
- Never run the full test suite — scope tests to the API Cache module.

## Out of Scope

- `DBHelperAwareStrategy` (Phase 2 — separate plan exists at `2026-03-13-api-caching-dbhelper-invalidation/`).
- The 2 pre-existing `HtaccessGeneratorTest` failures (unrelated, `feature-openapi-specs` branch).
- The 7 pre-existing PHPStan errors (unrelated files).
- `getID()` test for strategies — covered implicitly by adding `STRATEGY_ID` constants and testing them.
- Duration constant alignment between API and AI cache (documenting the divergence is sufficient — AI cache may intentionally use fewer granularities).

## Acceptance Criteria

1. A `UserScopedCacheInterface` exists that extends `CacheableAPIMethodInterface` and requires `getUserCacheIdentifier(): string`.
2. A `UserScopedCacheTrait` exists that automatically injects the user identifier into cache key parameters — no way to bypass.
3. `APICacheManager::getMethodCacheFolder()` throws `APICacheException` for empty or path-traversal input.
4. `readFromCache()` returns `null` and deletes corrupt cache files instead of propagating exceptions.
5. Cache keys use `json_encode()` instead of `serialize()`.
6. Both API cache strategy classes have `STRATEGY_ID` constants in PascalCase.
7. Both AI cache strategy classes have `STRATEGY_ID` constants migrated to PascalCase.
8. `FixedDurationStrategy::isCacheFileValid()` explicitly handles `filemtime() === false`.
9. `@phpstan-require-implements` is present on `CacheableAPIMethodTrait` and `UserScopedCacheTrait`.
10. PHPDoc is aligned across all cache strategy and listener classes.
11. All existing 19 tests pass (regression).
12. New tests cover user-scoped cache isolation, input validation, and corrupt-cache resilience.
13. `README.md` documents the two-tier caching pattern.
14. CTX documentation regenerated via `composer build`.

## Testing Strategy

- **Unit tests** for the new `UserScopedCacheTrait`: verify user identifier injection, verify different users → different cache keys, verify the trait throws `APICacheException` on empty user identifier (hard failure).
- **Unit tests** for `APICacheManager` input validation: verify rejection of empty strings, path separators, `..` sequences (all throw `APICacheException`).
- **Unit tests** for corrupt-cache resilience: create invalid JSON files, verify transparent fallback.
- **Unit tests** for strategy ID constants: verify `getID()` returns the constant value.
- **Regression**: run existing `APICacheStrategyTest` and `APICacheIntegrationTest` to verify no breakage.
- **Scope**: `composer test-filter -- APICache` should cover all relevant tests.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **AI cache strategy ID rename breaks persisted references** | AI cache files are ephemeral filesystem caches — clearing them (`composer clear-caches`) is safe and expected. Search for string references before renaming. |
| **`json_encode()` produces different hash than `serialize()`** | This is intentional — existing cache entries become stale misses and are replaced on next request. No data loss. |
| **`UserScopedCacheTrait` complexity confuses implementors** | Clear README documentation with usage examples showing both tiers. The interface name itself communicates intent. |
| **`filemtime()` guard changes behavior on race condition** | Previous behavior was also "treat as expired" — the guard makes the same behavior explicit and PHPStan-clean. |

```
###  Path: `/docs/agents/implementation-history/2026-03-13-api-caching-core-rework-1/synthesis.md`

```md
# Synthesis Report — API Caching Core: Post-Phase-1 Hardening & Alignment

**Plan:** `2026-03-13-api-caching-core-rework-1`  
**Synthesized:** 2026-03-17  
**Status:** COMPLETE (5/5 WPs)  
**Pipeline Health:** All 5 WPs passed all four pipeline stages (implementation → QA → code-review → documentation).

---

## Executive Summary

This project delivered three high-value improvements to the Application Framework's API caching subsystem, following the strategic recommendations of the Phase 1 synthesis report.

**Type-system user isolation (WP-001, WP-002):** A new two-tier caching architecture was introduced. `UserScopedCacheInterface` and `UserScopedCacheTrait` now compel any user-scoped API method to declare a user identity, which the trait automatically injects as the `_userScope` cache key parameter. It is structurally impossible for implementors to omit user identity and accidentally serve cross-user cached data. `APICacheException` was introduced as the dedicated exception class for cache infrastructure misuse, with three typed error constants.

**Defensive hardening (WP-001, WP-003):** `APICacheManager::getMethodCacheFolder()` now blocks path traversal attempts (empty string, `/`, `..`, `DIRECTORY_SEPARATOR`). `CacheableAPIMethodTrait::readFromCache()` silently recovers from corrupt cache files (delete and return null) instead of propagating a parse exception. `getCacheKey()` switched from `serialize()` to `json_encode(JSON_THROW_ON_ERROR)` for deterministic, injection-safe hashing. `FixedDurationStrategy::isCacheFileValid()` gained an explicit `filemtime() === false` guard replacing implicit type-coercion arithmetic.

**Code quality and test coverage (WP-003, WP-004, WP-005):** Both API cache strategies gained `STRATEGY_ID` typed constants. AI cache strategy IDs were migrated from snake_case to PascalCase. Twenty-one new unit tests across three new test files now cover all hardened behaviours. The full build pipeline (`composer build`) was verified and `.context/` documentation regenerated.

---

## Deliverables

### New Source Files

| File | Purpose |
|---|---|
| `src/classes/Application/API/Cache/APICacheException.php` | Typed exception for cache infrastructure misuse |
| `src/classes/Application/API/Cache/UserScopedCacheInterface.php` | Contract for user-scoped API caching |
| `src/classes/Application/API/Cache/UserScopedCacheTrait.php` | Trait implementing `_userScope` key injection |

### Modified Source Files

| File | Change Summary |
|---|---|
| `src/classes/Application/API/Cache/APICacheManager.php` | Path-traversal guard in `getMethodCacheFolder()` |
| `src/classes/Application/API/Cache/CacheableAPIMethodTrait.php` | Corrupt-cache resilience, `json_encode` key |
| `src/classes/Application/API/Cache/Strategies/FixedDurationStrategy.php` | `STRATEGY_ID` constant, `filemtime()` false guard |
| `src/classes/Application/API/Cache/Strategies/ManualOnlyStrategy.php` | `STRATEGY_ID` constant, PHPDoc |
| `src/classes/Application/AI/Cache/Strategies/FixedDurationStrategy.php` | `STRATEGY_ID` PascalCase migration |
| `src/classes/Application/AI/Cache/Strategies/UncachedStrategy.php` | `STRATEGY_ID` PascalCase migration |
| `src/classes/Application/API/Events/RegisterAPIResponseCacheListener.php` | `@package/@subpackage` PHPDoc alignment |
| `phpstan.neon` | Removed `trait.unused` suppression for `UserScopedCacheTrait.php` |

### New Test Files

| File | Tests | Assertions |
|---|---|---|
| `tests/AppFrameworkTests/API/Cache/UserScopedCacheTest.php` | 9 | 17 |
| `tests/AppFrameworkTests/API/Cache/APICacheManagerValidationTest.php` | 7 | 11 |
| `tests/AppFrameworkTests/API/Cache/CacheResilienceTest.php` | 5 | 6 |

### Documentation Files

| File | Change Summary |
|---|---|
| `src/classes/Application/API/Cache/README.md` | Full rewrite: two-tier pattern docs, usage guide, APICacheException section, Classes table update |
| `src/classes/Application/API/Cache/module-context.yaml` | New files, keywords, corrected YAML syntax, updated description field |
| `changelog.md` | v7.0.12 entry documenting user-scoped caching two-tier design |
| `docs/agents/project-manifest/testing.md` | Test count updated: ~141 → ~155 |
| `AGENTS.md` (project root) | Test count updated: ~141 → ~155 |
| `.context/modules/api-cache/` | Regenerated: overview (271 lines), architecture-core (596 lines), strategies (104 lines) |

---

## Metrics

| Metric | Value |
|---|---|
| Total tests after project | 40 (API/Cache suite) |
| New tests added | 21 |
| Tests failed | 0 |
| PHPStan errors (project-wide) | 7 (all pre-existing, none in project scope) |
| Security issues | 0 |
| WP-002 rework cycles | 2 (implementation + QA + code-review each ran 3 times) |
| Build status | PASS |

---

## Rework Analysis: WP-002

WP-002 required two full rework cycles (6 additional pipeline runs). Both bugs were missed by PHPStan on initial implementation because the `trait.unused` suppression in `phpstan.neon` **disables static analysis of the entire trait method body** when no consumer class exists yet. This was not an agent error — it is a structural blind spot inherent to the suppression pattern.

**Rework #1 — array_merge() key-collision (code-review FAIL)**  
`array_merge()` gives precedence to the right operand for duplicate string keys. A consumer's `getUserScopedCacheKeyParameters()` could silently overwrite the `_userScope` injection. Fixed by switching to the PHP array union operator (`+`), which gives precedence to the left operand.

**Rework #2 — APICacheException constructor argument order (code-review FAIL)**  
The `int` error code constant was passed as arg #2 (`string $developerInfo`) instead of arg #3 (`int $code`). With `declare(strict_types=1)` active in the file, PHP 8.4 would throw a `TypeError` instead of `APICacheException` at runtime. Fixed by using the correct 3-argument form, consistent with `APICacheManager.php`.

**Root cause:** The `trait.unused` PHPStan suppression acts as a complete analysis blackout for trait method bodies until a consumer class exists. Both bugs are of classes that PHPStan would normally catch immediately.

---

## Strategic Recommendations (Gold Nuggets)

### 1. `trait.unused` PHPStan suppression is a full static-analysis blackout
**Priority: High**  
When a trait is suppressed via `phpstan.neon` `trait.unused`, PHPStan **stops analyzing the trait's method bodies entirely** — not just the "trait is unused" notice. Any type errors, wrong argument positions, or logic bugs in the trait's methods become invisible to static analysis until the trait gains a consumer. In WP-002, two independent bugs slipped through for exactly this reason.

**Recommendation:** Define a policy for library traits that are delivered before their consumers. Options:
- Write a minimal anonymous-class fixture stub that implements the consumer interface and uses the trait — this provides PHPStan a consumer to analyze through, even before real consumers exist.
- Add a dedicated test file (`UserScopedCacheTraitTest.php` style) that exercises the trait methods via a fixture class, providing both PHPStan coverage and regression protection simultaneously.
- Never add `trait.unused` suppression without a companion test file. The suppression entry in `phpstan.neon` should include a comment referencing the test file that compensates for the analysis gap.

### 2. `ERROR_CACHE_FILE_CORRUPT` is dead code — no operator observability
**Priority: Medium**  
`APICacheException::ERROR_CACHE_FILE_CORRUPT` (59213011) was defined as "reserved for logging context" but is never referenced in any production code. When `readFromCache()` encounters a corrupt file, it silently deletes the file and returns `null`. There is no log entry, no metric increment, and no way for an operator to detect systematic cache corruption at scale.

**Recommendation:** In a follow-up WP, wire `ERROR_CACHE_FILE_CORRUPT` into a framework logger call at `WARNING` level inside the corrupt-cache catch block in `CacheableAPIMethodTrait::readFromCache()`. This provides operator observability with minimal code change.

### 3. AI `FixedDurationStrategy` retains implicit `filemtime()` arithmetic
**Priority: Medium**  
`Application\AI\Cache\Strategies\FixedDurationStrategy::isCacheFileValid()` still uses the pattern `time() - filemtime($cacheFile->getPath()) < $durationInSeconds`. If `filemtime()` returns `false`, PHP coerces `false` to `0`, making the subtraction approximately `1.7 billion` — the file is treated as expired. This is safe (a stale cache miss rather than serving corrupt data), but it relies on implicit type coercion rather than an explicit guard. The API counterpart was hardened in WP-003.

**Recommendation:** Apply the same explicit guard to the AI counterpart in the next quality pass WP: `if ($mtime === false) { return false; }`.

### 4. YAML keyword values must not contain bare colon+space sequences
**Priority: Medium**  
`module-context.yaml` keyword entries that include PHP method signatures (e.g. `CacheableAPIMethodTrait: ...`) are parsed by Symfony YAML as mapping keys rather than string scalars, causing `ModulesOverviewGenerator::buildModuleInfo()` to receive arrays and throw `Array to string conversion`. This was silently present in the documentation output until the `composer build` run in WP-005.

**Recommendation:** Document this constraint in the CTX Generator / module-context guide (`docs/agents/references/`). Keyword values that describe method signatures should either quote the entry or avoid the `word: ` pattern. The CTX Generator documentation should explicitly warn contributors about Symfony YAML's colon+space parsing behaviour.

### 5. `APICacheManager::invalidateMethod()` is missing a `@throws` annotation
**Priority: Low**  
`invalidateMethod()` propagates `APICacheException` from `getMethodCacheFolder()` but has no `@throws` annotation in its docblock. In practice this cannot fire through legitimate framework-internal callers (since `getMethodName()` always returns clean values), but the API contract is incomplete for direct callers.

**Recommendation:** Add `@throws APICacheException` to `invalidateMethod()`'s docblock in a future cleanup pass.

### 6. Consider a project-wide policy for `trait.unused` PHPStan suppressions
**Priority: Low**  
The current `phpstan.neon` has a narrow-scoped `trait.unused` suppression for `UserScopedCacheTrait.php` that was removed in WP-004. However, `CountryRequestTrait` and other library traits may have similar suppressions without compensating test coverage. A project-wide audit and a formal policy (suppress only with a named companion test file) would prevent this pattern from recurring.

---

## Next Steps for Planner/Manager

| Priority | Action |
|---|---|
| High | Define and document the `trait.unused` suppression policy — companion test file requirement |
| Medium | Add logger call for `ERROR_CACHE_FILE_CORRUPT` in `CacheableAPIMethodTrait::readFromCache()` |
| Medium | Apply explicit `filemtime() === false` guard to `AI\Cache\Strategies\FixedDurationStrategy` |
| Medium | Audit all `trait.unused` suppressions in `phpstan.neon` for compensating test coverage |
| Low | Add `@throws APICacheException` to `APICacheManager::invalidateMethod()` docblock |
| Low | Address minor README/table gaps identified by code review (APICacheException in Classes table — already fixed in WP-005; `@package/@subpackage` on AI cache strategy classes) |
| Future | Add whitespace-only identifier guard to `UserScopedCacheTrait` (trim + empty check) if user identifiers ever originate from untrusted / external input |

---

## Pipeline Summary by Work Package

| WP | Title | Impl | QA | Review | Docs | Reworks |
|---|---|---|---|---|---|---|
| WP-001 | APICacheException + Core Hardening | PASS | PASS | PASS | PASS | 0 |
| WP-002 | UserScopedCache Two-Tier Design | PASS (×3) | PASS (×3) | PASS (at 3rd) | PASS | 2 |
| WP-003 | Strategy Code Quality & Alignment | PASS | PASS | PASS | PASS | 0 |
| WP-004 | Unit Test Coverage | PASS | PASS | PASS | PASS | 0 |
| WP-005 | Documentation & Build Finalization | PASS | PASS | PASS | PASS | 0 |

```
###  Path: `/docs/agents/implementation-history/2026-03-13-api-caching-core-rework-1/work.md`

```md
# Work Packages — API Caching Core: Post-Phase-1 Hardening & Alignment

**Plan:** [plan.md](plan.md)  
**Project:** Application Framework  
**Date:** 2026-03-17

---

## Overview

This plan is decomposed into 5 work packages covering three priority areas:

- **High priority (Security):** User isolation type system (WP-001 → WP-002)
- **Medium priority (Defensive):** Exception class, input validation, cache resilience (WP-001)
- **Low priority (Quality):** Strategy constants, PHPDoc, AI cache alignment (WP-003)

Testing (WP-004) and documentation/build (WP-005) follow once all implementation WPs are complete.

## Work Package Summary

| WP | Title | Dependencies | Status |
|---|---|---|---|
| [WP-001](work/WP-001.md) | Defensive Hardening — Exception Class, Input Validation, and Trait Resilience | — | READY |
| [WP-002](work/WP-002.md) | User Isolation Type System — Interface and Trait | WP-001 | BLOCKED |
| [WP-003](work/WP-003.md) | Code Quality — Strategy Constants, Guards, PHPDoc, and AI Cache Alignment | — | READY |
| [WP-004](work/WP-004.md) | Unit Tests | WP-001, WP-002, WP-003 | BLOCKED |
| [WP-005](work/WP-005.md) | Documentation and Build | WP-001, WP-002, WP-003, WP-004 | BLOCKED |

## Dependency Graph

```
WP-001 (Defensive Hardening)  ──┬──→  WP-002 (User Isolation)  ──┬──→  WP-004 (Tests)  ──→  WP-005 (Docs & Build)
                                │                                 │
WP-003 (Code Quality)  ────────┼─────────────────────────────────┘
                                │
                                └─────────────────────────────────────→  (WP-003 is independent)
```

WP-001 and WP-003 can be implemented in parallel. WP-002 depends on WP-001 (needs `APICacheException`). WP-004 depends on all three implementation WPs. WP-005 is the final step.

## Plan Coverage

All 14 plan steps are mapped to work packages:

| Step | Description | WP |
|---|---|---|
| 1 | UserScopedCacheInterface | WP-002 |
| 2 | APICacheException | WP-001 |
| 3 | UserScopedCacheTrait | WP-002 |
| 4 | APICacheManager input validation | WP-001 |
| 5 | readFromCache corrupt-cache resilience | WP-001 |
| 6 | serialize → json_encode | WP-001 |
| 7 | STRATEGY_ID constants | WP-003 |
| 8 | filemtime() guard | WP-003 |
| 9 | @phpstan-require-implements on CacheableAPIMethodTrait | WP-001 |
| 10 | PHPDoc alignment | WP-003 |
| 11 | AI cache strategy ID migration | WP-003 |
| 12 | Module documentation update | WP-005 |
| 13 | Unit tests | WP-004 |
| 14 | composer dump-autoload and build | WP-005 |

```
###  Path: `/docs/agents/implementation-history/2026-03-13-api-caching-core-rework-1/work/WP-001.md`

```md
# WP-001: Defensive Hardening — Exception Class, Input Validation, and Trait Resilience

**Assigned to:** Developer  
**Priority:** High (Section B) + Foundation for Section A  
**Dependencies:** None  
**Plan Steps:** 2, 4, 5, 6, 9

---

## Description

Create the `APICacheException` class and apply three defensive hardening changes to existing cache infrastructure classes. This work package establishes the exception foundation needed by subsequent WPs and hardens `APICacheManager` and `CacheableAPIMethodTrait` against edge cases identified in the Phase 1 synthesis report.

## Requirements

### R1: Create `APICacheException` (Step 2)

Create `src/classes/Application/API/Cache/APICacheException.php`:

- Extend `Application\API\APIException` (inherits the framework 4-parameter exception signature).
- Define error code constants for all cache-specific error conditions:
  - `ERROR_EMPTY_USER_CACHE_IDENTIFIER` — user-scoped method returned an empty identifier
  - `ERROR_INVALID_METHOD_NAME` — empty or path-traversal-containing method name passed to `APICacheManager`
  - `ERROR_CACHE_FILE_CORRUPT` — (reserved, for logging context — not thrown directly in the resilience path)

### R2: `APICacheManager::getMethodCacheFolder()` Input Validation (Step 4)

In `APICacheManager::getMethodCacheFolder()`:

- Add a precondition guard that throws `APICacheException` (code `ERROR_INVALID_METHOD_NAME`) if `$methodName` is:
  - An empty string
  - Contains `DIRECTORY_SEPARATOR`, `/`, or `..`
- This prevents the `invalidateMethod('') === clearAll()` scenario and blocks path traversal.

### R3: `CacheableAPIMethodTrait::readFromCache()` Corrupt-Cache Resilience (Step 5)

In `CacheableAPIMethodTrait::readFromCache()`:

- Wrap `$cacheFile->parse()` (or equivalent JSON parsing call) in a try-catch for `\Throwable`.
- On exception: delete the corrupt cache file (best-effort, ignore deletion failures) and return `null`.
- This ensures a corrupt cache file never breaks an otherwise working API method.

### R4: `serialize()` → `json_encode()` in `getCacheKey()` (Step 6)

In `CacheableAPIMethodTrait::getCacheKey()`:

- Replace `serialize($params)` with `json_encode($params, JSON_THROW_ON_ERROR)`.
- `JSON_THROW_ON_ERROR` ensures non-encodable values fail fast rather than producing silent `false`.
- More portable than `serialize()`, avoids `__sleep()` edge cases, makes the intent (scalar-only params) explicit.

### R5: `@phpstan-require-implements` on `CacheableAPIMethodTrait` (Step 9)

Add `@phpstan-require-implements CacheableAPIMethodInterface` to the class-level docblock of `CacheableAPIMethodTrait`.

## Technical Constraints

- All new files must use `declare(strict_types=1)`.
- All array creation must use `array()` syntax, never `[]`.
- No PHP enums, no `readonly` properties.
- Run `composer dump-autoload` after creating the new class file (APICacheException).

## Files

**New files:**

| File | Type |
|---|---|
| `src/classes/Application/API/Cache/APICacheException.php` | Exception class |

**Modified files:**

| File | Change |
|---|---|
| `src/classes/Application/API/Cache/APICacheManager.php` | Add input validation guard in `getMethodCacheFolder()` |
| `src/classes/Application/API/Cache/CacheableAPIMethodTrait.php` | 3 changes: corrupt-cache try-catch in `readFromCache()`, `json_encode` in `getCacheKey()`, `@phpstan-require-implements` docblock |

## Acceptance Criteria

- [ ] `APICacheException` exists with all three error code constants and extends `Application\API\APIException`.
- [ ] `APICacheManager::getMethodCacheFolder()` throws `APICacheException` for empty string, strings with `/`, `..`, or `DIRECTORY_SEPARATOR`.
- [ ] `CacheableAPIMethodTrait::readFromCache()` returns `null` and deletes the file when the cache file contains invalid JSON.
- [ ] `CacheableAPIMethodTrait::getCacheKey()` uses `json_encode($params, JSON_THROW_ON_ERROR)` instead of `serialize()`.
- [ ] `CacheableAPIMethodTrait` class docblock includes `@phpstan-require-implements CacheableAPIMethodInterface`.
- [ ] `composer dump-autoload` runs successfully after changes.
- [ ] `composer analyze` reports no new errors.

```
###  Path: `/docs/agents/implementation-history/2026-03-13-api-caching-core-rework-1/work/WP-002.md`

```md
# WP-002: User Isolation Type System — Interface and Trait

**Assigned to:** Developer  
**Priority:** High (Section A — Security)  
**Dependencies:** WP-001  
**Plan Steps:** 1, 3

---

## Description

Introduce a dedicated `UserScopedCacheInterface` / `UserScopedCacheTrait` pair that makes it structurally impossible for user-scoped API methods to omit user-identifying data from cache keys. This is the core security deliverable of the plan — it enforces per-user cache isolation at the type system level.

## Requirements

### R1: Create `UserScopedCacheInterface` (Step 1)

Create `src/classes/Application/API/Cache/UserScopedCacheInterface.php`:

- Extends `CacheableAPIMethodInterface`.
- Declares two methods:
  1. `getUserCacheIdentifier(): string` — returns a unique, non-empty identifier for the current user context (e.g., pseudo user ID from API key).
  2. `getUserScopedCacheKeyParameters(): array` — returns the method-specific cache key parameters, excluding user identification (which the trait handles automatically).

### R2: Create `UserScopedCacheTrait` (Step 3)

Create `src/classes/Application/API/Cache/UserScopedCacheTrait.php`:

- Uses `CacheableAPIMethodTrait` internally.
- Overrides `getCacheKeyParameters()` to:
  1. Call `$this->getUserCacheIdentifier()`.
  2. **Hard fail** (throw `APICacheException` with code `ERROR_EMPTY_USER_CACHE_IDENTIFIER`) if the return value is empty — a user-scoped method must always provide a user identifier; silent fallback is not acceptable.
  3. Merge `'_userScope' => $identifier` with the result of `getUserScopedCacheKeyParameters()`.
- Add `@phpstan-require-implements UserScopedCacheInterface` to the class-level docblock.

### Design Notes

**Two-tier caching system result:**

- `CacheableAPIMethodInterface` + `CacheableAPIMethodTrait` → for stateless, non-user-scoped methods (e.g., `GetAppLocalesAPI`).
- `UserScopedCacheInterface` + `UserScopedCacheTrait` → for methods returning user-specific data (enforces user isolation by construction).

**Why subinterface rather than modifying `CacheableAPIMethodInterface`:**

Not all cacheable methods are user-scoped. `GetAppLocalesAPI` and `GetAppCountriesAPI` are stateless — forcing them to provide a user identifier would be wrong. The two-tier approach cleanly separates concerns.

**`BaseAPIMethod::_process()` needs no changes** — the existing `instanceof CacheableAPIMethodInterface` check already covers the subinterface.

**Security context:** API methods run outside normal session context (`APIBootstrap` calls `disableAuthentication()`). User identification happens via API keys (`APIKeyMethodInterface`), not sessions. `collectRequestData()` is bypassed entirely on cache hits — this is the security-critical gap that this WP addresses by ensuring user identity is always part of the cache key.

## Technical Constraints

- All new files must use `declare(strict_types=1)`.
- All array creation must use `array()` syntax, never `[]`.
- No PHP enums, no `readonly` properties.
- Run `composer dump-autoload` after creating the new class files.

## Files

**New files:**

| File | Type |
|---|---|
| `src/classes/Application/API/Cache/UserScopedCacheInterface.php` | Interface |
| `src/classes/Application/API/Cache/UserScopedCacheTrait.php` | Trait |

## Acceptance Criteria

- [ ] `UserScopedCacheInterface` extends `CacheableAPIMethodInterface` and declares `getUserCacheIdentifier(): string` and `getUserScopedCacheKeyParameters(): array`.
- [ ] `UserScopedCacheTrait` uses `CacheableAPIMethodTrait` and overrides `getCacheKeyParameters()`.
- [ ] `getCacheKeyParameters()` in the trait automatically injects `'_userScope'` key with the user identifier.
- [ ] An empty user identifier causes `APICacheException` to be thrown (hard failure, not silent fallback).
- [ ] `UserScopedCacheTrait` docblock includes `@phpstan-require-implements UserScopedCacheInterface`.
- [ ] `composer dump-autoload` runs successfully.
- [ ] `composer analyze` reports no new errors.

```
###  Path: `/docs/agents/implementation-history/2026-03-13-api-caching-core-rework-1/work/WP-003.md`

```md
# WP-003: Code Quality — Strategy Constants, Guards, PHPDoc, and AI Cache Alignment

**Assigned to:** Developer  
**Priority:** Low (Section C — Code Quality)  
**Dependencies:** None  
**Plan Steps:** 7, 8, 10, 11

---

## Description

Apply code quality improvements to the API cache strategy classes and align naming conventions between the API cache and AI cache strategy systems. This includes adding `STRATEGY_ID` constants, a defensive `filemtime()` guard, PHPDoc annotations, and migrating AI cache strategy IDs from `snake_case` to `PascalCase`.

## Requirements

### R1: Add `STRATEGY_ID` Constants (Step 7)

**`src/classes/Application/API/Cache/Strategies/FixedDurationStrategy.php`:**
- Add `public const string STRATEGY_ID = 'FixedDuration';`
- Update `getID()` to return `self::STRATEGY_ID`.

**`src/classes/Application/API/Cache/Strategies/ManualOnlyStrategy.php`:**
- Add `public const string STRATEGY_ID = 'ManualOnly';`
- Update `getID()` to return `self::STRATEGY_ID`.

Use **PascalCase** for strategy IDs — this aligns with the project norm for constant identifiers.

### R2: `filemtime()` Explicit Guard (Step 8)

In `src/classes/Application/API/Cache/Strategies/FixedDurationStrategy.php`, method `isCacheFileValid()`:

- Explicitly check the `filemtime()` return value. If it returns `false`, return `false` (treat as expired).
- This prevents arithmetic on `false` and satisfies strict PHPStan levels.

### R3: PHPDoc Alignment (Step 10)

- Add `@package API` and `@subpackage Cache` class-level docblocks to `FixedDurationStrategy` and `ManualOnlyStrategy` (if not already present).
- Fix `@package` annotation on `RegisterAPIResponseCacheListener` to match its sibling `RegisterAPIIndexCacheListener`.

**Note:** Check the actual `@package` values on `RegisterAPIIndexCacheListener` first and align `RegisterAPIResponseCacheListener` to match. Do not guess the package name.

### R4: AI Cache Strategy ID Migration to PascalCase (Step 11)

In `src/classes/Application/AI/Cache/Strategies/`:

- **`FixedDurationStrategy.php`:** Change `STRATEGY_ID` from `'fixed_duration'` to `'FixedDuration'`.
- **`UncachedStrategy.php`:** Change `STRATEGY_ID` from `'uncached'` to `'Uncached'`.
- **Search for any code referencing the old string values** (`'fixed_duration'`, `'uncached'`) and update all references.

## Technical Constraints

- All array creation must use `array()` syntax, never `[]`.
- No PHP enums, no `readonly` properties.
- Verify the old AI cache strategy ID strings are not used in stored data (database, config files) before changing — if they are, a migration step may be required (flag this for review).

## Files

**Modified files:**

| File | Change |
|---|---|
| `src/classes/Application/API/Cache/Strategies/FixedDurationStrategy.php` | Add `STRATEGY_ID` constant, update `getID()`, add `filemtime()` guard, add PHPDoc |
| `src/classes/Application/API/Cache/Strategies/ManualOnlyStrategy.php` | Add `STRATEGY_ID` constant, update `getID()`, add PHPDoc |
| `src/classes/Application/AI/Cache/Strategies/FixedDurationStrategy.php` | Change `STRATEGY_ID` to `'FixedDuration'` |
| `src/classes/Application/AI/Cache/Strategies/UncachedStrategy.php` | Change `STRATEGY_ID` to `'Uncached'` |
| Event listener file for `RegisterAPIResponseCacheListener` | Fix `@package` annotation |

## Acceptance Criteria

- [ ] Both API cache strategies have `STRATEGY_ID` constants and `getID()` returns `self::STRATEGY_ID`.
- [ ] `FixedDurationStrategy::isCacheFileValid()` explicitly handles `filemtime() === false`.
- [ ] `FixedDurationStrategy` and `ManualOnlyStrategy` have `@package` and `@subpackage` class-level PHPDoc.
- [ ] `RegisterAPIResponseCacheListener` `@package` annotation matches `RegisterAPIIndexCacheListener`.
- [ ] AI cache strategy IDs are changed to PascalCase: `'FixedDuration'` and `'Uncached'`.
- [ ] All references to old AI cache strategy ID strings are updated.
- [ ] `composer analyze` reports no new errors.

```
###  Path: `/docs/agents/implementation-history/2026-03-13-api-caching-core-rework-1/work/WP-004.md`

```md
# WP-004: Unit Tests

**Assigned to:** Developer  
**Priority:** High  
**Dependencies:** WP-001, WP-002, WP-003  
**Plan Steps:** 13

---

## Description

Write comprehensive unit tests covering all new functionality and hardening changes introduced in WP-001 through WP-003. Re-run existing API cache tests to confirm no regressions.

## Requirements

### R1: Tests for `UserScopedCacheTrait` (User Isolation)

- Verify that `getCacheKeyParameters()` always includes the `'_userScope'` key with the user identifier value.
- Verify that different user identifiers produce different cache keys (call `getCacheKey()` with two different identifiers and assert they differ).
- Verify that an empty user identifier throws `APICacheException` with code `ERROR_EMPTY_USER_CACHE_IDENTIFIER` (hard failure).

**Test approach:** Create a minimal test stub class that implements `UserScopedCacheInterface` and uses `UserScopedCacheTrait`. The stub should extend or mock `BaseAPIMethod` sufficiently to allow `getCacheKeyParameters()` and `getCacheKey()` to work.

### R2: Tests for `APICacheManager` Input Validation

Verify that `APICacheManager::getMethodCacheFolder()` throws `APICacheException` with code `ERROR_INVALID_METHOD_NAME` for:
- Empty string (`''`)
- String containing `/` (e.g., `'foo/bar'`)
- String containing `..` (e.g., `'foo..bar'` or `'../etc'`)
- String containing `DIRECTORY_SEPARATOR` (on the test OS)

Verify that valid method names (e.g., `'GetTenantsAPI'`) do **not** throw.

### R3: Tests for Corrupt-Cache Resilience

- Create a cache file with invalid JSON content at the expected path for a test method.
- Call `readFromCache()` and verify it returns `null`.
- Verify the corrupt file was deleted after the call.

**Test approach:** This may require a test stub implementing `CacheableAPIMethodInterface` + `CacheableAPIMethodTrait`, or it may be possible to test via an existing test method class if one exists in the test suite.

### R4: Tests for `json_encode` Cache Key

- Verify that `getCacheKey()` produces a deterministic hash for the same parameters.
- Verify that `getCacheKey()` produces different hashes for different parameters.
- (Optional) Verify that non-encodable values (e.g., resources) cause an exception rather than silent failure.

### R5: Regression — Re-run Existing Tests

Re-run all existing API cache tests (the 19 tests mentioned in the plan) to confirm no breakage from the hardening changes.

Use `composer test-filter -- APICache` or the appropriate filter pattern to run only API-cache-related tests.

## Technical Constraints

- Test files go under `tests/AppFrameworkTests/API/Cache/`.
- All array creation must use `array()` syntax, never `[]`.
- Follow existing test conventions in the framework (check `tests/AppFrameworkTests/` for base classes and patterns).
- Run with `composer test-file -- <path>` or `composer test-filter -- <pattern>`. **Never run the full test suite.**

## Files

**New test files:**

| File | Type |
|---|---|
| `tests/AppFrameworkTests/API/Cache/UserScopedCacheTest.php` | Unit tests for user isolation |
| `tests/AppFrameworkTests/API/Cache/APICacheManagerValidationTest.php` | Unit tests for input validation |
| `tests/AppFrameworkTests/API/Cache/CacheResilienceTest.php` | Unit tests for corrupt-cache and json_encode key |

> **Note:** The developer may consolidate these into fewer files if the existing test structure suggests a different organization. The file names above are suggestions.

## Acceptance Criteria

- [ ] All `UserScopedCacheTrait` tests pass: user scope injection, different-user-different-key, empty-identifier-exception.
- [ ] All `APICacheManager` input validation tests pass: empty string, path separators, `..` all throw; valid names pass.
- [ ] Corrupt-cache resilience test passes: returns `null`, deletes corrupt file.
- [ ] `json_encode` cache key tests pass: deterministic hashing, different params → different hashes.
- [ ] All 19 existing API cache tests pass (no regressions).
- [ ] Tests run successfully via `composer test-file` or `composer test-filter`.

```
###  Path: `/docs/agents/implementation-history/2026-03-13-api-caching-core-rework-1/work/WP-005.md`

```md
# WP-005: Documentation and Build

**Assigned to:** Developer  
**Priority:** Medium  
**Dependencies:** WP-001, WP-002, WP-003, WP-004  
**Plan Steps:** 12, 14

---

## Description

Update the API Cache module's documentation to reflect the new two-tier caching pattern (stateless vs. user-scoped), the strategy ID constants, and the defensive hardening changes. Update the `module-context.yaml` to include the new files. Run `composer dump-autoload` and `composer build` to finalize.

## Requirements

### R1: Update Module README (Step 12)

Update `src/classes/Application/API/Cache/README.md` to document:

1. **Two-tier caching pattern:**
   - `CacheableAPIMethodInterface` + `CacheableAPIMethodTrait` for stateless, non-user-scoped methods.
   - `UserScopedCacheInterface` + `UserScopedCacheTrait` for methods returning user-specific data.
   - Explain that the user-scoped variant enforces per-user cache isolation by automatically injecting the user identifier into the cache key.

2. **Strategy ID constants:** Document that each strategy has a `STRATEGY_ID` constant and uses PascalCase naming.

3. **APICacheException:** Document the exception class and its error codes.

### R2: Update `module-context.yaml` (Step 12)

Update `src/classes/Application/API/Cache/module-context.yaml` to include:
- `APICacheException.php`
- `UserScopedCacheInterface.php`
- `UserScopedCacheTrait.php`

Ensure these files are included in the appropriate document definitions (architecture-core or similar) so they appear in the generated `.context/` documentation.

### R3: Run `composer dump-autoload` (Step 14)

New class files were added in WP-001 and WP-002. Run `composer dump-autoload` to update the classmap.

> **Note:** The developer should have run `composer dump-autoload` during WP-001 and WP-002 as well. This step ensures it's current after all changes.

### R4: Run `composer build` (Step 14)

Run `composer build` to regenerate all CTX documentation, including the updated module docs reflecting the new files and structure.

## Technical Constraints

- README should follow the existing style/format of the module's current README.
- `module-context.yaml` changes must follow the CTX Generator schema (see `docs/agents/research/module-context-reference.md` for the format).

## Files

**Modified files:**

| File | Change |
|---|---|
| `src/classes/Application/API/Cache/README.md` | Document two-tier caching, strategy constants, exception class |
| `src/classes/Application/API/Cache/module-context.yaml` | Add new files to document definitions |

## Acceptance Criteria

- [ ] `README.md` documents the two-tier caching pattern with clear usage guidance.
- [ ] `README.md` documents strategy ID constants and the `APICacheException` class.
- [ ] `module-context.yaml` includes all three new files (`APICacheException`, `UserScopedCacheInterface`, `UserScopedCacheTrait`).
- [ ] `composer dump-autoload` runs successfully.
- [ ] `composer build` runs successfully and regenerates `.context/` documentation.

```
###  Path: `/docs/agents/implementation-history/2026-03-13-api-caching-core/plan.md`

```md
# Plan: API Response Caching — Core Infrastructure

**Series:** API Caching System (Plan 1 of 3)  
**Project:** Application Framework  
**Depends on:** Nothing  
**Blocked by:** Nothing  
**Reference:** [/docs/agents/projects/api-caching-system.md](/docs/agents/projects/api-caching-system.md)

---

## Summary

Add file-based response caching to the framework's API method layer. API methods opt in via an Interface + Trait pair (following the existing `DryRunAPIInterface`/`DryRunAPITrait` pattern). Two cache strategies are provided: fixed-duration TTL and manual-only. A static `APICacheManager` handles folder management, per-method invalidation, and global clearing. The `BaseAPIMethod::_process()` method is modified to check and write cache. The CacheControl system receives a new cache location so the API response cache appears in the admin cache management UI.

## Architectural Context

- **API method processing:** `BaseAPIMethod::_process()` in `src/classes/Application/API/BaseMethods/BaseAPIMethod.php` is the central pipeline: `validate() → collectRequestData() → collectResponseData() → sendSuccessResponse()`. The `sendSuccessResponse()` method is typed `never` and halts execution.
- **Interface + Trait composition pattern:** Used throughout the API layer — e.g., `DryRunAPIInterface`/`DryRunAPITrait` in `src/classes/Application/API/Traits/`. Methods implement the interface and `use` the trait; the base class checks `$this instanceof SomeInterface` to enable behavior.
- **AI cache strategies:** An existing file-based TTL cache exists at `src/classes/Application/AI/Cache/Strategies/FixedDurationStrategy.php`, serving as a design reference. The API cache strategies are independent but modeled after this pattern.
- **CacheControl system:** `CacheLocationInterface` / `BaseCacheLocation` in `src/classes/Application/CacheControl/`. Locations are registered via event listeners extending `BaseRegisterCacheLocationsListener` (e.g., `RegisterAPIIndexCacheListener` in `src/classes/Application/API/Events/`).
- **Storage layout:** `Application::getStorageSubfolderPath()` provides application storage paths. Cache files go under `{APP_STORAGE}/api/cache/{MethodName}/{hash}.json`.
- **File I/O:** `FolderInfo::factory()` for folder operations, `JSONFile::factory()` for JSON file read/write. Never use raw `file_get_contents`.
- **Autoloading:** Classmap-based. Run `composer dump-autoload` after creating new files.

## Approach / Architecture

Create a new `Application\API\Cache` namespace under `src/classes/Application/API/Cache/` containing:

1. **Strategy interface** (`APICacheStrategyInterface`) — defines `getID()` and `isCacheFileValid(JSONFile)`.
2. **Two strategy implementations** — `FixedDurationStrategy` (time-based TTL) and `ManualOnlyStrategy` (always valid).
3. **Cacheable interface + trait** (`CacheableAPIMethodInterface`/`CacheableAPIMethodTrait`) — methods implement the interface and `use` the trait; they only need to define `getCacheStrategy()` and `getCacheKeyParameters()`.
4. **Cache manager** (`APICacheManager`) — static utility for folder management, per-method invalidation, and global clearing.
5. **`BaseAPIMethod::_process()` modification** — insert cache check after `validate()`/`getActiveVersion()` (short-circuits on hit via `sendSuccessResponse()`) and cache write before the final `sendSuccessResponse()`.
6. **CacheControl integration** — `APIResponseCacheLocation` + `RegisterAPIResponseCacheListener` so the cache appears in admin cache management.

## Rationale

- **Interface + Trait pattern** is the established API extensibility mechanism — no new patterns introduced.
- **File-based JSON caching** aligns with the existing AI cache and requires no additional infrastructure (no Redis/Memcached dependency).
- **Per-method subdirectories** allow `invalidateMethod()` to delete one method's entire cache with a single folder delete, without touching other methods.
- **Cache check after `validate()`** is required because parameter values (needed for the cache key) are only available after validation runs.
- **Two-phase approach** (core now, DBHelper invalidation later) avoids coupling the base caching mechanism to DBHelper events, keeping Phase 1 self-contained.

## Detailed Steps

### Step 1: Create cache strategy interface

Create `src/classes/Application/API/Cache/APICacheStrategyInterface.php` with:
- `getID(): string`
- `isCacheFileValid(JSONFile $cacheFile): bool`

See the project document for the full implementation.

### Step 2: Create `FixedDurationStrategy`

Create `src/classes/Application/API/Cache/Strategies/FixedDurationStrategy.php` with:
- Duration constants: 1min, 5min, 15min, 1h, 6h, 12h, 24h
- Constructor accepting `int $durationInSeconds`
- `isCacheFileValid()` using `filemtime()` comparison

### Step 3: Create `ManualOnlyStrategy`

Create `src/classes/Application/API/Cache/Strategies/ManualOnlyStrategy.php`:
- `isCacheFileValid()` always returns `true`
- Cache only invalidated by explicit `invalidateCache()` calls

### Step 4: Create `CacheableAPIMethodInterface`

Create `src/classes/Application/API/Cache/CacheableAPIMethodInterface.php` extending `APIMethodInterface`:
- `getCacheStrategy(): APICacheStrategyInterface`
- `getCacheKeyParameters(): array`
- `getCacheKey(string $version): string`
- `readFromCache(string $version): ?array`
- `writeToCache(string $version, array $data): void`
- `invalidateCache(): void`

### Step 5: Create `APICacheManager`

Create `src/classes/Application/API/Cache/APICacheManager.php`:
- `getCacheFolder(): FolderInfo` — returns `{APP_STORAGE}/api/cache/`
- `invalidateMethod(string $methodName): void` — deletes the method's subfolder
- `clearAll(): void` — deletes and recreates the entire cache folder
- `getCacheSize(): int` — returns total byte size

### Step 6: Create `CacheableAPIMethodTrait`

Create `src/classes/Application/API/Cache/CacheableAPIMethodTrait.php`:
- `getCacheKey()` — builds hash from method name + version + sorted parameter values
- `getCacheFile()` — returns `JSONFile` at `{cache}/{MethodName}/{hash}.json`
- `readFromCache()` — checks existence, validates via strategy, returns parsed data or null
- `writeToCache()` — writes data via `JSONFile::putData()`
- `invalidateCache()` — delegates to `APICacheManager::invalidateMethod()`

**Note:** The trait depends on `APICacheManager` (Step 5), so it must be created after the manager.

### Step 7: Modify `BaseAPIMethod::_process()`

In `src/classes/Application/API/BaseMethods/BaseAPIMethod.php`:
1. Add `use Application\API\Cache\CacheableAPIMethodInterface;` import.
2. After `$version = $this->getActiveVersion();`, insert cache check:
   ```php
   if ($this instanceof CacheableAPIMethodInterface) {
       $cached = $this->readFromCache($version);
       if ($cached !== null) {
           $this->sendSuccessResponse(ArrayDataCollection::create($cached));
       }
   }
   ```
3. Before `$this->sendSuccessResponse($response);`, insert cache write:
   ```php
   if ($this instanceof CacheableAPIMethodInterface) {
       $this->writeToCache($version, $response->getData());
   }
   ```

### Step 8: Create `APIResponseCacheLocation`

Create `src/classes/Application/API/Cache/APIResponseCacheLocation.php` extending `BaseCacheLocation`:
- `getID()` → `'APIResponseCache'`
- `getLabel()` → `t('API Response Cache')`
- `getByteSize()` → delegates to `APICacheManager::getCacheSize()`
- `clear()` → delegates to `APICacheManager::clearAll()`

### Step 9: Create `RegisterAPIResponseCacheListener`

Create `src/classes/Application/API/Events/RegisterAPIResponseCacheListener.php` extending `BaseRegisterCacheLocationsListener`:
- Returns `array(new APIResponseCacheLocation())` from `getCacheLocations()`

Follows the same pattern as the existing `RegisterAPIIndexCacheListener` in the same directory.

### Step 10: Run `composer dump-autoload`

Required because the project uses classmap autoloading. All 8 new files must be indexed.

### Step 11: Write unit tests

Create test file(s) under `tests/AppFrameworkTests/API/Cache/`:

**Strategy tests:**
- `FixedDurationStrategy::isCacheFileValid()` returns `true` for fresh file
- `FixedDurationStrategy::isCacheFileValid()` returns `false` for expired file
- `ManualOnlyStrategy::isCacheFileValid()` always returns `true`

**Cache key tests:**
- `getCacheKey()` is deterministic (same inputs → same hash)
- `getCacheKey()` varies by version
- `getCacheKey()` varies by parameter values

**Cache manager tests:**
- `invalidateMethod()` deletes only the target method folder
- `clearAll()` deletes all method folders

### Step 12: Write integration tests

Create integration test(s) using a test API method stub that implements `CacheableAPIMethodInterface`:

- Call `processReturn()` twice — second call returns same data from cache file
- Call `processReturn()`, then `invalidateCache()`, then `processReturn()` — second call computes fresh
- Verify cache file written to expected path `{APP_STORAGE}/api/cache/{MethodName}/`
- `APIResponseCacheLocation::getByteSize()` returns > 0 after caching
- `APIResponseCacheLocation::clear()` removes all cached responses

Use `composer test-file` or `composer test-filter` to run tests. **Never run the full suite.**

### Step 13: Run static analysis

Run `composer analyze` to verify PHPStan passes with the new code.

## Dependencies

- `AppUtils\FileHelper\JSONFile` (existing dependency)
- `AppUtils\FileHelper\FolderInfo` (existing dependency)
- `Application\CacheControl\BaseCacheLocation` (existing class)
- `Application\CacheControl\Events\BaseRegisterCacheLocationsListener` (existing class)
- `Application\API\BaseMethods\BaseAPIMethod` (existing class to modify)
- `Application\API\APIMethodInterface` (existing interface to extend)

## Required Components

**New files (8):**

| # | File | Type |
|---|---|---|
| 1 | `src/classes/Application/API/Cache/APICacheStrategyInterface.php` | Interface |
| 2 | `src/classes/Application/API/Cache/Strategies/FixedDurationStrategy.php` | Class |
| 3 | `src/classes/Application/API/Cache/Strategies/ManualOnlyStrategy.php` | Class |
| 4 | `src/classes/Application/API/Cache/CacheableAPIMethodInterface.php` | Interface |
| 5 | `src/classes/Application/API/Cache/CacheableAPIMethodTrait.php` | Trait |
| 6 | `src/classes/Application/API/Cache/APICacheManager.php` | Class (static) |
| 7 | `src/classes/Application/API/Cache/APIResponseCacheLocation.php` | Class |
| 8 | `src/classes/Application/API/Events/RegisterAPIResponseCacheListener.php` | Listener |

**Modified files (1):**

| File | Change |
|---|---|
| `src/classes/Application/API/BaseMethods/BaseAPIMethod.php` | Add cache check + write in `_process()`, add import |

**New test files (~2):**

| File | Type |
|---|---|
| `tests/AppFrameworkTests/API/Cache/APICacheStrategyTest.php` | Unit tests |
| `tests/AppFrameworkTests/API/Cache/APICacheIntegrationTest.php` | Integration tests |

## Assumptions

- `ArrayDataCollection::create()` accepts an associative array to pre-populate the collection (used when restoring cached data).
- `sendSuccessResponse()` is typed `never` and halts execution, so the cache hit path short-circuits correctly without a `return` statement.
- `processReturn()` (test mode) works with the cache because `sendSuccessResponse()` throws `APIResponseDataException` in return mode, which `processReturn()` catches — no change needed.
- The existing event listener discovery mechanism will automatically pick up `RegisterAPIResponseCacheListener` without manual registration.

## Constraints

- All new files must use `declare(strict_types=1)`.
- All array creation must use `array()` syntax, never `[]`.
- No PHP enums, no `readonly` properties.
- Run `composer dump-autoload` after creating files (classmap autoloading).
- Follow the exact class/method/constant naming shown in the project document.

## Out of Scope

- **DBHelper-aware automatic invalidation** — covered in Plan 2.
- **HCP Editor API method conversion** — covered in Plan 3.
- **Admin UI for viewing/managing the API cache** — beyond the three-plan scope.
- **Redis/Memcached backends** — file-based only.

## Acceptance Criteria

- [ ] All 8 new files created with correct namespaces and conventions.
- [ ] `BaseAPIMethod::_process()` contains cache check and write blocks.
- [ ] A test API method implementing `CacheableAPIMethodInterface` with `FixedDurationStrategy` returns cached responses on the second `processReturn()` call.
- [ ] `invalidateCache()` forces fresh computation on the next call.
- [ ] `APICacheManager::clearAll()` removes all cached data.
- [ ] `APIResponseCacheLocation` appears in the CacheControl system and reports correct size.
- [ ] All unit and integration tests pass via `composer test-file`.
- [ ] `composer analyze` passes with no new errors.

## Testing Strategy

- **Unit tests** for strategies (TTL validity, manual always-valid) and cache key generation (determinism, version/parameter variation).
- **Unit tests** for `APICacheManager` (per-method invalidation, global clear).
- **Integration tests** using a test stub API method and `processReturn()` to verify end-to-end cache hit/miss/invalidation behavior.
- **CacheControl integration tests** to verify byte size reporting and clearing.
- Run with `composer test-file` or `composer test-filter`. Never run the full test suite.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **`ArrayDataCollection::create($cached)` doesn't accept array argument** | Verify the method signature before implementing the cache hit path; adapt if needed (e.g., `create()->setKeys($cached)`). |
| **Cache files accumulate without bound** | `clearAll()` available via CacheControl admin; TTL strategies provide automatic expiry. Plan 2 adds event-based invalidation. |
| **Race condition: two concurrent requests write the same cache file** | JSON write is atomic at the filesystem level (write-then-rename in `JSONFile::putData()`). Last writer wins, which is acceptable since both write the same data. |
| **`validate()` side effects on cache hit path** | Validate runs on every request (needed for cache key). Confirm it has no expensive side effects beyond parameter parsing. |

```
###  Path: `/docs/agents/implementation-history/2026-03-13-api-caching-core/synthesis.md`

```md
# Synthesis Report — API Caching Core (Phase 1)

**Plan:** `2026-03-13-api-caching-core`
**Date:** 2026-03-13
**Status:** COMPLETE
**Work Packages:** 7 / 7 COMPLETE

---

## Executive Summary

Phase 1 of the API response caching system has been fully implemented in the Application Framework. The feature introduces a transparent, opt-in cache-aside mechanism for API methods, integrated directly into `BaseAPIMethod._process()`. Any API method that implements `CacheableAPIMethodInterface` and uses `CacheableAPIMethodTrait` automatically benefits from caching with no changes to its internal logic.

The implementation follows the established `DryRunAPIInterface` / `DryRunAPITrait` extensibility pattern exactly — zero new architectural patterns were introduced. Cache storage is file-based (JSON), structured under the application storage folder, and surfaced in the CacheControl admin UI via the standard `BaseRegisterCacheLocationsListener` auto-discovery mechanism.

**New files delivered (8 source + 3 test + 2 config + 3 documentation):**

| File | Purpose |
|---|---|
| `src/.../API/Cache/APICacheStrategyInterface.php` | Strategy contract |
| `src/.../API/Cache/Strategies/FixedDurationStrategy.php` | TTL-based strategy (7 constants) |
| `src/.../API/Cache/Strategies/ManualOnlyStrategy.php` | Manual-invalidation-only strategy |
| `src/.../API/Cache/APICacheManager.php` | Static cache folder manager |
| `src/.../API/Cache/CacheableAPIMethodInterface.php` | Opt-in caching contract for API methods |
| `src/.../API/Cache/CacheableAPIMethodTrait.php` | Cache-aside implementation |
| `src/.../API/Cache/APIResponseCacheLocation.php` | CacheControl admin UI integration |
| `src/.../API/Events/RegisterAPIResponseCacheListener.php` | Auto-discovered listener |
| `src/.../API/BaseMethods/BaseAPIMethod.php` | Modified: transparent cache check + write hooks |
| `src/.../API/Cache/README.md` | Module usage documentation |
| `src/.../API/Cache/module-context.yaml` | CTX Generator registration |
| `tests/.../API/Cache/APICacheStrategyTest.php` | 13 unit tests |
| `tests/.../API/Cache/APICacheIntegrationTest.php` | 6 integration tests |
| `tests/.../TestDriver/API/TestCacheableMethod.php` | Integration test stub |
| `changelog.md`, `README.md` | Updated with v7.0.11 entry |
| `docs/agents/project-manifest/testing.md` | Updated: env setup + API stub placement gotcha |

---

## Metrics

| Metric | Value |
|---|---|
| WPs completed | 7 / 7 |
| Pipelines passed | 28 / 28 (7 × implementation + qa + code-review + documentation) |
| Unit tests | 13 PASS / 0 FAIL |
| Integration tests | 6 PASS / 0 FAIL |
| **Total tests** | **19 PASS / 0 FAIL** |
| New PHPStan errors | **0** |
| Pre-existing PHPStan errors | 7 (unrelated files, pre-existing) |
| Pre-existing test failures | 2 HtaccessGeneratorTest (unrelated, feature-openapi-specs branch) |

---

## Blockers & Known Issues

### Resolved During This Session
- **Vendor packages not installed** — PHPUnit and PHPStan were unavailable during WP-001 through WP-005. Resolved in WP-006 by configuring the local test environment (`.dist` config files copied and credentials set). All 19 tests pass after resolution.

### Pre-existing (Not Introduced by This Work)
- **7 PHPStan errors** in `CountryRequestTrait.php`, `BaseDBRecordRequestType.php`, `FetchMany.php`, `PropertiesGrid.php`, `Submode.php`, `frame.footer.php`, `DisposingTest.php` — existed before this feature branch.
- **2 HtaccessGeneratorTest failures** (`test_defaultRewriteBase`, `test_defaultRewriteBaseConstant`) — introduced by the `feature-openapi-specs` branch (commit `727bc18d`), unrelated to caching.

---

## Strategic Recommendations

### Security — High Priority

> **Cache user isolation responsibility is on the implementing method.**

`BaseAPIMethod._process()` bypasses `collectRequestData()` entirely on a cache hit — including any per-user authorization or row-level access checks performed there. `CacheableAPIMethodInterface` now carries a class-level docblock warning. **Implementors must include user-identifying parameters (e.g., user ID, session scope) in `getCacheKeyParameters()` for any method returning user-scoped data.** This is the most important constraint to communicate to future developers adopting this feature.

### Medium Priority — Defensive Hardening (Post-Phase-1 Pass)

1. **`APICacheManager::getMethodCacheFolder()` input validation** — `$methodName` is concatenated directly into a filesystem path with no sanitization. Current callers always pass internal API method class constants (safe), but the lack of a precondition guard is a latent footgun. Add: `if (empty($methodName) || str_contains($methodName, DIRECTORY_SEPARATOR)) { throw new \InvalidArgumentException(...); }`. The `invalidateMethod('') === clearAll()` scenario is particularly dangerous.

2. **`readFromCache()` corrupt-cache resilience** — `JSONFile::parse()` is called without a try-catch. A partially-written or corrupted JSON file will surface as an unhandled exception from `BaseAPIMethod._process()`, failing the entire API request rather than falling back to fresh computation. A catch-and-return-null wrapper would make the cache layer transparent to callers on corruption.

### Low Priority — Code Quality

| Item | Location | Action |
|---|---|---|
| `serialize()` → `json_encode()` for cache key | `CacheableAPIMethodTrait::getCacheKey()` | `json_encode()` is more portable (no `__sleep()` edge cases) and makes the intent (scalar params only) more explicit |
| `filemtime() === false` explicit guard | `FixedDurationStrategy::isCacheFileValid()` | Current behavior is safe (expires-closed fallback) but PHPStan may flag the arithmetic on `false` at strict levels |
| `getID()` is untested and unused at runtime | `APICacheStrategyInterface` | Either add a test or document intended future use (e.g., strategy-aware invalidation, cache statistics) |
| `@phpstan-require-implements` on trait | `CacheableAPIMethodTrait` | Static enforcement that the trait is never used without `CacheableAPIMethodInterface` |
| STRATEGY_ID constant missing | `FixedDurationStrategy`, `ManualOnlyStrategy` | The AI cache equivalent defines `STRATEGY_ID` as a typed constant enabling strategy-switching without magic strings |
| `instanceof` fan-out in `_process()` | `BaseAPIMethod.php` | Two `instanceof` guards are clean now; consider a middleware/pipeline pattern if a third cross-cutting concern is added |
| Class-level PHPDoc missing | `FixedDurationStrategy`, `ManualOnlyStrategy` | All other cache classes have `@package`/`@subpackage` annotations; bring strategy classes to the same standard |
| `@package` annotation inconsistency | `RegisterAPIResponseCacheListener` | Differs from sibling `RegisterAPIIndexCacheListener`; align to framework convention |

### Architectural Insight — Gold Nugget

> **`TestCacheableMethod::getCollectCount()` is an excellent zero-mock observability pattern** for API method tests that need to distinguish cache hits (count unchanged) from fresh executions (count incremented). This technique is worth adopting in all future API method tests that need to verify execution-path branching without mocking `BaseAPIMethod`.

---

## Next Steps

### Immediate (Before Merging)
1. **Run `composer build`** — regenerate `.context/modules/api-cache/` from the new `module-context.yaml`. This is required for the CTX documentation to reflect the new module.
2. **Merge to main** — no blocking issues remain; all 28 pipelines PASS.

### Phase 2 Planning
3. **`DBHelperAwareStrategy`** — cache invalidation tied to database record changes (listed as Phase 2 in `docs/agents/projects/api-caching-system.md`). This is the highest-value next increment.
4. **Defensive hardening pass** — address the `getMethodCacheFolder()` input validation and `readFromCache()` corrupt-cache resilience items above before the first production consumers are onboarded.

### Maintenance
5. **Address 2 HtaccessGeneratorTest failures** on the `feature-openapi-specs` branch — pre-existing but should not land on `main` unresolved.
6. **Address 7 pre-existing PHPStan errors** in a dedicated cleanup pass.
7. **AI cache alignment** — `FixedDurationStrategy` has 7 duration constants; the `Application\AI\Cache\Strategies` equivalent has 4. Decide whether to align them, and document strategy ID naming conventions (`FixedDuration` PascalCase vs `fixed_duration` snake_case) across the two systems.

---

## Deliverables Checklist

- [x] `APICacheStrategyInterface` + strategy classes (WP-001)
- [x] `APICacheManager` static folder manager (WP-002)
- [x] `CacheableAPIMethodInterface` + `CacheableAPIMethodTrait` (WP-003)
- [x] `BaseAPIMethod._process()` transparent cache-aside integration (WP-004)
- [x] `APIResponseCacheLocation` + listener for CacheControl admin UI (WP-005)
- [x] 19 passing tests: 13 unit + 6 integration (WP-006)
- [x] PHPStan: 0 new errors (WP-007)
- [x] `src/classes/Application/API/Cache/README.md` module documentation
- [x] `module-context.yaml` CTX Generator registration
- [x] `changelog.md` v7.0.11 entry
- [x] `docs/agents/project-manifest/testing.md` updated (env setup + API stub placement)
- [ ] `composer build` — pending (generates `.context/modules/api-cache/`)

```
###  Path: `/docs/agents/implementation-history/2026-03-13-api-caching-core/work.md`

```md
# Work Packages: API Response Caching — Core Infrastructure

**Plan:** [plan.md](plan.md)  
**Project:** Application Framework  
**Total WPs:** 7  

---

## Overview

| WP | Title | Dependencies | Status |
|---|---|---|---|
| [WP-001](work/WP-001.md) | Cache Strategy Interface & Implementations | — | READY |
| [WP-002](work/WP-002.md) | Cache Manager | — | READY |
| [WP-003](work/WP-003.md) | Cacheable API Method Interface & Trait | WP-001, WP-002 | BLOCKED |
| [WP-004](work/WP-004.md) | BaseAPIMethod Cache Integration | WP-003 | BLOCKED |
| [WP-005](work/WP-005.md) | CacheControl System Integration | WP-002 | BLOCKED |
| [WP-006](work/WP-006.md) | Unit & Integration Tests | WP-004, WP-005 | BLOCKED |
| [WP-007](work/WP-007.md) | Static Analysis Verification | WP-006 | BLOCKED |

## Dependency Graph

```
WP-001 (Strategies) ──┐
                       ├──→ WP-003 (Interface+Trait) ──→ WP-004 (BaseAPIMethod) ──┐
WP-002 (Manager) ─────┤                                                            ├──→ WP-006 (Tests) ──→ WP-007 (Analysis)
                       └──→ WP-005 (CacheControl) ────────────────────────────────┘
```

## New Files (8)

| # | File | Type | WP |
|---|---|---|---|
| 1 | `src/classes/Application/API/Cache/APICacheStrategyInterface.php` | Interface | WP-001 |
| 2 | `src/classes/Application/API/Cache/Strategies/FixedDurationStrategy.php` | Class | WP-001 |
| 3 | `src/classes/Application/API/Cache/Strategies/ManualOnlyStrategy.php` | Class | WP-001 |
| 4 | `src/classes/Application/API/Cache/CacheableAPIMethodInterface.php` | Interface | WP-003 |
| 5 | `src/classes/Application/API/Cache/CacheableAPIMethodTrait.php` | Trait | WP-003 |
| 6 | `src/classes/Application/API/Cache/APICacheManager.php` | Class (static) | WP-002 |
| 7 | `src/classes/Application/API/Cache/APIResponseCacheLocation.php` | Class | WP-005 |
| 8 | `src/classes/Application/API/Events/RegisterAPIResponseCacheListener.php` | Listener | WP-005 |

## Modified Files (1)

| File | Change | WP |
|---|---|---|
| `src/classes/Application/API/BaseMethods/BaseAPIMethod.php` | Add cache check + write in `_process()` | WP-004 |

## Test Files (~2)

| File | Type | WP |
|---|---|---|
| `tests/AppFrameworkTests/API/Cache/APICacheStrategyTest.php` | Unit tests | WP-006 |
| `tests/AppFrameworkTests/API/Cache/APICacheIntegrationTest.php` | Integration tests | WP-006 |

```
###  Path: `/docs/agents/implementation-history/2026-03-13-api-caching-core/work/WP-001.md`

```md
# WP-001: Cache Strategy Interface & Implementations

**Plan Steps:** 1, 2, 3  
**Dependencies:** None  
**Assigned to:** Developer  

---

## Description

Create the foundational cache strategy abstraction: an interface that defines how a cache entry's validity is evaluated, plus two concrete implementations (time-based TTL and manual-only). These strategies are consumed by the cacheable trait (WP-003) to determine whether a cached response is still valid.

## Requirements

### 1. `APICacheStrategyInterface`

**File:** `src/classes/Application/API/Cache/APICacheStrategyInterface.php`  
**Namespace:** `Application\API\Cache`

Methods:
- `getID(): string` — unique strategy identifier (e.g. `'FixedDuration'`, `'ManualOnly'`).
- `isCacheFileValid(JSONFile $cacheFile): bool` — given a cache file, return whether it is still valid.

### 2. `FixedDurationStrategy`

**File:** `src/classes/Application/API/Cache/Strategies/FixedDurationStrategy.php`  
**Namespace:** `Application\API\Cache\Strategies`

- Duration constants: `DURATION_1MIN`, `DURATION_5MIN`, `DURATION_15MIN`, `DURATION_1HOUR`, `DURATION_6HOURS`, `DURATION_12HOURS`, `DURATION_24HOURS`.
- Constructor: accepts `int $durationInSeconds`.
- `isCacheFileValid()`: compare `filemtime()` of the cache file against the current time minus the duration. Return `true` if the file is younger than the duration.
- `getID()`: returns `'FixedDuration'`.

Design reference: `src/classes/Application/AI/Cache/Strategies/FixedDurationStrategy.php` (existing AI cache strategy with similar logic).

### 3. `ManualOnlyStrategy`

**File:** `src/classes/Application/API/Cache/Strategies/ManualOnlyStrategy.php`  
**Namespace:** `Application\API\Cache\Strategies`

- `isCacheFileValid()`: always returns `true`.
- `getID()`: returns `'ManualOnly'`.
- Cache entries using this strategy are only invalidated by explicit `invalidateCache()` calls.

## Technical Constraints

- Use `declare(strict_types=1)`.
- Use `array()` syntax, not `[]`.
- No PHP enums, no `readonly` properties.
- Use `AppUtils\FileHelper\JSONFile` for file type hints (existing dependency).

## Acceptance Criteria

- [ ] `APICacheStrategyInterface` defines `getID()` and `isCacheFileValid(JSONFile)`.
- [ ] `FixedDurationStrategy` correctly validates based on file modification time vs. TTL duration.
- [ ] `FixedDurationStrategy` exposes all 7 duration constants.
- [ ] `ManualOnlyStrategy::isCacheFileValid()` always returns `true`.
- [ ] All files use correct namespaces and `declare(strict_types=1)`.

```
###  Path: `/docs/agents/implementation-history/2026-03-13-api-caching-core/work/WP-002.md`

```md
# WP-002: Cache Manager

**Plan Step:** 5  
**Dependencies:** None  
**Assigned to:** Developer  

---

## Description

Create the static `APICacheManager` utility class that manages the file system layout for cached API responses. It provides folder resolution, per-method invalidation, global clearing, and size reporting. This manager is consumed by both the cacheable trait (WP-003) and the CacheControl integration (WP-005).

## Requirements

**File:** `src/classes/Application/API/Cache/APICacheManager.php`  
**Namespace:** `Application\API\Cache`

### Methods

- `getCacheFolder(): FolderInfo` — returns `{APP_STORAGE}/api/cache/` using `Application::getStorageSubfolderPath()`.
- `getMethodCacheFolder(string $methodName): FolderInfo` — returns `{APP_STORAGE}/api/cache/{MethodName}/`.
- `invalidateMethod(string $methodName): void` — deletes the method's entire subdirectory. Use `FolderInfo::delete()` (or equivalent safe folder deletion). No-op if the folder does not exist.
- `clearAll(): void` — deletes the entire cache folder. The folder is not immediately recreated; it is lazily recreated on the next `getCacheFolder()` call via `Application::getStorageSubfolderPath()`. No-op if the folder does not exist.
- `getCacheSize(): int` — returns total byte size of all files in the cache folder. Returns `0` if the folder does not exist.

### Storage Layout

```
{APP_STORAGE}/
  api/
    cache/
      {MethodName}/
        {hash}.json
        {hash}.json
      {AnotherMethod}/
        {hash}.json
```

## Technical Constraints

- Use `declare(strict_types=1)`.
- Use `array()` syntax, not `[]`.
- No PHP enums, no `readonly` properties.
- Use `FolderInfo::factory()` for all folder operations, `JSONFile::factory()` for JSON files. Never use raw `file_get_contents` or `file_put_contents`.
- All methods are static.

## Acceptance Criteria

- [ ] `getCacheFolder()` returns the correct path under application storage.
- [ ] `invalidateMethod()` deletes only the targeted method's subfolder and does not affect others.
- [ ] `clearAll()` removes all cached data.
- [ ] `getCacheSize()` accurately reports total byte size.
- [ ] The class handles the case where the cache folder does not yet exist (no errors).

```
###  Path: `/docs/agents/implementation-history/2026-03-13-api-caching-core/work/WP-003.md`

```md
# WP-003: Cacheable API Method Interface & Trait

**Plan Steps:** 4, 6  
**Dependencies:** WP-001, WP-002  
**Assigned to:** Developer  

---

## Description

Create the interface and trait that API methods implement to opt into caching. The interface extends `APIMethodInterface` and defines the cache contract. The trait provides the default implementation for cache key generation, file resolution, read/write, and invalidation — delegating to the strategy (WP-001) for validity checks and to the manager (WP-002) for file system operations.

## Requirements

### 1. `CacheableAPIMethodInterface`

**File:** `src/classes/Application/API/Cache/CacheableAPIMethodInterface.php`  
**Namespace:** `Application\API\Cache`  
**Extends:** `Application\API\APIMethodInterface`

Methods:
- `getCacheStrategy(): APICacheStrategyInterface` — returns the caching strategy for this method (implementing class defines this).
- `getCacheKeyParameters(): array` — returns the parameters that should be included in the cache key hash (implementing class defines this).
- `getCacheKey(string $version): string` — builds a deterministic hash from method name + version + sorted parameter values.
- `readFromCache(string $version): ?array` — checks for a valid cache file and returns parsed data, or `null` on miss/expiry.
- `writeToCache(string $version, array $data): void` — writes response data to the cache file.
- `invalidateCache(): void` — invalidates all cached entries for this method.

### 2. `CacheableAPIMethodTrait`

**File:** `src/classes/Application/API/Cache/CacheableAPIMethodTrait.php`  
**Namespace:** `Application\API\Cache`

Provides default implementations for all interface methods **except** `getCacheStrategy()` and `getCacheKeyParameters()` (which the consuming class must define).

#### `getCacheKey(string $version): string`
- Collect: method name (`$this->getMethodName()` or equivalent), `$version`, sorted `getCacheKeyParameters()` values.
- Concatenate and hash with `md5()` (or `sha256` — follow the pattern from the AI cache if one exists).
- Return the hash string.

#### `getCacheFile(string $version): JSONFile`
- Private/protected helper (not on the interface).
- Returns `JSONFile::factory('{cache}/{MethodName}/{hash}.json')` using `APICacheManager::getMethodCacheFolder()` and `getCacheKey()`.

#### `readFromCache(string $version): ?array`
1. Get cache file via `getCacheFile()`.
2. If file does not exist → return `null`.
3. Call `$this->getCacheStrategy()->isCacheFileValid($cacheFile)`.
4. If invalid → return `null`.
5. Parse and return the JSON data.

#### `writeToCache(string $version, array $data): void`
1. Get cache file via `getCacheFile()`.
2. Ensure parent directory exists.
3. Write data via `JSONFile::putData()`.

#### `invalidateCache(): void`
- Delegate to `APICacheManager::invalidateMethod($this->getMethodName())` (or equivalent method name accessor).

## Technical Constraints

- Use `declare(strict_types=1)`.
- Use `array()` syntax, not `[]`.
- No PHP enums, no `readonly` properties.
- The trait assumes `$this` implements both `CacheableAPIMethodInterface` and `APIMethodInterface` (it will be used inside API method classes that extend `BaseAPIMethod`).
- Use `JSONFile::factory()` for file operations.

## Acceptance Criteria

- [ ] `CacheableAPIMethodInterface` extends `APIMethodInterface`.
- [ ] `CacheableAPIMethodTrait` provides implementations for `getCacheKey()`, `readFromCache()`, `writeToCache()`, `invalidateCache()`.
- [ ] `getCacheKey()` is deterministic: same inputs always produce the same hash.
- [ ] `getCacheKey()` produces different hashes when version or parameters differ.
- [ ] `readFromCache()` returns `null` when cache file is missing or expired.
- [ ] `readFromCache()` returns data array when cache file is valid.
- [ ] `writeToCache()` creates the cache file with correct data.
- [ ] `invalidateCache()` delegates to `APICacheManager::invalidateMethod()`.

```
###  Path: `/docs/agents/implementation-history/2026-03-13-api-caching-core/work/WP-004.md`

```md
# WP-004: BaseAPIMethod Cache Integration

**Plan Step:** 7  
**Dependencies:** WP-003  
**Assigned to:** Developer  

---

## Description

Modify `BaseAPIMethod::_process()` to integrate cache checking and writing for API methods that implement `CacheableAPIMethodInterface`. This is the core integration point: on a cache hit, the method short-circuits via `sendSuccessResponse()` (typed `never`); on a miss, the response is written to cache before the final send.

## Requirements

**File:** `src/classes/Application/API/BaseMethods/BaseAPIMethod.php`  
**Modification:** `_process()` method

### Changes

1. **Add import:** `use Application\API\Cache\CacheableAPIMethodInterface;` at the top of the file.

2. **Cache check (after validation):** After `$version = $this->getActiveVersion();`, insert:
   ```php
   if ($this instanceof CacheableAPIMethodInterface) {
       $cached = $this->readFromCache($version);
       if ($cached !== null) {
           $this->sendSuccessResponse(ArrayDataCollection::create($cached));
       }
   }
   ```
   Because `sendSuccessResponse()` is typed `never`, a cache hit halts execution — no `return` needed.

3. **Cache write (before final response):** Before the final `$this->sendSuccessResponse($response);`, insert:
   ```php
   if ($this instanceof CacheableAPIMethodInterface) {
       $this->writeToCache($version, $response->getData());
   }
   ```

### Assumption to verify

- `ArrayDataCollection::create()` accepts an associative array to pre-populate the collection. If the signature differs, adapt the cache hit path accordingly (e.g., `ArrayDataCollection::create()->setKeys($cached)` or similar).

### Execution Flow (after modification)

```
validate()
getActiveVersion()
→ [CACHE CHECK: if cacheable, read cache → hit? sendSuccessResponse(cached) → HALT]
collectRequestData()
collectResponseData()
→ [CACHE WRITE: if cacheable, writeToCache(response)]
sendSuccessResponse(response) → HALT
```

## Technical Constraints

- Do not alter the method signature or existing logic beyond the two insertions.
- The `instanceof` check is the standard pattern used for API trait-based feature detection (same as `DryRunAPIInterface`).
- Use `array()` syntax if any new arrays are created.

## Acceptance Criteria

- [ ] `_process()` contains the cache check block after `getActiveVersion()`.
- [ ] `_process()` contains the cache write block before `sendSuccessResponse()`.
- [ ] `CacheableAPIMethodInterface` import is added.
- [ ] Non-cacheable API methods are unaffected (the `instanceof` check gates both blocks).
- [ ] Cache hit short-circuits correctly via `sendSuccessResponse()` (typed `never`).

```
###  Path: `/docs/agents/implementation-history/2026-03-13-api-caching-core/work/WP-005.md`

```md
# WP-005: CacheControl System Integration

**Plan Steps:** 8, 9  
**Dependencies:** WP-002  
**Assigned to:** Developer  

---

## Description

Integrate the API response cache into the framework's CacheControl system so it appears in the admin cache management UI and can be cleared alongside other caches. This requires a cache location class and an event listener to register it.

## Requirements

### 1. `APIResponseCacheLocation`

**File:** `src/classes/Application/API/Cache/APIResponseCacheLocation.php`  
**Namespace:** `Application\API\Cache`  
**Extends:** `Application\CacheControl\BaseCacheLocation`

Methods:
- `getID(): string` → `'APIResponseCache'`
- `getLabel(): string` → `t('API Response Cache')` (localized label)
- `getByteSize(): int` → delegates to `APICacheManager::getCacheSize()`
- `clear(): void` → delegates to `APICacheManager::clearAll()`

### 2. `RegisterAPIResponseCacheListener`

**File:** `src/classes/Application/API/Events/RegisterAPIResponseCacheListener.php`  
**Namespace:** `Application\API\Events`  
**Extends:** `Application\CacheControl\Events\BaseRegisterCacheLocationsListener`

Method:
- `getCacheLocations(): array` → returns `array(new APIResponseCacheLocation())`

**Pattern reference:** Follow the existing `RegisterAPIIndexCacheListener` in `src/classes/Application/API/Events/` — it uses the same base class and pattern.

### Event Listener Discovery

The framework's event listener system automatically discovers classes extending `BaseRegisterCacheLocationsListener`. No manual registration is required. Verify this assumption matches the existing `RegisterAPIIndexCacheListener`.

## Technical Constraints

- Use `declare(strict_types=1)`.
- Use `array()` syntax, not `[]`.
- No PHP enums, no `readonly` properties.
- Use `t()` for the label string (framework localization function).

## Acceptance Criteria

- [ ] `APIResponseCacheLocation` extends `BaseCacheLocation` and delegates to `APICacheManager`.
- [ ] `RegisterAPIResponseCacheListener` extends `BaseRegisterCacheLocationsListener`.
- [ ] The listener returns the cache location in `getCacheLocations()`.
- [ ] `getByteSize()` returns > 0 after API responses have been cached.
- [ ] `clear()` removes all cached API responses.
- [ ] The cache location integrates with the CacheControl admin UI without manual registration.

```
###  Path: `/docs/agents/implementation-history/2026-03-13-api-caching-core/work/WP-006.md`

```md
# WP-006: Unit & Integration Tests

**Plan Steps:** 10, 11, 12  
**Dependencies:** WP-004, WP-005  
**Assigned to:** Developer  

---

## Description

Write unit and integration tests covering all new cache components. Run `composer dump-autoload` first to register the new classes with the classmap autoloader. Tests must verify strategy validity logic, cache key determinism, manager operations, end-to-end cache hit/miss behavior, and CacheControl integration.

## Requirements

### Pre-requisite

Run `composer dump-autoload` to register all 8 new files with the classmap autoloader.

### Unit Tests

**File:** `tests/AppFrameworkTests/API/Cache/APICacheStrategyTest.php`

#### Strategy Tests
- `FixedDurationStrategy::isCacheFileValid()` returns `true` for a file modified within the TTL window.
- `FixedDurationStrategy::isCacheFileValid()` returns `false` for a file older than the TTL duration.
- `ManualOnlyStrategy::isCacheFileValid()` always returns `true` regardless of file age.

#### Cache Key Tests
- `getCacheKey()` is deterministic: same method name + version + parameters → same hash.
- `getCacheKey()` varies when the version changes.
- `getCacheKey()` varies when parameter values change.
- `getCacheKey()` is order-independent for parameters (sorted internally).

#### Cache Manager Tests
- `APICacheManager::invalidateMethod()` deletes only the targeted method folder.
- `APICacheManager::invalidateMethod()` is a no-op when the folder does not exist.
- `APICacheManager::clearAll()` deletes all method folders.
- `APICacheManager::getCacheSize()` returns accurate byte counts.
- `APICacheManager::getCacheSize()` returns `0` when no cache exists.

### Integration Tests

**File:** `tests/AppFrameworkTests/API/Cache/APICacheIntegrationTest.php`

These tests require a **test stub API method** that implements `CacheableAPIMethodInterface` with `CacheableAPIMethodTrait`, using a `FixedDurationStrategy`.

#### End-to-End Cache Tests
- Call `processReturn()` twice — the second call returns the same data from cache (verify the cache file exists).
- Call `processReturn()`, then `invalidateCache()`, then `processReturn()` — the second call computes fresh data.
- Verify the cache file is written to the expected path: `{APP_STORAGE}/api/cache/{MethodName}/{hash}.json`.

#### CacheControl Integration Tests
- `APIResponseCacheLocation::getByteSize()` returns > 0 after caching.
- `APIResponseCacheLocation::clear()` removes all cached responses.

### Test Stub Location

Place the test stub API method in the test classes directory (e.g., `tests/AppFrameworkTestClasses/API/` or alongside the test files — follow existing test stub patterns in the project).

## Technical Constraints

- Use `declare(strict_types=1)`.
- Use `array()` syntax, not `[]`.
- Follow existing test base class patterns (check `tests/AppFrameworkTests/` for the project's PHPUnit base class).
- Run tests with `composer test-file -- <path>` or `composer test-filter -- <pattern>`. **Never run the full test suite.**

## Acceptance Criteria

- [ ] `composer dump-autoload` completes successfully with all new files indexed.
- [ ] All unit tests pass for strategies, cache keys, and cache manager.
- [ ] Integration tests confirm end-to-end cache hit/miss/invalidation behavior.
- [ ] CacheControl integration tests verify size reporting and clearing.
- [ ] Tests follow the project's existing test patterns and conventions.

```
###  Path: `/docs/agents/implementation-history/2026-03-13-api-caching-core/work/WP-007.md`

```md
# WP-007: Static Analysis Verification

**Plan Step:** 13  
**Dependencies:** WP-006  
**Assigned to:** Developer  

---

## Description

Run PHPStan static analysis to verify that all new code passes without introducing new errors. This is the final validation step before the implementation is considered complete.

## Requirements

1. Run `composer analyze` (the project's PHPStan wrapper).
2. Verify that no new errors are introduced by the 8 new files or the `BaseAPIMethod.php` modification.
3. If errors are found, fix them and re-run until clean.

## Technical Constraints

- **Never invoke `vendor/bin/phpstan` directly** — always use `composer analyze`.
- Use `composer analyze-clear` if the result cache needs to be reset.

## Acceptance Criteria

- [ ] `composer analyze` passes with no new errors attributable to the API caching code.
- [ ] Any pre-existing errors are documented but not introduced by this change.

```
###  Path: `/docs/agents/implementation-history/2026-03-13-api-caching-dbhelper-invalidation/plan.md`

```md
# Plan: API Response Caching — DBHelper Automatic Invalidation

**Series:** API Caching System (Plan 2 of 3)  
**Project:** Application Framework  
**Depends on:** Plan 1 (Core Caching Infrastructure)  
**Blocked by:** Plan 1 must be completed first  
**Reference:** [/docs/agents/projects/api-caching-system.md](/docs/agents/projects/api-caching-system.md)

---

## Summary

Extend the API caching system (delivered in Plan 1) with a third cache strategy — `DBHelperAwareStrategy` — that automatically invalidates cached API responses when the underlying DBHelper collections change. An `APICacheInvalidationManager` scans all cacheable API methods at boot time, identifies those using DBHelper-aware caching, and wires `onAfterCreateRecord`/`onAfterDeleteRecord` event listeners to trigger per-method cache invalidation. A fixed-duration TTL acts as a safety net for record updates (since `BaseRecord::save()` does not fire a collection-level event).

## Architectural Context

- **Plan 1 deliverables (prerequisite):** `APICacheStrategyInterface`, `FixedDurationStrategy`, `ManualOnlyStrategy`, `CacheableAPIMethodInterface`/`CacheableAPIMethodTrait`, `APICacheManager`, modified `BaseAPIMethod::_process()`.
- **DBHelper collection events:** `DBHelper_BaseCollection` provides `onAfterCreateRecord(callable)` and `onAfterDeleteRecord(callable)` methods that register listeners. Events fire after `triggerAfterCreateRecord()` / `triggerAfterDeleteRecord()` in the collection. Event classes live in `src/classes/DBHelper/BaseCollection/Event/`.
- **Record save gap:** `BaseRecord::save()` does not fire a collection-level event — only record-level events. This means record updates cannot trigger automatic cache invalidation. The TTL safety net covers this.
- **API method discovery:** `APIManager::getInstance()->getMethodCollection()->getAll()` returns all registered API method instances for scanning.
- **Wiring point:** The invalidation manager's `registerListeners()` must run once during application setup, after API methods are registered but before request processing.

## Approach / Architecture

1. **`DBHelperAwareStrategy`** extends `FixedDurationStrategy` (inherits TTL validity) and adds a list of `DBHelper_BaseCollection` class-strings that the API method depends on.
2. **`APICacheInvalidationManager`** is a static utility that:
   - Scans all API methods for `CacheableAPIMethodInterface` implementations using `DBHelperAwareStrategy`.
   - Builds a reverse map: collection class → method names that depend on it.
   - Registers `onAfterCreateRecord` and `onAfterDeleteRecord` listeners on each collection to call `APICacheManager::invalidateMethod()` for all dependent methods.
3. **Wiring:** Lazy initialization in `APIManager::process()` — call `APICacheInvalidationManager::registerListeners()` once on first API request. This keeps it automatic and requires no application-level changes.

## Rationale

- **Extends `FixedDurationStrategy`** rather than composing it — the TTL safety net is always needed because record updates aren't covered by collection events. Inheritance avoids duplicating the `isCacheFileValid()` logic.
- **Lazy registration in `APIManager::process()`** avoids wiring overhead on non-API requests and requires zero application bootstrap changes.
- **Reverse map (collection → methods)** ensures each collection only gets one set of listeners regardless of how many methods depend on it, and the invalidation callback is efficient.

## Detailed Steps

### Step 1: Create `DBHelperAwareStrategy`

Create `src/classes/Application/API/Cache/Strategies/DBHelperAwareStrategy.php`:

- Extends `FixedDurationStrategy`
- Constructor: `array $collectionClasses, int $durationInSeconds = self::DURATION_24_HOURS`
- Stores `class-string<\DBHelper_BaseCollection>[]`
- `getID()` → `'dbhelper_aware'`
- `getCollectionClasses(): array` — returns the stored collection classes
- Inherits `isCacheFileValid()` from parent (TTL safety net)

See the project document for the full implementation.

### Step 2: Create `APICacheInvalidationManager`

Create `src/classes/Application/API/Cache/APICacheInvalidationManager.php`:

**`registerListeners(): void`** (public static):
1. Call `collectBindings()` to build the reverse map.
2. For each collection class in the map:
   - Instantiate the collection: `new $collectionClass()`
   - Create an invalidator closure that calls `APICacheManager::invalidateMethod()` for each dependent method name.
   - Register the closure on `onAfterCreateRecord()` and `onAfterDeleteRecord()`.

**`collectBindings(): array`** (private static):
1. Get all API methods via `APIManager::getInstance()->getMethodCollection()->getAll()`.
2. Filter to `CacheableAPIMethodInterface` instances.
3. Filter those to instances with `DBHelperAwareStrategy`.
4. Build map: `array<class-string<DBHelper_BaseCollection>, string[]>`.

### Step 3: Wire into `APIManager::process()`

Modify `src/classes/Application/API/APIManager.php`:
1. Add a private static property: `private static bool $invalidationListenersRegistered = false;`
2. At the top of `process()`, before method resolution, add:
   ```php
   if (!self::$invalidationListenersRegistered) {
       self::$invalidationListenersRegistered = true;
       APICacheInvalidationManager::registerListeners();
   }
   ```
3. Add the import: `use Application\API\Cache\APICacheInvalidationManager;`

### Step 4: Run `composer dump-autoload`

Two new files need to be indexed in the classmap.

### Step 5: Write unit tests

Create `tests/AppFrameworkTests/API/Cache/DBHelperAwareStrategyTest.php`:

- `DBHelperAwareStrategy` returns correct strategy ID (`'dbhelper_aware'`)
- `getCollectionClasses()` returns the classes passed to the constructor
- `isCacheFileValid()` behaves like `FixedDurationStrategy` (inherits TTL logic)
- Default TTL is 24 hours

### Step 6: Write integration tests

Create or extend `tests/AppFrameworkTests/API/Cache/APICacheInvalidationTest.php`:

- Create a test API method stub using `DBHelperAwareStrategy` with a test collection class.
- Call `processReturn()` to populate the cache.
- Trigger a create on the test collection.
- Verify the cache was invalidated (next `processReturn()` recomputes).
- Same for delete on the test collection.
- Verify that methods using other strategies are not affected by collection events.

**Note:** Integration tests will need a test collection class. Check if one already exists in `tests/AppFrameworkTestClasses/` or create a minimal stub. The existing test application in `tests/application/` likely has test collections available.

### Step 7: Run static analysis

Run `composer analyze` to verify PHPStan passes.

## Dependencies

- **Plan 1 deliverables:** `APICacheStrategyInterface`, `FixedDurationStrategy`, `CacheableAPIMethodInterface`, `APICacheManager`, modified `BaseAPIMethod::_process()`.
- `Application\API\APIManager` (existing class to modify)
- `DBHelper_BaseCollection::onAfterCreateRecord()` / `onAfterDeleteRecord()` (existing event methods)

## Required Components

**New files (2):**

| # | File | Type |
|---|---|---|
| 1 | `src/classes/Application/API/Cache/Strategies/DBHelperAwareStrategy.php` | Class |
| 2 | `src/classes/Application/API/Cache/APICacheInvalidationManager.php` | Class (static) |

**Modified files (1):**

| File | Change |
|---|---|
| `src/classes/Application/API/APIManager.php` | Add lazy `registerListeners()` call in `process()`, add import, add static flag |

**New test files (~2):**

| File | Type |
|---|---|
| `tests/AppFrameworkTests/API/Cache/DBHelperAwareStrategyTest.php` | Unit tests |
| `tests/AppFrameworkTests/API/Cache/APICacheInvalidationTest.php` | Integration tests |

## Assumptions

- `APIManager::getInstance()->getMethodCollection()->getAll()` returns instances of the API method classes (not metadata), allowing `instanceof` checks and `getCacheStrategy()` calls.
- Test collections exist in the framework test application or can be created as stubs for integration testing.
- Instantiating a collection class via `new $collectionClass()` is valid for all DBHelper collections intended for use with this strategy.

## Constraints

- All new files must use `declare(strict_types=1)`.
- All array creation must use `array()` syntax, never `[]`.
- No PHP enums, no `readonly` properties.
- Run `composer dump-autoload` after creating files.
- The `registerListeners()` call must be idempotent (guarded by a flag) since `process()` may be called multiple times in tests.

## Out of Scope

- **Record update invalidation** — `BaseRecord::save()` doesn't fire a collection event. The TTL safety net covers this. A future enhancement could add record-level event support.
- **Per-record granular invalidation** — only per-method invalidation is supported (all parameter combinations for a method are cleared).
- **HCP Editor API method conversion** — covered in Plan 3.

## Acceptance Criteria

- [ ] `DBHelperAwareStrategy` created with correct inheritance from `FixedDurationStrategy`.
- [ ] `APICacheInvalidationManager::registerListeners()` correctly wires collection events to method cache invalidation.
- [ ] Creating a record in a watched collection invalidates the dependent method's cache.
- [ ] Deleting a record in a watched collection invalidates the dependent method's cache.
- [ ] Methods using other strategies (FixedDuration, ManualOnly) are not affected by collection events.
- [ ] The wiring in `APIManager::process()` runs exactly once (idempotent flag).
- [ ] All unit and integration tests pass via `composer test-file`.
- [ ] `composer analyze` passes with no new errors.

## Testing Strategy

- **Unit tests** for `DBHelperAwareStrategy`: correct ID, collection classes getter, inherited TTL behavior.
- **Integration tests** using test stubs: verify that collection create/delete events trigger cache invalidation for dependent methods, and do not affect non-dependent methods.
- Run with `composer test-file` or `composer test-filter`. Never run the full test suite.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **`getAll()` returns lazy proxies that don't support `instanceof`** | Verify the return type of `getMethodCollection()->getAll()` before implementing. If proxies are returned, adapt to use metadata-based detection. |
| **Collection instantiation has side effects** | The `new $collectionClass()` call is standard DBHelper usage. If specific collections require constructor arguments, the strategy should document this constraint. |
| **Listener registration overhead on first request** | The scan runs once (guarded by static flag) and only iterates registered API methods. Overhead is negligible. |
| **Record updates not caught by events** | Documented and accepted. The TTL safety net (default 24h) ensures stale data is bounded. Consider adding record-level events as a future enhancement. |

```
###  Path: `/docs/agents/json-handling.md`

```md
# JSON Handling

## Converting variables to JSON

Use the `JSONConverter::var2json` method to convert variables to JSON format.
This throws an exception on failure, which contains useful information. 
This makes it a much better alternative to PHP's built-in `json_encode()` function.

```php
use AppUtils\ConvertHelper\JSONConverter;

$var = [
    'name' => 'John Doe',
    'age' => 30,
    'is_member' => true,
    'preferences' => [
        'colors' => ['red', 'green', 'blue'],
        'notifications' => false
    ]
];

$json = JSONConverter::var2json($var);

// JSON parameters can be passed as the second argument
$jsonPretty = JSONConverter::var2json($var, JSON_PRETTY_PRINT);

// Variant that fails silently and returns an empty string on error
$jsonSilent = JSONConverter::var2jsonSilent($var, JSON_THROW_ON_ERROR);
```

## Converting JSON to Array

Using associative arrays when decoding JSON is the standard in the
Application Framework, and must be preferred over objects.

```php
use AppUtils\ConvertHelper\JSONConverter;

$jsonAssoc = '{"name":"John Doe","age":30,"is_member":true,"preferences":{"colors":["red","green","blue"],"notifications":false}}';

$assoc = JSONConverter::json2array($jsonAssoc);

// Variant that fails silently and returns an empty array on error
$assocSilent = JSONConverter::json2arraySilent($jsonAssoc);
```

## Converting JSON to Mixed

Use the `JSONConverter::json2var` method to convert JSON strings back to PHP variables.

**Note:** Valid JSON can be objects, arrays, or primitive values (numbers, strings, booleans, null).
When decoding primitive JSON values, the method returns the corresponding PHP type directly.

```php
use AppUtils\ConvertHelper\JSONConverter;

// Convert to an object (default behavior)
$object = JSONConverter::json2var('{"name":"John Doe","age":30,"is_member":true}');

// JSON can also be primitive values
$number = JSONConverter::json2var('10'); // Returns integer: 10
$float = JSONConverter::json2var('10.5'); // Returns float: 10.5
$string = JSONConverter::json2var('"hello"'); // Returns string: "hello"
$boolean = JSONConverter::json2var('true'); // Returns boolean: true
$null = JSONConverter::json2var('null'); // Returns NULL
```


```
###  Path: `/docs/agents/plans/2026-02-09-upgrade-helper-work.md`

```md
# Work Packages: v7.0.0 Upgrade Documentation & Helper Script

**Plan Reference**: [2026-02-09-upgrade-helper.md](2026-02-09-upgrade-helper.md)  
**Date Created**: 2026-02-09  
**Status**: Ready for Implementation  
**Total Estimated Effort**: 13-20 hours

## Overview

This document breaks down the v7.0.0 upgrade documentation project into distinct, incrementally implementable work packages. Each package is self-contained with all necessary context for implementation, even if picked up weeks or months later.

## Context Summary

**What**: Create comprehensive upgrade guide and automated scanner for Application Framework v7.0.0 "Breaking-XXL" release.

**Why**: v7.0.0 contains systematic breaking changes (class relocations, namespace additions, admin screen system overhaul, events refactoring) requiring detailed migration documentation.

**Key Changes in v7.0.0**:
- Classes moved into thematically organized folders with PHP namespaces
- Admin screens now dynamically loaded via `RegisterAdminScreenFolders` event
- Offline events refactored with auto-discovery
- Deprecated classes maintain backward compatibility temporarily
- One database change: `2025-12-19-app-sets.sql`

**Key Files**:
- Source: `/Users/smordziol/Webserver/libraries/application-framework/changelog.md`
- Source: `/Users/smordziol/Webserver/libraries/application-framework/src/classes/_deprecated/`
- Template: `/Users/smordziol/Webserver/libraries/application-framework/docs/upgrade-guides/upgrade-guide-v5.5.0.md`
- Output: `/Users/smordziol/Webserver/libraries/application-framework/docs/upgrade-guides/upgrade-guide-v7.0.0.md`
- Script: `/Users/smordziol/Webserver/libraries/application-framework/tools/upgrade-to-v7.php`

---

## Work Package 1: Extract Complete Class Mapping Database

**Objective**: Create a comprehensive reference table of all deprecated class mappings (old → new) for use in documentation and scanner script.

**Status**: 🔲 Not Started  
**Estimated Effort**: 2-3 hours  
**Dependencies**: None  
**Priority**: HIGH (Required for all other packages)

### Context

v7.0.0 moved 50+ classes from root folder into organized subfolders with namespacing. Deprecated wrapper classes maintain backward compatibility. Need complete mapping for:
1. Upgrade guide reference table
2. Scanner script's detection database
3. Migration example generation

### Information Sources

1. **Changelog commits**: `/Users/smordziol/Webserver/libraries/application-framework/changelog.md`
   - Search for: "Moved", "Renamed", "Relocated"
   - Lines approximately 1-500 contain v7.0.0 changes

2. **Deprecated classes folder**: `/Users/smordziol/Webserver/libraries/application-framework/src/classes/_deprecated/`
   - Each file contains `@deprecated` tag pointing to new location
   - Example: `Application_Exception` → `Application\Exception\ApplicationException`

3. **Git history** (if available):
   ```bash
   cd /Users/smordziol/Webserver/libraries/application-framework
   git log --oneline --all --grep="Moved" --since="2025-01-01"
   git log --oneline --all --grep="Renamed" --since="2025-01-01"
   git diff HEAD~100..HEAD --name-status | grep "^R"
   ```

### Tasks

1. **Scan deprecated classes folder**
   - Read all PHP files in `src/classes/_deprecated/`
   - Extract class name from `class ClassName`
   - Extract new location from `@deprecated Use {@see \New\Namespace\ClassName}`
   - Record namespace, type (class/interface/trait)

2. **Parse changelog entries**
   - Extract "Moved X to Y" entries from `changelog.md`
   - Cross-reference with deprecated folder findings
   - Note any classes mentioned but not deprecated

3. **Categorize mappings**
   - **Core classes**: Exceptions, Interfaces, Utils, AppFolder
   - **Media library**: Media, MediaCollection, events
   - **Admin screens**: Base classes, area classes
   - **Session**: Session classes, events
   - **Events**: Offline events, listeners
   - **UI**: UI, renderable classes
   - **Other**: Miscellaneous utilities

4. **Create structured data file**
   - Format: Markdown table or JSON
   - Columns: Old Class | New Class | Namespace | Type | Category | Priority
   - Priority: HIGH (commonly used), MEDIUM, LOW
   - Save to: `docs/upgrade-guides/v7.0.0-class-mappings.md` or `.json`

### Example Entry Format

**Markdown**:
```markdown
| Old Class | New Class | Namespace | Type | Category | Priority |
|-----------|-----------|-----------|------|----------|----------|
| Application_Exception | ApplicationException | Application\Exception | class | Core | HIGH |
| Application_FilterSettings | FilterSettingsInterface | Application | interface | Core | HIGH |
```

**JSON**:
```json
{
  "Application_Exception": {
    "newClass": "ApplicationException",
    "fullNamespace": "Application\\Exception\\ApplicationException",
    "namespace": "Application\\Exception",
    "type": "class",
    "category": "Core",
    "priority": "HIGH",
    "usage": ["throw new", "extends"],
    "notes": "Primary exception class"
  }
}
```

### Acceptance Criteria

- [ ] At least 50 deprecated classes documented
- [ ] All entries include: old name, new name, full namespace, type
- [ ] Classes categorized by module/functionality
- [ ] Priority assigned to each (HIGH/MEDIUM/LOW based on usage frequency)
- [ ] Data saved in both human-readable (Markdown) and machine-readable (JSON) formats
- [ ] Cross-referenced with changelog entries
- [ ] Validated by checking that new classes actually exist in codebase

### Output Files

- `docs/upgrade-guides/v7.0.0-class-mappings.md` (human-readable reference)
- `docs/upgrade-guides/v7.0.0-class-mappings.json` (scanner script data)

### Notes

- Use `grep -r "@deprecated" src/classes/_deprecated/` to quickly find all deprecated classes
- Common patterns in deprecated files:
  ```php
  /**
   * @deprecated Use {@see \New\Namespace\ClassName} instead.
   */
  class Old_ClassName extends \New\Namespace\ClassName
  ```
- If a class is mentioned in changelog but not in `_deprecated/`, it may be a complete removal (not just relocation)

---

## Work Package 2: Create Upgrade Guide Structure & Overview

**Objective**: Create the upgrade guide document with complete structure, overview sections, and prerequisites.

**Status**: 🔲 Not Started  
**Estimated Effort**: 1-2 hours  
**Dependencies**: None (can run parallel to WP1)  
**Priority**: HIGH

### Context

Create the main upgrade guide document following the established pattern from previous upgrade guides. Sets foundation for all detailed content.

### Reference Documents

**Template**: `/Users/smordziol/Webserver/libraries/application-framework/docs/upgrade-guides/upgrade-guide-v5.5.0.md`

**Read sections**:
- Document structure (headers, sections)
- Overview format and tone
- Prerequisites format
- Database updates format
- Step-by-step guide structure

### Tasks

1. **Create document file**
   - Path: `/Users/smordziol/Webserver/libraries/application-framework/docs/upgrade-guides/upgrade-guide-v7.0.0.md`
   - Copy header structure from v5.5.0 guide
   - Update version references to v7.0.0

2. **Write Overview section**
   - Brief summary of v7.0.0 scope
   - Major changes summary (3-5 bullet points)
   - Version compatibility (upgrading from v6.x)
   - Reference to "Breaking-XXL" nature
   - Link to changelog for full details

3. **Write Prerequisites section**
   - PHP version requirements (check `composer.json`)
   - Database requirements (MySQL/MariaDB versions)
   - Required tools (Git, Composer, etc.)
   - Backup recommendations
   - Estimated migration time ranges (by application size)
   - Testing environment recommendation

4. **Write Database Updates section**
   - List required SQL script: `2025-12-19-app-sets.sql`
   - Location: `docs/sql/2025-12-19-app-sets.sql`
   - Import instructions (command-line and GUI)
   - Verification steps
   - Backup recommendations

5. **Create section placeholders**
   - Breaking Changes (to be filled in WP3)
   - Step-by-Step Migration Guide (to be filled in WP4)
   - Testing Checklist (to be filled in WP4)
   - Common Issues and Solutions (to be filled in WP4)
   - Deprecation Timeline
   - Additional Resources
   - Version Compatibility
   - Support

6. **Write Deprecation Timeline section**
   - v7.0.0: Deprecated classes available with warnings
   - v7.1.0: Deprecated classes still available
   - v8.0.0: Deprecated classes will be removed (estimated Q3 2026)
   - Recommendation to migrate immediately

7. **Write Version Compatibility section**
   - Upgrading from: v6.0.0, v6.1.0, v6.1.1, v6.2.0, v6.3.0
   - Upgrading to: v7.0.0
   - PHP requirements from `composer.json`
   - Database version requirements

8. **Write Additional Resources section**
   - Link to `changelog.md`
   - Link to `docs/changelog-history/v6-changelog.md`
   - Framework documentation links
   - Contact information for support

9. **Write Support section**
   - Migration assistance process
   - Issue reporting
   - Where to get help

### Template Structure

```markdown
# Upgrade Guide: v7.0.0

> **Migration Complexity**: Breaking-XXL  
> **Estimated Time**: 2-6 hours depending on application size  
> **Last Updated**: 2026-02-09

## Overview

[2-3 paragraphs about v7.0.0 scope and changes]

**Major Changes**:
- Class reorganization with namespacing
- Admin screen system overhaul
- Offline events refactoring
- MCP/AI integration support
- Type safety improvements

See [changelog.md](../../changelog.md) for complete details.

## Prerequisites

### System Requirements
- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Composer 2.0+

### Before You Begin
1. **Create full backup** of application and database
2. **Set up testing environment** - do NOT upgrade production first
3. **Review this guide completely** before starting
4. **Allocate time**:
   - Small applications (< 10k LOC): 2-3 hours
   - Medium applications (10-50k LOC): 3-5 hours
   - Large applications (> 50k LOC): 5-6+ hours

## Database Updates

### Required SQL Scripts

Execute the following SQL script on your database:

**File**: `docs/sql/2025-12-19-app-sets.sql`  
**Purpose**: Creates AppSets feature database storage

**Command-line import**:
```bash
mysql -u username -p database_name < docs/sql/2025-12-19-app-sets.sql
```

**Verification**:
```sql
SHOW TABLES LIKE '%appsets%';
```

**Note**: This is the ONLY database change required for v7.0.0.

## Breaking Changes

[Placeholder - filled in WP3]

## Step-by-Step Migration Guide

[Placeholder - filled in WP4]

## Testing Checklist

[Placeholder - filled in WP4]

## Common Issues and Solutions

[Placeholder - filled in WP4]

## Deprecation Timeline

| Version | Status | Timeline |
|---------|--------|----------|
| v7.0.0 | Deprecated classes available with `@deprecated` warnings | Current |
| v7.1.0 | Deprecated classes still available | Q2 2026 |
| v8.0.0 | **Deprecated classes REMOVED** | Q3 2026 (estimated) |

**⚠️ Recommendation**: Migrate immediately to avoid breaking changes in v8.0.0 (6-12 months).

## Version Compatibility

**Upgrading From**: v6.0.0, v6.1.0, v6.1.1, v6.2.0, v6.3.0  
**Upgrading To**: v7.0.0  
**PHP Requirements**: PHP 8.0+ (PHP 8.1+ recommended)  
**Database**: MySQL 5.7+, MariaDB 10.2+

## Additional Resources

- **Detailed Changes**: [changelog.md](../../changelog.md)
- **v6 History**: [v6-changelog.md](../changelog-history/v6-changelog.md)
- **Framework Documentation**: [docs/](../README.md)
- **Agent Documentation**: [agents/](../agents/readme.md)

## Support

For migration assistance:
1. Review this guide thoroughly
2. Run automated scanner (see Work Package 5)
3. Check changelog for additional details
4. Contact framework maintainer

---

**Document Version**: 1.0  
**Created**: 2026-02-09  
**Applies To**: Application Framework v7.0.0
```

### Acceptance Criteria

- [ ] Document created at correct path
- [ ] Overview section complete and accurate
- [ ] Prerequisites clearly stated with version numbers
- [ ] Database update instructions complete and tested
- [ ] All major section placeholders created
- [ ] Deprecation timeline included
- [ ] Version compatibility table complete
- [ ] Follows format and tone of existing upgrade guides
- [ ] Markdown formatting valid

### Output Files

- `docs/upgrade-guides/upgrade-guide-v7.0.0.md` (partial - structure only)

---

## Work Package 3: Document Breaking Changes in Detail

**Objective**: Complete the "Breaking Changes" section of the upgrade guide with comprehensive details, examples, and migration instructions for each category.

**Status**: 🔲 Not Started  
**Estimated Effort**: 3-4 hours  
**Dependencies**: WP1 (class mappings), WP2 (document structure)  
**Priority**: HIGH

### Context

This is the core content of the upgrade guide. Documents each breaking change category with examples and migration paths. Uses class mapping data from WP1.

### Input Files

- Class mapping data: `docs/upgrade-guides/v7.0.0-class-mappings.md` (from WP1)
- Document structure: `docs/upgrade-guides/upgrade-guide-v7.0.0.md` (from WP2)
- Changelog reference: `changelog.md` (for detailed change descriptions)

### Tasks

#### Task 3.1: Class Locations and Namespaces Section

1. **Write overview**
   - Explain the reorganization pattern
   - Why namespaces were added
   - Impact on existing code

2. **Insert class mapping reference table**
   - Use top 30-40 most important classes from WP1 data
   - Format as markdown table
   - Group by category (Core, Media, Admin, Events, etc.)
   - Include status column (Deprecated, Relocated, etc.)

3. **Write migration steps**
   - How to update use statements
   - How to update class references
   - How to update type hints
   - IDE find/replace patterns

4. **Create code examples**
   - Minimum 5 before/after examples
   - Cover: Exceptions, Media library, Filter settings, UI classes
   - Show both old and new patterns
   - Include namespace import examples

**Example Structure**:
```markdown
### 1. Class Locations and Namespaces

#### Overview

The v7.0.0 release reorganizes classes from the root folder into thematically organized subfolders with proper PHP namespacing. This improves code organization, enables autoloading, and follows modern PHP standards.

**Pattern**: Old underscore-based pseudo-namespaces → Proper PHP namespaces

**Backward Compatibility**: All old class names remain available as deprecated wrappers but will be removed in v8.0.0.

#### Class Mapping Reference

##### Core Classes
| Old Class | New Class | Full Namespace | Status |
|-----------|-----------|----------------|--------|
| Application_Exception | ApplicationException | Application\Exception\ApplicationException | Deprecated |
| ... | ... | ... | ... |

##### Media Library
[Table of media classes]

##### Admin Screens
[Table of screen classes]

#### Migration Steps

1. **Search for deprecated class usage**
   - Use automated scanner (see Scanner Tool section)
   - Or manually search: `grep -r "Application_Exception" assets/classes/`

2. **Update namespace imports**
   ```php
   // Add at top of file
   use Application\Exception\ApplicationException;
   ```

3. **Update class references**
   - Replace old names with new names
   - Remove underscores from class names

4. **Update type hints**
   ```php
   // OLD
   public function handle(Application_Exception $e) {}
   
   // NEW
   public function handle(ApplicationException $e) {}
   ```

#### Code Examples

**Example 1: Exception Handling**
```php
// OLD
try {
    // code
} catch (Application_Exception $e) {
    throw new Application_Exception('Error: ' . $e->getMessage());
}

// NEW
use Application\Exception\ApplicationException;

try {
    // code
} catch (ApplicationException $e) {
    throw new ApplicationException('Error: ' . $e->getMessage());
}
```

[4+ more examples]
```

#### Task 3.2: Admin Screen System Migration Section

1. **Write overview**
   - Explain architectural change
   - Old: Fixed `Area` folder structure
   - New: Dynamic loading with event registration
   - Benefits of new approach

2. **Write migration steps**
   - Numbered steps for migrating screens
   - How to implement `getAdminScreensFolder()`
   - How to create event listener
   - How to register listener

3. **Create complete example**
   - Show old structure
   - Show new structure
   - Module class with `getAdminScreensFolder()`
   - Event listener implementation
   - Registration code

4. **List affected screens**
   - Which built-in screens moved
   - What application developers need to check

**Example Structure**:
```markdown
### 2. Admin Screen System Migration

#### Overview

Admin screens are now **dynamically loaded** instead of being tied to the fixed `Area` folder structure. Screens can now be placed alongside their modules for better organization.

**Old Approach**:
- Screens in fixed `/Area/` folder structure
- Hardcoded screen locations
- Manual sitemap maintenance

**New Approach**:
- Screens can be anywhere in codebase
- Dynamic loading by class name
- Register locations via `RegisterAdminScreenFolders` event
- Automatic sitemap generation

**Benefits**: Better code organization, screens next to their modules, easier maintenance.

#### Affected Screens

The following built-in screens were relocated:
- Users management → `Application\Admin\Screens\Users\`
- Media library → `Application\Media\Admin\Screens\`
- News central → `Application\Admin\Screens\News\`
- Developer screens → `Application\Admin\Screens\Developer\`

**Action Required**: If you have custom admin screens, follow migration steps below.

#### Migration Steps

[Detailed numbered steps]

#### Complete Example

[Full before/after code example]
```

#### Task 3.3: Offline Events System Section

1. **Write overview**
2. **Document required changes**
   - Extend `BaseOfflineEvent`
   - Implement `getEventName()`
   - Remove `wakeUp()` method
3. **Create before/after examples**
4. **List moved events**

#### Task 3.4: Media Library Changes Section

1. **Document class renames**
2. **Show migration examples**
3. **Note admin screen changes**

#### Task 3.5: Deprecated Screen Base Classes Section

1. **Create mapping table**
2. **Show migration examples**

### Acceptance Criteria

- [ ] All 5 breaking change categories documented
- [ ] Each category has clear overview
- [ ] Migration steps are detailed and actionable
- [ ] Minimum 10 total code examples (before/after)
- [ ] Class mapping reference table complete (30-40 entries)
- [ ] Examples are tested and accurate
- [ ] Language is clear and non-technical where possible
- [ ] Cross-references to other sections where appropriate

### Output Files

- `docs/upgrade-guides/upgrade-guide-v7.0.0.md` (updated - breaking changes section complete)

---

## Work Package 4: Write Step-by-Step Migration Guide & Testing

**Objective**: Complete the practical migration guide with phased approach, testing checklist, and common issues section.

**Status**: 🔲 Not Started  
**Estimated Effort**: 2-3 hours  
**Dependencies**: WP3 (breaking changes documented)  
**Priority**: HIGH

### Context

Provides actionable step-by-step process for upgrading, organized by phase with time estimates. Includes comprehensive testing checklist and troubleshooting guide.

### Tasks

#### Task 4.1: Step-by-Step Migration Guide

Create 7 phases with detailed tasks:

1. **Phase 1: Preparation**
   - Backup procedures
   - Scanner script download/setup
   - Report review
   - Scope estimation

2. **Phase 2: Database Updates**
   - SQL script execution
   - Verification
   - Rollback plan

3. **Phase 3: Class Reference Updates**
   - Priority-based approach (high-frequency classes first)
   - Find/replace patterns
   - Namespace imports
   - Testing after each batch

4. **Phase 4: Admin Screen Migration**
   - Screen identification
   - Optional relocation
   - Event listener creation
   - Registration
   - Cache clearing

5. **Phase 5: Event Listener Updates**
   - Custom event updates
   - Listener updates
   - `wakeUp()` removal
   - `getEventName()` implementation

6. **Phase 6: Testing**
   - Cache clearing
   - Functionality testing
   - Test suite execution
   - Smoke testing

7. **Phase 7: Cleanup**
   - Code cleanup
   - Documentation updates
   - Commit changes

**Format Each Phase**:
```markdown
### Phase X: Phase Name (Time Estimate)

**Goal**: [What this phase accomplishes]

**Steps**:
1. [Detailed step with commands/examples]
2. [Detailed step]
   ```bash
   # Example commands
   ```
3. [Detailed step]

**Verification**:
- [ ] Checkpoint 1
- [ ] Checkpoint 2

**Common Issues**: See [Common Issues](#common-issues-and-solutions)
```

#### Task 4.2: Testing Checklist

Create comprehensive checklist organized by:

1. **Critical Functionality**
   - Application boots
   - Database connection
   - Authentication
   - Admin access

2. **Admin Screens**
   - Default screens load
   - Custom screens accessible
   - Navigation complete
   - Forms work
   - Modes/tabs work

3. **Events System**
   - Session events fire
   - Media events fire
   - Custom events fire
   - Listeners execute

4. **Media Library**
   - Collection loads
   - Upload works
   - Editing works
   - Deletion works

5. **Application-Specific**
   - Template for custom tests

**Format**:
```markdown
## Testing Checklist

### Critical Functionality
- [ ] Application boots without errors
- [ ] Database connection successful
- [ ] User authentication works
- [ ] Admin area accessible
- [ ] No PHP warnings/notices in logs

### Admin Screens
- [ ] Dashboard loads
- [ ] Users screen accessible
- [ ] [etc.]

[Continue for all categories]

### Application-Specific Tests
Add your application-specific tests:
- [ ] _________________
- [ ] _________________
```

#### Task 4.3: Common Issues and Solutions

Document 8-10 common issues with solutions:

1. **"Class not found" errors**
   - Cause
   - Solution
   - Example

2. **Admin screen not appearing**
   - Cause
   - Solution
   - Example

3. **Events not firing**
4. **Interface not found errors**
5. **Cache-related errors**
6. **Namespace import errors**
7. **Type hint errors**
8. **Deprecated warning floods**

**Format**:
```markdown
## Common Issues and Solutions

### Issue: "Class 'Application_Exception' not found"

**Cause**: Deprecated class reference not updated and old class file missing.

**Solution**: 
1. Use upgrade scanner to find all usages
2. Update namespace imports
3. Update class references

**Example**:
```php
// Update from:
throw new Application_Exception('Error');

// To:
use Application\Exception\ApplicationException;
throw new ApplicationException('Error');
```

**See Also**: [Class Locations and Namespaces](#class-locations-and-namespaces)

[Continue for all issues]
```

### Acceptance Criteria

- [ ] 7 migration phases documented
- [ ] Each phase has clear goals, steps, verification
- [ ] Time estimates provided for each phase
- [ ] Testing checklist covers all critical areas
- [ ] Minimum 8 common issues documented
- [ ] Each issue has cause, solution, example
- [ ] Cross-references between sections
- [ ] Practical and actionable instructions

### Output Files

- `docs/upgrade-guides/upgrade-guide-v7.0.0.md` (updated - guide sections complete)

---

## Work Package 5: Create Automated Scanner Script

**Objective**: Build a CLI tool that scans application code for deprecated class usage and generates actionable migration reports.

**Status**: 🔲 Not Started  
**Estimated Effort**: 4-6 hours  
**Dependencies**: WP1 (class mappings - provides data source)  
**Priority**: MEDIUM (valuable but guide can exist without it)

### Context

Create PHP CLI script that helps developers identify deprecated class usage in their applications. Makes migration significantly easier, especially for large codebases.

### Script Location

`/Users/smordziol/Webserver/libraries/application-framework/tools/upgrade-to-v7.php`

### Features

1. **Recursive file scanning**: Scan all PHP files in target directory
2. **Class usage detection**: Find deprecated classes in various contexts (new, extends, implements, type hints, use statements)
3. **Admin screen detection**: Identify screens needing migration
4. **Event listener detection**: Identify event listeners needing updates
5. **Report generation**: Console, HTML, and JSON output formats
6. **Priority ranking**: Order findings by frequency/importance
7. **Progress indicator**: Show progress for large scans

### Script Structure

```php
#!/usr/bin/env php
<?php
/**
 * Application Framework v7.0.0 Upgrade Scanner
 * 
 * Scans application code for deprecated class usage and generates
 * migration reports.
 * 
 * Usage:
 *   php upgrade-to-v7.php /path/to/application
 *   php upgrade-to-v7.php /path/to/app --format=html --output=report.html
 *   php upgrade-to-v7.php /path/to/app --format=json --output=report.json
 */

// 1. Argument parsing
// 2. Class definitions
// 3. Main execution
// 4. Output formatting

class UpgradeScanner
{
    private array $classMap = [];
    private array $findings = [];
    private string $scanPath;
    private int $filesScanned = 0;
    
    // Methods:
    // - __construct()
    // - loadClassMap()
    // - scan()
    // - scanFile()
    // - detectClassUsage()
    // - detectAdminScreens()
    // - detectEventListeners()
    // - generateReport()
    // - formatConsole()
    // - formatHTML()
    // - formatJSON()
    // - prioritize()
}
```

### Tasks

#### Task 5.1: Core Scanner Implementation

1. **Create file with shebang and docblock**
2. **Implement argument parsing**
   - Path (required)
   - --format=console|html|json (optional, default: console)
   - --output=filename (optional)
   - --help flag

3. **Load class mapping data**
   - Read JSON file from WP1
   - Parse into lookup array
   - Include metadata (namespace, type, category, priority)

4. **Implement recursive file scanner**
   - Use `RecursiveDirectoryIterator`
   - Filter for `.php` files
   - Skip common ignore patterns (vendor/, node_modules/, cache/, etc.)
   - Show progress (files scanned / total)

5. **Implement class usage detector**
   - Regex patterns for:
     - `new ClassName(`
     - `extends ClassName`
     - `implements ClassName`
     - `use Full\ClassName;`
     - Type hints: `function foo(ClassName $var)`
     - Static calls: `ClassName::method()`
   - Capture file path and line number
   - Store in findings array

6. **Implement admin screen detector**
   - Look for patterns:
     - `extends Application_Admin_Area`
     - Files in `/Area/` folders
     - Classes extending deprecated screen base classes
   - Flag for migration

7. **Implement event listener detector**
   - Look for patterns:
     - `public function wakeUp(` (old pattern)
     - Files in `OfflineEvents/` folders
     - Classes without `getEventName()` method
   - Flag for migration

#### Task 5.2: Report Generation

1. **Console formatter**
   - ASCII table formatting
   - Color coding (if terminal supports)
   - Priority sections (High/Medium/Low)
   - File grouping
   - Summary statistics

2. **HTML formatter**
   - Bootstrap-based styling
   - Collapsible sections
   - Syntax highlighting
   - Searchable/filterable table
   - Export functionality

3. **JSON formatter**
   - Structured data output
   - Machine-readable
   - For CI/CD integration

4. **Priority ranking**
   - HIGH: 10+ usages or critical classes
   - MEDIUM: 3-9 usages
   - LOW: 1-2 usages
   - Sort by frequency within priority

#### Task 5.3: Testing and Refinement

1. **Create test fixtures**
   - Small PHP files with various deprecated usage patterns
   - Test all detection patterns
   - Verify accurate line numbers

2. **Test on real application**
   - Run on sample application using framework
   - Verify findings are accurate
   - Check for false positives
   - Refine patterns as needed

3. **Performance optimization**
   - Test on large codebases
   - Optimize file reading
   - Add caching if needed

4. **Error handling**
   - Handle invalid paths
   - Handle permission errors
   - Handle malformed PHP files
   - Graceful degradation

### Example Output

**Console Format**:
```
=================================================================
 Application Framework v7.0.0 Upgrade Scanner
=================================================================

Scanning: /path/to/application
Files scanned: 145/145 [========================================] 100%

Found 47 deprecated class usages in 23 files

PRIORITY 1: High-frequency deprecated classes (10+ usages)
-----------------------------------------------------------
Application_Exception (15 usages)
  /assets/classes/MyApp/Module.php:45
  /assets/classes/MyApp/Module.php:67
  /assets/classes/MyApp/Helper.php:23
  ...
→ Replacement: Use Application\Exception\ApplicationException
→ See: docs/upgrade-guides/upgrade-guide-v7.0.0.md#class-locations

Application_Media (12 usages)
  /assets/classes/MyApp/MediaHandler.php:12, 34, 56, 78
  ...
→ Replacement: Use Application\Media\Collection\MediaCollection

PRIORITY 2: Medium-frequency (3-9 usages)
------------------------------------------
[...]

PRIORITY 3: Low-frequency (1-2 usages)
---------------------------------------
[...]

Admin Screens Requiring Migration
----------------------------------
  /Area/MyModule/CustomScreen.php
    → Extends deprecated Application_Admin_Area_Mode_CollectionCreateScreen
    → Action: Update to DBHelper\Admin\Screens\Mode\BaseRecordCreateMode
    → See: docs/upgrade-guides/upgrade-guide-v7.0.0.md#admin-screens

Offline Events Requiring Migration
-----------------------------------
  /assets/classes/MyApp/Events/MyListener.php:45
    → Has wakeUp() method (deprecated pattern)
    → Action: Remove wakeUp(), implement getEventName()
    → See: docs/upgrade-guides/upgrade-guide-v7.0.0.md#offline-events

=================================================================
Summary
=================================================================
Deprecated classes found:    15 distinct classes
Total usages:                47 locations
Files affected:              23 files
Admin screens to migrate:     1 screen
Event listeners to update:    1 listener

Estimated migration effort:   3-4 hours

Next Steps:
1. Review this report carefully
2. Read: docs/upgrade-guides/upgrade-guide-v7.0.0.md
3. Start with HIGH priority items
4. Test after each batch of changes
=================================================================
```

### Acceptance Criteria

- [ ] Script executable from command line
- [ ] Scans directory recursively for PHP files
- [ ] Detects all deprecated class usage patterns (new, extends, implements, type hints, use)
- [ ] Detects admin screens needing migration
- [ ] Detects event listeners needing updates
- [ ] Generates console report with priorities
- [ ] Generates HTML report (optional)
- [ ] Generates JSON export (optional)
- [ ] Shows progress indicator for large scans
- [ ] Handles errors gracefully
- [ ] Performance acceptable (< 1 second per 100 files)
- [ ] Tested on real application
- [ ] Documented with --help flag
- [ ] No false positives in test cases

### Output Files

- `tools/upgrade-to-v7.php` (executable PHP script)
- `tools/upgrade-to-v7-README.md` (usage documentation)

### Testing Data

Create test directory: `tests/upgrade-scanner/fixtures/`

Sample files to test all patterns:
```php
// fixture-exceptions.php
class TestClass {
    public function test() {
        throw new Application_Exception('Test'); // Should detect
        try {
        } catch (Application_Exception $e) { // Should detect
        }
    }
}

// fixture-extends.php
class TestScreen extends Application_Admin_Area_Mode_CollectionCreateScreen { // Should detect
}

// fixture-typehints.php
class TestClass {
    public function handle(Application_Exception $e) { // Should detect
    }
}

// fixture-use.php
use Application_Exception; // Should detect

// fixture-events.php
class TestListener {
    public function wakeUp() { // Should detect (needs migration)
    }
}
```

---

## Work Package 6: Integration and Final Testing

**Objective**: Integrate all components, test on real application, refine based on findings.

**Status**: 🔲 Not Started  
**Estimated Effort**: 2-3 hours  
**Dependencies**: WP2, WP3, WP4, WP5 (all components complete)  
**Priority**: HIGH

### Context

Validate that the complete upgrade guide and scanner tool work together effectively on a real application upgrade.

### Tasks

#### Task 6.1: Test Application Setup

1. **Create or select test application**
   - Use existing application on framework v6.3.0
   - Or create minimal test application with:
     - Custom admin screens
     - Custom event listeners
     - Media library usage
     - Exception handling
     - FilterSettings implementation

2. **Establish baseline**
   - Document current functionality
   - Create automated tests if possible
   - Take full backup
   - Note all custom features

#### Task 6.2: Execute Upgrade Following Guide

1. **Run scanner script**
   - Execute on test application
   - Review report
   - Document findings
   - Note any unexpected results

2. **Follow upgrade guide step-by-step**
   - Execute each phase precisely as documented
   - Note any unclear instructions
   - Track actual time vs estimated time
   - Document any issues encountered

3. **Record all changes**
   - Keep log of modifications
   - Note helpful patterns
   - Identify pain points

#### Task 6.3: Validate Results

1. **Functional testing**
   - All features work as before upgrade
   - No new errors
   - No warnings in logs
   - Performance unchanged

2. **Code quality**
   - No deprecated class warnings
   - All namespaces correct
   - Admin screens accessible
   - Events firing correctly

3. **Scanner validation**
   - Re-run scanner on upgraded application
   - Should find zero deprecated usages
   - Verify accuracy

#### Task 6.4: Refine Documentation

1. **Update upgrade guide based on findings**
   - Clarify ambiguous instructions
   - Add missing steps
   - Improve examples
   - Adjust time estimates
   - Add discovered gotchas to Common Issues

2. **Update scanner script**
   - Fix any false positives
   - Add missing detection patterns
   - Improve error messages
   - Refine output formatting

3. **Update class mappings**
   - Add any missing classes discovered
   - Correct any errors
   - Improve categorization

### Acceptance Criteria

- [ ] Test application successfully upgraded from v6.3.0 to v7.0.0
- [ ] All functionality works post-upgrade
- [ ] Scanner tool identified all actual deprecated usages
- [ ] Zero false positives in scanner results
- [ ] Upgrade guide instructions are accurate and complete
- [ ] Time estimates are realistic
- [ ] Common issues section covers actual encountered problems
- [ ] Documentation refined based on real-world testing

### Output Files

- Updated `docs/upgrade-guides/upgrade-guide-v7.0.0.md`
- Updated `tools/upgrade-to-v7.php`
- Updated class mapping files
- Test application upgrade log (for reference)

---

## Work Package 7: Review, Polish, and Finalize

**Objective**: Final review, proofreading, consistency check, and formal release preparation.

**Status**: 🔲 Not Started  
**Estimated Effort**: 1-2 hours  
**Dependencies**: WP6 (testing complete)  
**Priority**: MEDIUM

### Context

Ensure documentation is production-ready with consistent formatting, accurate cross-references, and professional quality.

### Tasks

#### Task 7.1: Content Review

1. **Accuracy check**
   - Verify all version numbers (v6.x → v7.0.0)
   - Verify all file paths
   - Verify all commands work
   - Verify all code examples syntax
   - Test all cross-reference links

2. **Completeness check**
   - All breaking changes covered
   - All migration paths documented
   - All common issues addressed
   - All acceptance criteria from WP1-6 met

3. **Consistency check**
   - Terminology consistent throughout
   - Formatting consistent
   - Tone consistent
   - Example structure consistent

#### Task 7.2: Formatting and Polish

1. **Markdown validation**
   - Valid markdown syntax
   - Proper heading hierarchy
   - Code blocks properly formatted
   - Lists properly structured
   - Tables properly formatted

2. **Visual formatting**
   - Consistent use of bold, italic, code
   - Proper use of callouts/notes
   - Adequate spacing between sections
   - Syntax highlighting correct

3. **Grammar and clarity**
   - Proofread all text
   - Remove jargon where possible
   - Simplify complex sentences
   - Fix typos and grammar errors

#### Task 7.3: Cross-References and Links

1. **Internal links**
   - Verify all anchor links work
   - Add missing cross-references
   - Consistent link formatting

2. **External references**
   - Verify file paths exist
   - Verify referenced documentation exists
   - Update if files moved

3. **Navigation aids**
   - Table of contents (if needed)
   - "See also" references
   - Back-to-top links (if long document)

#### Task 7.4: Final Checklist

- [ ] All work packages 1-6 complete
- [ ] Document tested on real upgrade
- [ ] Scanner script tested and functional
- [ ] All code examples are valid PHP
- [ ] All file paths are accurate
- [ ] All cross-references work
- [ ] Markdown validates
- [ ] Grammar and spelling checked
- [ ] Formatting consistent
- [ ] No TODOs or placeholders remaining
- [ ] Version and date correct
- [ ] Contact/support information accurate

#### Task 7.5: Create Summary Document

Create `docs/upgrade-guides/README.md` or update existing:
- List all available upgrade guides
- Add v7.0.0 guide to list
- Link to scanner tool
- Note complexity/estimated time

### Acceptance Criteria

- [ ] All checklist items complete
- [ ] Document is professional quality
- [ ] No errors or inconsistencies
- [ ] Ready for public release
- [ ] Summary/index updated

### Output Files

- Final `docs/upgrade-guides/upgrade-guide-v7.0.0.md`
- Final `tools/upgrade-to-v7.php`
- Final class mapping files
- Updated `docs/upgrade-guides/README.md`

---

## Implementation Strategy

### Recommended Order

1. **Start with WP1 and WP2 in parallel** (both are foundational, no dependencies)
2. **Complete WP3** (needs class mappings from WP1, document structure from WP2)
3. **Complete WP4** (needs breaking changes from WP3)
4. **Complete WP5** (needs class mappings from WP1; can be parallel with WP3/WP4)
5. **Complete WP6** (needs all components)
6. **Complete WP7** (final polish)

### Incremental Delivery

Each work package produces usable output:
- **WP1**: Class mapping reference (useful immediately)
- **WP2**: Document structure (shows scope and outline)
- **WP3**: Breaking changes details (primary content)
- **WP4**: Migration guide (practical instructions)
- **WP5**: Scanner tool (high-value utility)
- **WP6**: Validated documentation
- **WP7**: Publication-ready materials

### Time Distribution

| Work Package | Hours | Percentage |
|--------------|-------|------------|
| WP1: Class Mappings | 2-3 | 15% |
| WP2: Structure | 1-2 | 10% |
| WP3: Breaking Changes | 3-4 | 25% |
| WP4: Migration Guide | 2-3 | 18% |
| WP5: Scanner Script | 4-6 | 32% |
| WP6: Testing | 2-3 | 18% |
| WP7: Polish | 1-2 | 10% |
| **Total** | **15-23** | **100%** |

### Parallelization Opportunities

- **WP1 + WP2**: Can run completely in parallel
- **WP5**: Can start after WP1, parallel to WP3/WP4
- **WP3 + WP4**: Sequential but can start WP4 sections that don't depend on WP3

### Session Planning

**Session 1** (3-4 hours):
- Complete WP1 (class mappings)
- Complete WP2 (document structure)
- Start WP3 (first 2 breaking change sections)

**Session 2** (3-4 hours):
- Complete WP3 (remaining breaking changes)
- Complete WP4 (migration guide)

**Session 3** (4-6 hours):
- Complete WP5 (scanner script)

**Session 4** (2-3 hours):
- Complete WP6 (testing)
- Complete WP7 (polish)

---

## Success Metrics

- [ ] Upgrade guide covers 100% of v7.0.0 breaking changes
- [ ] Class mapping includes 50+ classes
- [ ] Scanner detects 95%+ of deprecated usages (validated by testing)
- [ ] Scanner has < 5% false positive rate
- [ ] Upgrade guide tested on real application successfully
- [ ] Average upgrade time for medium application: 3-5 hours (validated)
- [ ] Zero blockers encountered in test upgrade
- [ ] Documentation clarity rated 4+/5 by test users

---

## Notes for Future Implementers

### Key Files Quick Reference

**Framework Root**: `/Users/smordziol/Webserver/libraries/application-framework/`

- Changelog: `changelog.md`
- Deprecated classes: `src/classes/_deprecated/`
- Upgrade guides: `docs/upgrade-guides/`
- Tools: `tools/`
- Class mapping output: `docs/upgrade-guides/v7.0.0-class-mappings.*`
- Guide output: `docs/upgrade-guides/upgrade-guide-v7.0.0.md`
- Scanner output: `tools/upgrade-to-v7.php`

### Common Patterns in Deprecated Files

```php
/**
 * @deprecated Use {@see \Full\Namespace\NewClass} instead.
 */
class Old_Class_Name extends \Full\Namespace\NewClass
{
    // Usually empty - just extends new class
}
```

### Git Commands for Research

```bash
cd /Users/smordziol/Webserver/libraries/application-framework

# Find moved classes
git log --all --oneline --grep="Moved" --since="2025-01-01"

# Find renames
git diff HEAD~100..HEAD --name-status | grep "^R"

# Find all deprecated tags
grep -r "@deprecated" src/classes/_deprecated/
```

### Changelog Parsing Tips

v7.0.0 section starts around line 1 in `changelog.md`. Look for commit summaries like:
- "Moved X to Y"
- "Renamed X to Y"
- "Relocated X"

### Scanner Detection Patterns

Common PHP patterns to detect:
```regex
new\s+([A-Z][a-zA-Z_]*)
extends\s+([A-Z][a-zA-Z_]*)
implements\s+([A-Z][a-zA-Z_,\s]*)
use\s+([A-Z][a-zA-Z_\\]*);
function\s+\w+\([^)]*([A-Z][a-zA-Z_]*)\s+\$
```

---

## Document Status Tracking

| Work Package | Status | Assignee | Started | Completed | Notes |
|--------------|--------|----------|---------|-----------|-------|
| WP1: Class Mappings | 🔲 Not Started | - | - | - | - |
| WP2: Structure | 🔲 Not Started | - | - | - | - |
| WP3: Breaking Changes | 🔲 Not Started | - | - | - | - |
| WP4: Migration Guide | 🔲 Not Started | - | - | - | - |
| WP5: Scanner Script | 🔲 Not Started | - | - | - | - |
| WP6: Testing | 🔲 Not Started | - | - | - | - |
| WP7: Polish | 🔲 Not Started | - | - | - | - |

**Legend**: 🔲 Not Started | 🔄 In Progress | ✅ Complete | ⚠️ Blocked

---

**Last Updated**: 2026-02-09  
**Document Version**: 1.0  
**Estimated Total Effort**: 15-23 hours

```
###  Path: `/docs/agents/plans/2026-02-09-upgrade-helper.md`

```md
# Plan: v7.0.0 Upgrade Documentation and Helper Script

**Date**: 2026-02-09  
**Status**: Ready for Implementation  
**Complexity**: High (Breaking-XXL Release)

## Overview

Create comprehensive upgrade documentation for the v7.0.0 "Breaking-XXL" release, including:
1. Detailed upgrade guide following existing documentation patterns
2. Automated helper script to scan applications for deprecated class usage
3. Complete class mapping reference table
4. Migration examples for all affected systems

## Context

The v7.0.0 release contains systematic breaking changes centered on:
- **Class reorganization**: Classes moved into thematically organized folders with proper namespacing
- **Admin screen system overhaul**: Dynamic loading with sitemap auto-discovery
- **Events system refactoring**: Auto-discovery of event/listener classes
- **MCP/AI integration**: Context-as-Code support for agentic development
- **Type safety improvements**: Extensive type hints and strict typing

Changes are well-documented in `changelog.md` with detailed commit summaries. Backward compatibility is maintained through deprecated class wrappers.

## Breaking Changes Summary

### 1. Class Locations and Namespaces (PRIMARY PATTERN)

Classes moved from root folder into thematically organized subfolders with proper PHP namespacing.

**Key Examples**:
- `Application_Exception` → `Application\Exception\ApplicationException`
- `Application_FilterSettings` → `Application\FilterSettingsInterface`
- `UI` class → Moved to organized location
- Media library classes → `Application\Media\*` namespace
- Session events → `Application\Session\Events\*`

**Pattern**: Old underscore-based pseudo-namespaces replaced with proper PHP namespaces.

### 2. Admin Screen System (MAJOR BREAKING)

**Old Approach**:
- Screens in fixed `Area` folder structure
- Hardcoded screen locations
- Manual sitemap maintenance

**New Approach**:
- Screens can be placed alongside their modules
- Dynamic loading by class name
- `RegisterAdminScreenFolders` offline event to register screen locations
- Automatic sitemap generation with caching
- Admin screens indexed on build

**Affected Screens**:
- Users management
- News central
- Media library
- Time tracker
- UI translation
- Countries management
- Tags management
- Developer screens

### 3. Offline Events System (BREAKING)

**Changes Required**:
- Events must extend `BaseOfflineEvent`
- Listeners must provide event name via `getEventName()`
- No more `wakeUp()` method needed
- Special `OfflineEvents` folder no longer required
- Event/listener classes auto-discovered

**Examples**:
- Session events moved to `Application\Session\Events\`
- Media events moved to `Application\Media\Events\`
- New `RegisterAdminScreenFolders` event

### 4. Database Changes (MINIMAL)

**Required SQL Updates**:
- `2025-12-19-app-sets.sql` - AppSets feature database storage
- This is the ONLY database change for v7.0.0

### 5. Deprecated Classes

Old class names marked as deprecated with `@deprecated` tags pointing to new locations. Backward compatibility maintained but classes will be removed in future version.

## Upgrade Guide Structure

Following the pattern from `docs/upgrade-guides/upgrade-guide-v5.5.0.md`:

```markdown
# Upgrade Guide: v7.0.0

## Overview
- Scope and impact summary
- Version compatibility (upgrading from v6.x)

## Prerequisites
- Minimum PHP version requirements
- Backup recommendations
- Estimated migration time

## Database Updates
### Required SQL Scripts
- 2025-12-19-app-sets.sql
- Import instructions

## Breaking Changes

### 1. Class Locations and Namespaces
#### Overview
- Pattern explanation
- Impact on existing code

#### Class Mapping Reference Table
Complete old → new mappings (50+ entries):
| Old Class | New Class/Namespace | Status |
|-----------|---------------------|--------|
| Application_Exception | Application\Exception\ApplicationException | Deprecated |
| Application_FilterSettings | Application\FilterSettingsInterface | Deprecated |
| [etc.] | [etc.] | [etc.] |

#### Migration Steps
1. Search for deprecated class usage
2. Update use statements
3. Update class references
4. Test affected functionality

#### Code Examples
```php
// OLD
throw new Application_Exception('Error');

// NEW
use Application\Exception\ApplicationException;
throw new ApplicationException('Error');
```

### 2. Admin Screen System Migration
#### Overview
- Architectural change explanation
- Benefits of new approach

#### Migration Steps
1. Identify custom admin screens
2. Move screens to module-adjacent locations (optional)
3. Implement `getAdminScreensFolder()` in module
4. Create `RegisterAdminScreenFolders` event listener
5. Register listener
6. Test screen accessibility

#### Complete Example
```php
// Before: Screen in fixed location
// /Area/Devel/MyCustomScreen.php

// After: Screen alongside module
// /MyModule/Admin/Screens/MyCustomScreen.php

// Module class
class MyModule
{
    public static function getAdminScreensFolder(): string
    {
        return __DIR__ . '/Admin/Screens';
    }
}

// Event listener
class MyScreenFoldersListener extends BaseRegisterAdminScreenFoldersListener
{
    public function handleEvent(RegisterAdminScreenFolders $event): void
    {
        $event->addFolder(MyModule::getAdminScreensFolder());
    }
}
```

### 3. Offline Events System Migration
#### Overview
- Event discovery changes
- Listener base class requirements

#### Migration Steps
1. Update event classes to extend `BaseOfflineEvent`
2. Update listeners to extend appropriate base listener
3. Remove `wakeUp()` methods
4. Implement `getEventName()` in listeners
5. Move events to thematic namespaces (recommended)

#### Code Examples
```php
// OLD
class MyListener
{
    public function wakeUp(Application_EventHandler_OfflineEvents_OfflineEvent $event) 
    {
        // Setup
    }
    
    public function handleEvent(array $data) 
    {
        // Handle
    }
}

// NEW
namespace MyApp\Events;

use Application\OfflineEvents\BaseOfflineEventListener;

class MyListener extends BaseOfflineEventListener
{
    public function getEventName(): string 
    { 
        return MyEvent::class; 
    }
    
    public function handleEvent(MyEvent $event): void
    {
        // Handle - no wakeUp needed
    }
}
```

### 4. Media Library Changes
#### Class Renames
- Collection class namespace updates
- Admin screen relocations

#### Migration Example
```php
// OLD
$media = Application_Media::getInstance();

// NEW
use Application\Media\Collection\MediaCollection;
$media = MediaCollection::getInstance();
```

### 5. Deprecated Screen Base Classes
#### Mapping Table
| Old Base Class | New Base Class | Usage |
|----------------|----------------|-------|
| Application_Admin_Area_Mode_CollectionCreateScreen | DBHelper\Admin\Screens\Mode\BaseRecordCreateMode | Create screens |
| Application_Admin_Area_Mode_CollectionRecordScreen | DBHelper\Admin\Screens\Mode\BaseRecordMode | Edit screens |
| [etc.] | [etc.] | [etc.] |

## Step-by-Step Migration Guide

### Phase 1: Preparation (15-30 minutes)
1. Create full backup of application
2. Review this guide completely
3. Run automated deprecated class scanner
4. Review scanner report
5. Estimate migration scope

### Phase 2: Database Updates (5 minutes)
1. Execute `2025-12-19-app-sets.sql`
2. Verify table creation
3. No data migration required

### Phase 3: Class Reference Updates (1-3 hours)
1. Update namespace imports
2. Replace deprecated class references
3. Update type hints
4. Focus on most-used classes first:
   - Exception classes
   - Media library
   - Filter settings
   - Admin screens

### Phase 4: Admin Screen Migration (30 minutes - 2 hours)
1. Identify custom admin screens
2. Optionally relocate screens
3. Implement `getAdminScreensFolder()` methods
4. Create and register event listeners
5. Clear cache and rebuild

### Phase 5: Event Listener Updates (30 minutes - 1 hour)
1. Update custom offline events
2. Update custom event listeners
3. Remove `wakeUp()` methods
4. Implement `getEventName()`
5. Test event firing

### Phase 6: Testing (1-2 hours)
1. Clear all caches
2. Test admin screen access
3. Test custom event functionality
4. Test media library features
5. Run application test suite
6. Manual smoke testing

### Phase 7: Cleanup (30 minutes)
1. Remove old commented code
2. Update inline documentation
3. Document any deferred changes
4. Commit migration changes

## Automated Helper Script

### Purpose
Scan application codebase for deprecated class usage and generate migration report.

### Location
`tools/upgrade-to-v7.php`

### Features
1. **Scan for deprecated classes**: Search PHP files for old class names
2. **Generate report**: List files, line numbers, and suggested replacements
3. **Priority ranking**: Order by frequency of usage
4. **Export options**: Console output, HTML report, JSON export

### Usage
```bash
# Scan entire application
php tools/upgrade-to-v7.php /path/to/application

# Scan specific folder
php tools/upgrade-to-v7.php /path/to/application/assets/classes

# Generate HTML report
php tools/upgrade-to-v7.php /path/to/application --format=html --output=report.html

# JSON export for automated processing
php tools/upgrade-to-v7.php /path/to/application --format=json --output=report.json
```

### Sample Output
```
=================================================================
 Application Framework v7.0.0 Upgrade Scanner
=================================================================

Scanning: /path/to/application
Found 47 deprecated class usages in 23 files

PRIORITY 1: High-frequency deprecated classes (10+ usages)
-----------------------------------------------------------
Application_Exception (15 usages)
  - /assets/classes/MyApp/Module.php:45, 67, 89
  - /assets/classes/MyApp/Helper.php:23, 156
  Replacement: Use Application\Exception\ApplicationException

PRIORITY 2: Medium-frequency deprecated classes (3-9 usages)
-----------------------------------------------------------
Application_Media (6 usages)
  - /assets/classes/MyApp/MediaHandler.php:12, 34, 56, 78, 90, 102
  Replacement: Use Application\Media\Collection\MediaCollection

[etc.]

Admin Screens Requiring Migration:
-----------------------------------
  - /Area/MyModule/CustomScreen.php
    Action: Implement getAdminScreensFolder() and register via event

Offline Events Requiring Migration:
------------------------------------
  - /assets/classes/MyApp/Events/MyCustomListener.php
    Action: Remove wakeUp(), implement getEventName()

=================================================================
Summary: 47 deprecated usages, 2 screen migrations, 1 event migration
Estimated effort: 2-4 hours
=================================================================
```

### Implementation Details

**Class Mapping Database**: Hardcoded array of deprecated classes with replacements:
```php
$classMap = [
    'Application_Exception' => [
        'new' => 'Application\Exception\ApplicationException',
        'namespace' => 'Application\Exception',
        'type' => 'class',
        'priority' => 'high'
    ],
    'Application_FilterSettings' => [
        'new' => 'Application\FilterSettingsInterface',
        'namespace' => 'Application',
        'type' => 'interface',
        'priority' => 'high'
    ],
    // [50+ more entries]
];
```

**Scanning Logic**:
1. Recursively scan PHP files
2. Parse for class usage (new, extends, implements, type hints, use statements)
3. Match against deprecated class map
4. Collect file locations and line numbers
5. Generate prioritized report

**Special Detections**:
- Admin screen base class usage (triggers migration notice)
- Offline event listener patterns (triggers migration notice)
- `Area` folder structure usage

## Testing Checklist

### Critical Functionality
- [ ] Application boots without errors
- [ ] Database connection successful
- [ ] User authentication works
- [ ] Admin area accessible

### Admin Screens
- [ ] All default admin screens load
- [ ] Custom admin screens accessible
- [ ] Navigation menu complete
- [ ] Screen tabs/modes work
- [ ] Form submissions successful

### Events System
- [ ] Session events fire correctly
- [ ] Media events fire correctly
- [ ] Custom events fire correctly
- [ ] Event listeners execute

### Media Library
- [ ] Media collection loads
- [ ] Media upload works
- [ ] Media editing works
- [ ] Media deletion works

### Application-Specific
- [ ] [Add application-specific tests]
- [ ] [Add application-specific tests]

## Common Issues and Solutions

### Issue: "Class not found" errors
**Cause**: Deprecated class reference not updated  
**Solution**: Use upgrade scanner to find all usages, update namespaces

### Issue: Admin screen not appearing
**Cause**: Screen not registered via `RegisterAdminScreenFolders` event  
**Solution**: Create event listener and register screen folder

### Issue: Events not firing
**Cause**: Listener not implementing `getEventName()` or wrong base class  
**Solution**: Update listener to extend proper base class, implement `getEventName()`

### Issue: "Interface not found" errors
**Cause**: `FilterSettingsInterface` namespace not imported  
**Solution**: Add `use Application\FilterSettingsInterface;`

### Issue: Cache-related errors
**Cause**: Old cached data referencing old class locations  
**Solution**: Clear all caches (storage/cache/*, storage/compiled/*)

## Deprecation Timeline

**v7.0.0**: Deprecated classes available with warnings  
**v7.1.0**: Deprecated classes still available  
**v8.0.0**: Deprecated classes removed (estimated Q3 2026)

**Recommendation**: Migrate immediately to avoid breaking changes in v8.0.0

## Additional Resources

- **Changelog**: `changelog.md` - Detailed commit summaries
- **Changelog History**: `docs/changelog-history/v6-changelog.md` - Historical v6.x changes
- **Framework Documentation**: `docs/` - General framework guides
- **Example Applications**: Contact framework maintainer for example migrations

## Version Compatibility

**Upgrading From**: v6.0.0, v6.1.0, v6.1.1, v6.2.0, v6.3.0  
**Upgrading To**: v7.0.0  
**PHP Requirements**: PHP 8.0+ (PHP 8.5 support planned)  
**Database**: MySQL 5.7+, MariaDB 10.2+

## Support

For migration assistance or issues:
1. Review this guide thoroughly
2. Run automated scanner
3. Check changelog for additional details
4. Contact framework maintainer

---

**Document Version**: 1.0  
**Last Updated**: 2026-02-09  
**Applies To**: Application Framework v7.0.0
```

## Implementation Steps

### Step 1: Create Upgrade Guide Document
**File**: `docs/upgrade-guides/upgrade-guide-v7.0.0.md`

**Tasks**:
1. Copy structure template above
2. Extract complete class mapping from:
   - Git history analysis
   - Changelog commit summaries  
   - Deprecated class files in `_deprecated/` folder
3. Create comprehensive mapping table (target: 50+ entries)
4. Write detailed examples for each section
5. Add application-specific testing checklist
6. Review and refine language

**Estimated Time**: 3-4 hours

### Step 2: Create Automated Helper Script
**File**: `tools/upgrade-to-v7.php`

**Requirements**:
1. Command-line interface
2. Recursive PHP file scanning
3. Pattern matching for deprecated classes
4. Report generation (console, HTML, JSON)
5. Priority ranking
6. Admin screen detection
7. Event listener detection

**Core Components**:

```php
#!/usr/bin/env php
<?php
/**
 * Application Framework v7.0.0 Upgrade Scanner
 * 
 * Scans application code for deprecated class usage and generates
 * migration reports.
 */

class UpgradeScanner
{
    private array $classMap = []; // Deprecated class mappings
    private array $findings = []; // Scan results
    private string $scanPath;
    
    public function __construct(string $scanPath)
    {
        $this->scanPath = $scanPath;
        $this->initClassMap();
    }
    
    private function initClassMap(): void
    {
        // Load all deprecated class mappings
        // Extract from framework's deprecated classes
    }
    
    public function scan(): void
    {
        // Recursively scan PHP files
        // Parse for class usage
        // Match against deprecated map
        // Store findings
    }
    
    public function generateReport(string $format = 'console'): void
    {
        // Generate report in specified format
        // Priority ranking
        // File grouping
        // Actionable recommendations
    }
    
    private function detectAdminScreens(): array
    {
        // Detect admin screens needing migration
    }
    
    private function detectEventListeners(): array
    {
        // Detect event listeners needing updates
    }
}

// CLI execution
$scanner = new UpgradeScanner($argv[1] ?? getcwd());
$scanner->scan();
$scanner->generateReport($options['format'] ?? 'console');
```

**Features to Implement**:
- [ ] Class mapping database (50+ entries)
- [ ] Recursive file scanner
- [ ] Class usage detection (new, extends, implements, type hints, use)
- [ ] Admin screen pattern detection
- [ ] Event listener pattern detection
- [ ] Priority ranking algorithm
- [ ] Console output formatter
- [ ] HTML report generator
- [ ] JSON export
- [ ] Summary statistics
- [ ] Progress indicator for large scans

**Estimated Time**: 4-6 hours

### Step 3: Extract Complete Class Mapping

**Sources**:
1. Git history: `git log --all --oneline --grep="moved" --since="2025-01-01"`
2. Changelog: Parse commit summaries in `changelog.md`
3. Deprecated files: Scan `src/classes/_deprecated/` folder
4. New namespaced classes: Scan organized folders

**Mapping Categories**:
- Core classes (Exception, Interfaces, Utils)
- Media library classes
- Admin screen base classes
- Event classes
- Session classes
- Deployment classes
- UI classes

**Format**:
```
OLD_CLASS | NEW_CLASS | NAMESPACE | TYPE | PRIORITY
```

**Estimated Time**: 2-3 hours

### Step 4: Testing and Validation

**Test on Sample Application**:
1. Create test application using v6.3.0
2. Add various deprecated class usages
3. Run upgrade scanner
4. Follow upgrade guide step-by-step
5. Verify all functionality works
6. Document any issues

**Refine Documentation**:
1. Update guide based on test findings
2. Add common issues encountered
3. Improve examples
4. Clarify ambiguous instructions

**Estimated Time**: 2-3 hours

### Step 5: Review and Finalize

**Review Checklist**:
- [ ] All breaking changes documented
- [ ] Code examples tested and verified
- [ ] Class mapping table complete (50+ entries)
- [ ] Scanner script functional
- [ ] Testing checklist comprehensive
- [ ] Language clear and actionable
- [ ] Formatting consistent
- [ ] Links to related docs valid

**Final Steps**:
1. Proofread entire guide
2. Test scanner on real applications
3. Get feedback from framework maintainer
4. Make final revisions
5. Mark document as complete

**Estimated Time**: 1-2 hours

## Total Estimated Effort

**Documentation**: 6-9 hours  
**Scanner Script**: 4-6 hours  
**Testing**: 2-3 hours  
**Review**: 1-2 hours  

**Total**: 13-20 hours

## Success Criteria

1. ✅ Upgrade guide covers all breaking changes in v7.0.0
2. ✅ Class mapping table includes 50+ deprecated classes
3. ✅ Scanner script successfully detects deprecated usage
4. ✅ Scanner script generates actionable reports
5. ✅ Guide tested on sample application successfully
6. ✅ All code examples tested and verified
7. ✅ Documentation follows existing pattern
8. ✅ Testing checklist is comprehensive

## Notes

- This is a "Breaking-XXL" release - comprehensive documentation is critical
- Backward compatibility via deprecated classes gives users migration runway
- Scanner script provides significant value for large applications
- Template can be reused for future major version upgrades
- Consider creating video walkthrough for complex migrations (future enhancement)

## Related Documents

- `changelog.md` - Detailed v7.0.0 changes
- `docs/changelog-history/v6-changelog.md` - Historical context
- `docs/upgrade-guides/upgrade-guide-v5.5.0.md` - Template reference
- `VERSION` - Current framework version (7.0.0)

---

**Plan Status**: ✅ Ready for Implementation  
**Approval Date**: 2026-02-09  
**Implementation Target**: Q1 2026

```
###  Path: `/docs/agents/plans/2026-03-13-api-caching-core-rework-1-rework-1/plan.md`

```md
# Plan

## Summary

Follow-up rework implementing all strategic recommendations from the `2026-03-13-api-caching-core-rework-1` synthesis report. The work covers five actionable items: wiring corrupt-cache logging with the reserved `ERROR_CACHE_FILE_CORRUPT` constant, applying the explicit `filemtime()` guard to the AI cache strategy, documenting the YAML keyword colon+space constraint in the module-context reference, adding the missing `@throws` annotation to `APICacheManager::invalidateMethod()`, and implementing a test-application consumer for `CountryRequestTrait` (the only currently unused trait). Recommendations 1 and 6 from the synthesis (trait.unused suppression policy) are resolved by restating the existing project policy: traits are never suppressed — the test application must implement concrete consumers.

## Architectural Context

### API Cache Module
- **Location:** `src/classes/Application/API/Cache/`
- **Key files:**
  - [CacheableAPIMethodTrait.php](src/classes/Application/API/Cache/CacheableAPIMethodTrait.php) — provides `readFromCache()` with silent corrupt-file recovery (lines 72–78)
  - [APICacheException.php](src/classes/Application/API/Cache/APICacheException.php) — defines `ERROR_CACHE_FILE_CORRUPT` (59213011), currently unreferenced in code
  - [APICacheManager.php](src/classes/Application/API/Cache/APICacheManager.php) — `invalidateMethod()` propagates `APICacheException` but lacks `@throws`
  - [README.md](src/classes/Application/API/Cache/README.md) — module documentation (recently rewritten in Phase 1)

### AI Cache Module
- **Location:** `src/classes/Application/AI/Cache/`
- **Key file:**
  - [Strategies/FixedDurationStrategy.php](src/classes/Application/AI/Cache/Strategies/FixedDurationStrategy.php) — `isCacheFileValid()` uses implicit `filemtime()` coercion (no explicit `=== false` guard)
- **Hardened counterpart for reference:** [API/Cache/Strategies/FixedDurationStrategy.php](src/classes/Application/API/Cache/Strategies/FixedDurationStrategy.php) — contains the explicit guard pattern to match

### Framework Logging
- **Logger access:** `AppFactory::createLogger()` returns an `Application_Logger` instance
- **Method:** `logError(string $message, ...$args)` for error/warning-level messages
- **Pattern:** Used throughout the framework for diagnostic logging

### Countries / CountryRequestTrait
- **Trait:** [src/classes/Application/Countries/Admin/Traits/CountryRequestTrait.php](src/classes/Application/Countries/Admin/Traits/CountryRequestTrait.php)
- **Interface:** [src/classes/Application/Countries/Admin/Traits/CountryRequestInterface.php](src/classes/Application/Countries/Admin/Traits/CountryRequestInterface.php) — extends `AdminScreenInterface`
- **Request type:** [src/classes/Application/Countries/Admin/CountryRequestType.php](src/classes/Application/Countries/Admin/CountryRequestType.php) — extends `BaseDBRecordRequestType`
- **Test application:** `tests/application/assets/classes/TestDriver/` — no Countries consumer exists yet
- **Existing patterns:** Other request type consumers exist in the test app (e.g., collection record screens)

### Module Context Reference
- **Location:** [docs/agents/references/module-context-reference.md](docs/agents/references/module-context-reference.md) — the single reference file for `module-context.yaml` authoring
- **Current state:** Documents `moduleMetaData` and `documents` sections but does not mention YAML syntax pitfalls for keyword values

### PHPStan Configuration
- **Location:** [phpstan.neon](phpstan.neon) — currently clean of `trait.unused` suppressions (removed in WP-004 of the original project)

## Approach / Architecture

The rework is six discrete, low-risk changes. No new architectural patterns are introduced — each change applies an existing pattern or extends existing documentation.

1. **Corrupt-cache logging (Rec 2):** Add a `logError()` call in the catch block of `CacheableAPIMethodTrait::readFromCache()`, referencing `ERROR_CACHE_FILE_CORRUPT` for context. The method already silently recovers; this adds operator observability without changing behaviour.

2. **AI `filemtime()` guard (Rec 3):** Mirror the API `FixedDurationStrategy` pattern in the AI counterpart — extract `filemtime()` into a local variable and add an explicit `=== false` check before the arithmetic comparison.

3. **YAML keyword constraint documentation (Rec 4):** Add a "Keyword Value Syntax" section to `docs/agents/references/module-context-reference.md` documenting the Symfony YAML colon+space parsing behaviour and the quoting requirement.

4. **`@throws` annotation (Rec 5):** Add `@throws APICacheException` to `APICacheManager::invalidateMethod()`.

5. **CountryRequestTrait test-app consumer (Rec 1 & 6 policy implementation):** Create a minimal admin screen in the test application that implements `CountryRequestInterface` and uses `CountryRequestTrait`. This provides PHPStan a consumer to analyze through and establishes the pattern for future trait consumers.

6. **Policy documentation (Rec 1 & 6):** Add a "Trait Consumer Policy" section to the project constraints document stating that `trait.unused` suppressions must never be added and that the test application must implement concrete consumers for all library traits.

## Rationale

- **Rec 1 & 6 consolidated:** The synthesis proposed a suppression-management policy. The user clarified the actual policy is stricter: never suppress, always implement a test-app consumer. This is the better approach because it provides both PHPStan coverage and regression testing simultaneously.
- **Logging over exceptions for corrupt cache:** The resilience design (silent delete + cache miss) is correct. Adding a log call preserves this behaviour while providing operator observability — systematic corruption will now be visible in application logs.
- **Mirroring the API guard pattern:** Consistency between AI and API cache strategies reduces cognitive load and prevents the same class of implicit-coercion bugs.
- **YAML documentation:** This constraint has caused multiple real issues already. Documenting it in the module-context reference is the most discoverable location for authors of `module-context.yaml` files.

## Detailed Steps

### Step 1: Add corrupt-cache logging to `CacheableAPIMethodTrait::readFromCache()`
- **File:** `src/classes/Application/API/Cache/CacheableAPIMethodTrait.php`
- In the catch block (currently lines 72–78), add a `logError()` call before the delete-and-return-null sequence.
- Use `AppFactory::createLogger()->logError()` with the cache file path and exception message for diagnostic context.
- Reference `APICacheException::ERROR_CACHE_FILE_CORRUPT` in the log message or as contextual info.
- Add `use Application\AppFactory;` import if not already present.

### Step 2: Apply explicit `filemtime()` guard to AI `FixedDurationStrategy`
- **File:** `src/classes/Application/AI/Cache/Strategies/FixedDurationStrategy.php`
- Replace the single-line `isCacheFileValid()` body with the two-step pattern from the API counterpart:
  1. `$mtime = filemtime($cacheFile->getPath());`
  2. `if($mtime === false) { return false; }`
  3. `return (time() - $mtime) < $this->durationInSeconds;`
- Add a PHPDoc block matching the API version's documentation style.

### Step 3: Add `@throws` annotation to `APICacheManager::invalidateMethod()`
- **File:** `src/classes/Application/API/Cache/APICacheManager.php`
- Add a docblock above `invalidateMethod()` with `@throws APICacheException` (error code: `ERROR_INVALID_METHOD_NAME`, propagated from `getMethodCacheFolder()`).

### Step 4: Document YAML keyword colon+space constraint
- **File:** `docs/agents/references/module-context-reference.md`
- Add a new section (e.g., "Keyword Value Syntax Constraints") documenting:
  - Symfony YAML parses bare `word: text` as a mapping key, not a string scalar.
  - Keyword values containing `: ` (colon followed by space) must be quoted.
  - Example: `"CacheableAPIMethodTrait: provides caching"` (quoted) vs `CacheableAPIMethodTrait: provides caching` (broken — parsed as mapping).
  - Consequence: `ModulesOverviewGenerator::buildModuleInfo()` receives arrays instead of strings, causing `Array to string conversion` errors in `composer build`.

### Step 5: Implement `CountryRequestTrait` consumer in test application
- **New file:** `tests/application/assets/classes/TestDriver/Admin/Area/Countries/` (or appropriate test-app admin area)
- Create a minimal admin screen class that:
  - Extends the appropriate test-app admin area base class
  - Implements `CountryRequestInterface`
  - Uses `CountryRequestTrait`
- The class needs only minimal method stubs to satisfy the interface contracts — it exists to give PHPStan a consumer and to demonstrate the trait's integration pattern.
- The Countries collection is available in the test application by default — no special setup needed.
- Run `composer dump-autoload` after creating the file.

### Step 6: Document trait consumer policy in constraints
- **File:** `docs/agents/project-manifest/constraints.md`
- Add a section titled "Trait Consumer Policy" or similar, documenting:
  - `trait.unused` PHPStan suppressions must never be added to `phpstan.neon`.
  - The test application (`tests/application/`) must implement concrete consumers for all library traits.
  - If a trait is unused, the correct action is to create a test-app consumer class, not to suppress the PHPStan notice.
  - Reason: `trait.unused` suppression disables static analysis of the trait's entire method body, creating a blind spot for type errors and logic bugs.

### Step 7: Run verification
- Run `composer dump-autoload` (for the new test-app class).
- Run `composer analyze` to verify the `CountryRequestTrait` consumer resolves PHPStan notices and introduces no new errors.
- Run `composer test-filter -- CountryRequest` if any related tests exist, or run `composer test-suite -- api-cache` to verify no regressions in cache tests.
- Run `composer build` to regenerate `.context/` documentation.

## Dependencies

- Steps 1–4 are fully independent and can be implemented in any order.
- Step 5 (CountryRequestTrait consumer) may require investigation of the test-application's admin area structure to determine the correct base class and registration pattern.
- Step 6 depends on the policy being confirmed (confirmed by user).
- Step 7 depends on all prior steps.

## Required Components

### Modified Files
- `src/classes/Application/API/Cache/CacheableAPIMethodTrait.php` — add logging
- `src/classes/Application/AI/Cache/Strategies/FixedDurationStrategy.php` — add `filemtime()` guard
- `src/classes/Application/API/Cache/APICacheManager.php` — add `@throws` annotation
- `docs/agents/references/module-context-reference.md` — add YAML constraint section
- `docs/agents/project-manifest/constraints.md` — add trait consumer policy section

### New Files
- `tests/application/assets/classes/TestDriver/Admin/Area/Countries/...` — CountryRequestTrait consumer (exact path TBD based on test-app conventions)

### No External Services or Infrastructure Changes

## Assumptions

- The framework logger (`AppFactory::createLogger()`) is available in the context where `CacheableAPIMethodTrait::readFromCache()` executes at runtime (API method processing context).
- The test application's admin area supports registering new screen classes that implement `AdminScreenInterface` without additional wiring beyond class creation and autoload.
- The `Application_Countries` collection referenced by `CountryRequestType` is available in the test application by default.

## Constraints

- All PHP code must use `array()` syntax (not `[]`).
- All new files must use `declare(strict_types=1);`.
- Classmap autoloading requires `composer dump-autoload` after adding new files.
- The full PHPUnit test suite must not be run — only targeted test commands.

## Out of Scope

- Whitespace-only identifier guard for `UserScopedCacheTrait` (synthesis marks as "Future" — deferred until user identifiers originate from untrusted input).
- `@package/@subpackage` annotation alignment on AI cache strategy classes (cosmetic, minimal impact).
- API Cache README updates beyond what logging changes require (README was fully rewritten in Phase 1).
- Full audit of every trait in the codebase — this rework addresses the one known unused trait (`CountryRequestTrait`) and documents the policy for future cases.

## Acceptance Criteria

1. `CacheableAPIMethodTrait::readFromCache()` logs a warning-level message (including file path and exception message) when a corrupt cache file is encountered and deleted.
2. `APICacheException::ERROR_CACHE_FILE_CORRUPT` is no longer dead code — it is referenced in the logging call.
3. `Application\AI\Cache\Strategies\FixedDurationStrategy::isCacheFileValid()` uses an explicit `$mtime === false` guard, matching the API counterpart's pattern.
4. `APICacheManager::invalidateMethod()` has a `@throws APICacheException` annotation in its docblock.
5. `docs/agents/references/module-context-reference.md` documents the Symfony YAML colon+space parsing constraint for keyword values.
6. A test-application admin screen class exists that implements `CountryRequestInterface` and uses `CountryRequestTrait`.
7. `docs/agents/project-manifest/constraints.md` documents the trait consumer policy (never suppress `trait.unused`, always implement a test-app consumer).
8. `composer analyze` produces no new errors related to the changes.
9. `composer build` completes successfully.

## Testing Strategy

| Scope | Command | Purpose |
|---|---|---|
| API Cache suite | `composer test-suite -- api-cache` | Verify no regressions in cache tests after logging addition |
| CountryRequest | `composer test-filter -- CountryRequest` | Verify any existing tests still pass |
| PHPStan | `composer analyze` | Verify CountryRequestTrait consumer resolves notices, no new errors |
| Build | `composer build` | Verify `.context/` regeneration and module docs |

No new unit tests are required for this rework:
- The logging addition is a side-effect-only change inside an existing catch block — the existing `CacheResilienceTest` already covers the corrupt-file recovery path.
- The `filemtime()` guard is a defensive hardening of an edge case that is impractical to unit-test (requires mocking `filemtime()` at the C level).
- The `@throws` annotation and documentation changes are non-functional.
- The CountryRequestTrait consumer exists to provide PHPStan coverage, not to test business logic.

## Risks & Mitigations

| Risk | Mitigation |
|---|---|
| **Logger unavailable in API method context** | `AppFactory::createLogger()` is a static factory available throughout the framework lifecycle. Verify during implementation that the import resolves and the method exists in the trait's execution context. |
| **CountryRequestType constructor expectations** | The Countries collection is available in the test application by default, so no special stubbing is needed. The consumer class can use the trait directly. |
| **YAML documentation section placement** | Place the new section prominently (near the top of the keywords subsection) to maximize discoverability. Reference it from the project manifest constraints as well. |

```
###  Path: `/docs/agents/plans/2026-03-13-api-caching-core-rework-1-rework-1/synthesis.md`

```md
# Synthesis Report
**Plan:** `2026-03-13-api-caching-core-rework-1-rework-1`
**Date:** 2026-03-18
**Status:** COMPLETE — all 7 work packages delivered

---

## Executive Summary

This rework plan implemented all six actionable strategic recommendations surfaced by the prior `2026-03-13-api-caching-core-rework-1` synthesis. The work was entirely low-risk: no new architectural patterns were introduced, no APIs changed shape, and no existing behaviour was altered. Every change applied an existing pattern, added a missing annotation, created a required test-app consumer, or expanded documentation.

**What was built:**

| WP | Change | Scope |
|---|---|---|
| WP-001 | Corrupt-cache logging with `ERROR_CACHE_FILE_CORRUPT` constant | `CacheableAPIMethodTrait.php` |
| WP-002 | Explicit `filemtime() === false` guard in AI `FixedDurationStrategy` | `AI/Cache/Strategies/FixedDurationStrategy.php` |
| WP-003 | `@throws APICacheException` annotation on `APICacheManager::invalidateMethod()` | `APICacheManager.php` |
| WP-004 | YAML keyword colon+space syntax constraint documented with examples | `docs/agents/references/module-context-reference.md` |
| WP-005 | `CountryRequestScreen` test-app consumer for `CountryRequestTrait` | `tests/application/.../CountryRequestScreen.php` (new) |
| WP-006 | Trait Consumer Policy section in project `constraints.md` | `docs/agents/project-manifest/constraints.md` |
| WP-007 | Integration verification: tests, PHPStan, build | No production files modified |

---

## Metrics

| Metric | Result |
|---|---|
| Work packages completed | 7 / 7 |
| Pipeline stages run | 28 (4 pipelines × 7 WPs) |
| Pipeline failures | 0 |
| API Cache tests | 40 tests, 61 assertions — all PASS |
| Broader Cache regression (API + AI) | 44 tests, 69 assertions — all PASS |
| PHPStan new errors introduced | 0 |
| PHPStan baseline (pre-existing) | 6 errors in unrelated files |
| `trait.unused` notices (CountryRequestTrait) | 0 (resolved by WP-005) |
| Security issues | 0 |
| `composer build` | PASS — `.context/` regenerated |

**Files modified (net change across all WPs):**

| File | WP(s) |
|---|---| 
| `src/classes/Application/API/Cache/CacheableAPIMethodTrait.php` | WP-001 |
| `src/classes/Application/AI/Cache/Strategies/FixedDurationStrategy.php` | WP-002 |
| `src/classes/Application/API/Cache/APICacheManager.php` | WP-003 |
| `docs/agents/references/module-context-reference.md` | WP-004 |
| `tests/application/assets/classes/TestDriver/Area/TestingScreen/CountryRequestScreen.php` *(new)* | WP-005 |
| `docs/agents/project-manifest/constraints.md` | WP-006 |
| `src/classes/Application/API/Cache/README.md` | WP-001, WP-003 docs |
| `docs/agents/projects/api-caching-system.md` | WP-001, WP-003 docs |
| `.context/modules/api-cache/overview.md` | Regenerated |
| `.context/modules/api-cache/architecture-core.md` | Regenerated |
| `changelog.md` | WP-007 docs (v7.0.13 entry) |

---

## Strategic Recommendations (Gold Nuggets)

These observations were extracted from Reviewer and QA pipeline comments across all WPs. None were blocking for this rework but each represents a meaningful improvement opportunity.

### High Priority
*(none — this rework introduced no high-priority findings)*

### Medium Priority

**[A] Logger defensiveness in `readFromCache()` corrupt-cache path**
> **Source:** QA (WP-007), Reviewer (WP-007, WP-001)

`AppFactory::createLogger()->logError()` in the corrupt-cache catch block is not itself wrapped in a defensive try/catch. If the logger throws during bootstrap failure, the exception escapes `readFromCache()` instead of falling through to `return null`. The framework logger is a stable singleton in production, making this unlikely — but for a resilience path the extra defensive wrap would be consistent with the intent of the recovery pattern.

**[B] PHPUnit class-discovery warnings inflate exit code**
> **Source:** Developer (WP-007), QA (WP-007), Reviewer (WP-007)

19+ test class files emit `Class X cannot be found` warnings at runtime, causing `composer test-filter` to return exit-code 1 even when all tests pass. Root cause: non-namespaced short-named test classes clash with PHPUnit's class-finder heuristics. This makes CI interpretation ambiguous. The fix is to namespace the offending test classes and update their autoload classmap entries. Pre-existing; carries significant impact on CI reliability.

### Low Priority

**[C] AI Cache module has no README.md or module-context.yaml**
> **Source:** Documentation (WP-002, WP-007)

The AI Cache module (`src/classes/Application/AI/Cache/`) is absent from the `.context/` module system and has no README. The API Cache counterpart is fully documented. A documentation pass creating `README.md` and `module-context.yaml` for the AI Cache module would bring it into the context system and make it discoverable for future agents.

**[D] `composer test-suite -- api-cache` mismatches phpunit.xml**
> **Source:** Developer (WP-007)

The WP spec referenced `composer test-suite -- api-cache`, but `phpunit.xml` defines only a single suite (`Framework Tests`). Passing an unrecognized suite name to `--testsuite` silently results in "No tests executed" rather than an error, which is dangerous in CI. Either add named suites to `phpunit.xml` for key modules, or document in `testing.md` that `composer test-file` / `composer test-filter` are the correct path-based targeting commands.

**[E] AI vs API `FixedDurationStrategy` naming inconsistency**
> **Source:** Reviewer (WP-002)

AI strategy uses `DURATION_1_HOUR` / `DURATION_6_HOURS` (underscore between number and unit); API counterpart uses `DURATION_1HOUR` / `DURATION_6HOURS` (no separator). Additionally, the AI strategy is missing the short-duration constants (`1min`, `5min`, `15min`) present in the API counterpart. A future harmonization pass would improve cross-module consistency.

**[F] `docs/agents/projects/api-caching-system.md` should be flagged as a design archive**
> **Source:** Documentation (WP-001)

This file is a historical design specification whose code snapshots can drift from the implementation. A header note marking it as a "design archive (not authoritative — see `README.md` and `.context/` for current state)" would prevent future agents from treating its code examples as real.

**[G] `readFromCache()` PHPDoc does not document corrupt-cache logging behaviour**
> **Source:** Documentation (WP-007)

`CacheableAPIMethodTrait::readFromCache()`'s PHPDoc states only "Returns null if the cache file does not exist or is no longer valid". The full corrupt-file + logError() recovery behavior is documented in the README and propagated to `.context/`, but the method-level PHPDoc does not reflect it. A one-line addition would improve self-contained discoverability.

**[H] `CountryRequestTrait` lazy-init uses `isset()` over `=== null` guard**
> **Source:** Reviewer (WP-005)

`CountryRequestTrait` uses `if(!isset($this->countryRequest))` as its lazy-init guard. For a nullable typed property, `isset()` returns false both when null and when uninitialised — correct, but `=== null` would be more expressive. Stylistic and fully consistent with existing project patterns; non-blocking.

---

## Blockers & Failures

None. All pipelines passed cleanly.

---

## Next Steps

Recommended focus for the next planning cycle:

1. **Resolve PHPUnit class-discovery warnings [B]** — highest operational impact; CI exit code ambiguity affects all future test runs.
2. **Document the AI Cache module [C]** — create `README.md` + `module-context.yaml` for `src/classes/Application/AI/Cache/`; run `composer build` to register it in the context system.
3. **Fix test-suite phpunit.xml alignment [D]** — either add named suites (`api-cache`, etc.) to `phpunit.xml` or update `testing.md` with the correct path-targeting commands.
4. **Harmonize AI and API FixedDurationStrategy [E]** — align constant naming and add the missing short-duration constants to the AI Strategy.
5. **Wrap logger call in corrupt-cache path [A]** — low-effort defensive hardening; enclose `logError()` in its own inner try/catch.
6. **Flag design-archive documents [F]** — add header notes to `docs/agents/projects/api-caching-system.md` (and similar) to prevent confusion about authority.

```
###  Path: `/docs/agents/plans/2026-03-13-api-caching-core-rework-1-rework-1/work.md`

```md
# Work Packages — API Caching Core Rework 1 (Rework 1)

## Overview

This rework implements all strategic recommendations from the `2026-03-13-api-caching-core-rework-1` synthesis report. Seven work packages cover corrupt-cache logging, AI cache hardening, annotation fixes, documentation updates, and verification.

## Work Package Summary

| WP | Title | Dependencies | Status |
|---|---|---|---|
| [WP-001](work/WP-001.md) | Add corrupt-cache logging | — | READY |
| [WP-002](work/WP-002.md) | Apply explicit `filemtime()` guard to AI cache strategy | — | READY |
| [WP-003](work/WP-003.md) | Add `@throws` annotation to `APICacheManager::invalidateMethod()` | — | READY |
| [WP-004](work/WP-004.md) | Document YAML keyword colon+space constraint | — | READY |
| [WP-005](work/WP-005.md) | Implement `CountryRequestTrait` consumer in test application | — | READY |
| [WP-006](work/WP-006.md) | Document trait consumer policy in constraints | — | READY |
| [WP-007](work/WP-007.md) | Verification & build | WP-001 – WP-006 | BLOCKED |

## Dependency Graph

```
WP-001 ──┐
WP-002 ──┤
WP-003 ──┤
WP-004 ──┼──► WP-007 (Verification & Build)
WP-005 ──┤
WP-006 ──┘
```

## Grouping

- **Code changes:** WP-001, WP-002, WP-003, WP-005
- **Documentation:** WP-004, WP-006
- **Verification:** WP-007

All code and documentation work packages (WP-001 through WP-006) are independent of each other and can be implemented in any order. WP-007 must run last.

## Modified Files

| File | Work Package |
|---|---|
| `src/classes/Application/API/Cache/CacheableAPIMethodTrait.php` | WP-001 |
| `src/classes/Application/AI/Cache/Strategies/FixedDurationStrategy.php` | WP-002 |
| `src/classes/Application/API/Cache/APICacheManager.php` | WP-003 |
| `docs/agents/references/module-context-reference.md` | WP-004 |
| `tests/application/assets/classes/TestDriver/Admin/Area/Countries/...` (new) | WP-005 |
| `docs/agents/project-manifest/constraints.md` | WP-006 |

```
###  Path: `/docs/agents/plans/2026-03-13-api-caching-core-rework-1-rework-1/work/WP-001.md`

```md
# WP-001: Add Corrupt-Cache Logging

## Description

Wire the reserved `ERROR_CACHE_FILE_CORRUPT` constant into live logging by adding an `AppFactory::createLogger()->logError()` call in the catch block of `CacheableAPIMethodTrait::readFromCache()`. The method already silently deletes corrupt cache files and returns `null`; this adds operator-level observability without changing recovery behaviour.

## Requirements

1. In `CacheableAPIMethodTrait::readFromCache()`, locate the catch block (currently lines 72–78) that handles corrupt/unreadable cache files.
2. Add a `logError()` call **before** the existing delete-and-return-null sequence.
3. The log message must include:
   - The cache file path (`$cacheFile->getPath()` or equivalent).
   - The exception message for diagnostic context.
   - A reference to `APICacheException::ERROR_CACHE_FILE_CORRUPT` (use the constant name in the log message or as structured context).
4. Add `use Application\AppFactory;` import if not already present.

## Technical Constraints

- Use `AppFactory::createLogger()->logError(...)` — the framework-standard logging pattern.
- Do **not** change the existing recovery behaviour (delete file → return `null`).
- The log call must not throw or disrupt the catch block flow.
- PHP code must use `array()` syntax (not `[]`).

## Acceptance Criteria

- [ ] `CacheableAPIMethodTrait::readFromCache()` logs a warning-level message when a corrupt cache file is encountered and deleted.
- [ ] `APICacheException::ERROR_CACHE_FILE_CORRUPT` is referenced in code (no longer dead code).
- [ ] Existing `CacheResilienceTest` tests still pass.

## Modified Files

- `src/classes/Application/API/Cache/CacheableAPIMethodTrait.php`

## Dependencies

None.

## Testing

```bash
composer test-suite -- api-cache
```

```
###  Path: `/docs/agents/plans/2026-03-13-api-caching-core-rework-1-rework-1/work/WP-002.md`

```md
# WP-002: Apply Explicit `filemtime()` Guard to AI Cache Strategy

## Description

Harden `Application\AI\Cache\Strategies\FixedDurationStrategy::isCacheFileValid()` by extracting `filemtime()` into a local variable and adding an explicit `=== false` guard before the arithmetic comparison. This mirrors the already-hardened API cache counterpart and prevents implicit type coercion bugs.

## Requirements

1. In `src/classes/Application/AI/Cache/Strategies/FixedDurationStrategy.php`, replace the single-line `isCacheFileValid()` body with the two-step pattern:
   ```php
   $mtime = filemtime($cacheFile->getPath());
   if($mtime === false) {
       return false;
   }
   return (time() - $mtime) < $this->durationInSeconds;
   ```
2. Add a PHPDoc block matching the API version's (`src/classes/Application/API/Cache/Strategies/FixedDurationStrategy.php`) documentation style.

## Technical Constraints

- The API counterpart at `src/classes/Application/API/Cache/Strategies/FixedDurationStrategy.php` contains the reference pattern — mirror it.
- PHP code must use `array()` syntax (not `[]`).

## Acceptance Criteria

- [ ] `isCacheFileValid()` uses an explicit `$mtime === false` guard.
- [ ] The method's logic matches the API counterpart's pattern.
- [ ] `composer analyze` introduces no new errors for this file.

## Modified Files

- `src/classes/Application/AI/Cache/Strategies/FixedDurationStrategy.php`

## Dependencies

None.

## Testing

```bash
composer analyze
```

```
###  Path: `/docs/agents/plans/2026-03-13-api-caching-core-rework-1-rework-1/work/WP-003.md`

```md
# WP-003: Add `@throws` Annotation to `APICacheManager::invalidateMethod()`

## Description

Add the missing `@throws APICacheException` docblock annotation to `APICacheManager::invalidateMethod()`. The method propagates `APICacheException` (error code: `ERROR_INVALID_METHOD_NAME`) from the internal `getMethodCacheFolder()` call but currently lacks the annotation.

## Requirements

1. In `src/classes/Application/API/Cache/APICacheManager.php`, locate `invalidateMethod()`.
2. Add or update the method's PHPDoc block to include `@throws APICacheException`.

## Technical Constraints

- Follow existing docblock conventions in the file.
- If a docblock already exists, add the `@throws` line; if not, create a minimal docblock.

## Acceptance Criteria

- [ ] `APICacheManager::invalidateMethod()` has a `@throws APICacheException` annotation in its docblock.
- [ ] `composer analyze` introduces no new errors for this file.

## Modified Files

- `src/classes/Application/API/Cache/APICacheManager.php`

## Dependencies

None.

## Testing

```bash
composer test-suite -- api-cache
```

```
###  Path: `/docs/agents/plans/2026-03-13-api-caching-core-rework-1-rework-1/work/WP-004.md`

```md
# WP-004: Document YAML Keyword Colon+Space Constraint

## Description

Add a "Keyword Value Syntax Constraints" section to `docs/agents/references/module-context-reference.md` documenting the Symfony YAML parser's behaviour with colon+space sequences in keyword values. This constraint has caused multiple real issues during `composer build` runs.

## Requirements

1. In `docs/agents/references/module-context-reference.md`, add a new section documenting:
   - Symfony YAML parses bare `word: text` as a mapping key, not a string scalar.
   - Keyword values containing `: ` (colon followed by space) **must** be quoted.
   - **Good example:** `"CacheableAPIMethodTrait: provides caching"` (quoted).
   - **Bad example:** `CacheableAPIMethodTrait: provides caching` (parsed as mapping → broken).
   - **Consequence:** `ModulesOverviewGenerator::buildModuleInfo()` receives arrays instead of strings, causing `Array to string conversion` errors in `composer build`.
2. Place the section prominently near the keywords subsection for maximum discoverability.

## Technical Constraints

- The file is Markdown documentation — no code changes.
- Keep the section concise and example-driven.

## Acceptance Criteria

- [ ] `docs/agents/references/module-context-reference.md` documents the Symfony YAML colon+space parsing constraint for keyword values.
- [ ] The section includes both correct and incorrect examples.
- [ ] The consequence of violating the constraint is documented.

## Modified Files

- `docs/agents/references/module-context-reference.md`

## Dependencies

None.

## Testing

No automated tests — documentation-only change. Verify by reading the file.

```
###  Path: `/docs/agents/plans/2026-03-13-api-caching-core-rework-1-rework-1/work/WP-005.md`

```md
# WP-005: Implement `CountryRequestTrait` Consumer in Test Application

## Description

Create a minimal admin screen class in the test application that implements `CountryRequestInterface` and uses `CountryRequestTrait`. This provides PHPStan a concrete consumer to analyze, resolving the `trait.unused` notice for `CountryRequestTrait` and establishing the pattern for future trait consumers.

## Requirements

1. Create a new admin screen class in the test application (under `tests/application/assets/classes/TestDriver/Admin/Area/`).
2. The class must:
   - Extend the appropriate test-app admin area base class.
   - Implement `Application\Countries\Admin\Traits\CountryRequestInterface`.
   - Use `Application\Countries\Admin\Traits\CountryRequestTrait`.
3. The class needs only minimal method stubs to satisfy the interface contracts — it exists to provide PHPStan coverage and demonstrate the trait's integration pattern.
4. Run `composer dump-autoload` after creating the file (classmap autoloading).

## Technical Constraints

- Use `declare(strict_types=1);` at the top of the file.
- PHP code must use `array()` syntax (not `[]`).
- Follow existing test-application conventions for class naming and directory structure.
- The Countries collection is available in the test application by default — no special setup is needed.

## Investigation Required

- Examine the test application's admin area structure (`tests/application/assets/classes/TestDriver/Admin/Area/`) to determine:
  - The correct base class to extend.
  - Existing patterns for screen classes (naming, registration, directory layout).
- Examine `CountryRequestInterface` and `CountryRequestTrait` to determine required method signatures.

## Acceptance Criteria

- [ ] A test-application admin screen class exists that implements `CountryRequestInterface` and uses `CountryRequestTrait`.
- [ ] `composer dump-autoload` succeeds after file creation.
- [ ] `composer analyze` no longer reports `trait.unused` for `CountryRequestTrait`.
- [ ] No new PHPStan errors are introduced.

## New Files

- `tests/application/assets/classes/TestDriver/Admin/Area/Countries/...` (exact path TBD based on test-app conventions)

## Dependencies

None.

## Testing

```bash
composer dump-autoload
composer analyze
```

```
###  Path: `/docs/agents/plans/2026-03-13-api-caching-core-rework-1-rework-1/work/WP-006.md`

```md
# WP-006: Document Trait Consumer Policy in Constraints

## Description

Add a "Trait Consumer Policy" section to `docs/agents/project-manifest/constraints.md` documenting the project's strict policy: `trait.unused` PHPStan suppressions must never be added; instead, the test application must implement concrete consumers for all library traits.

## Requirements

1. In `docs/agents/project-manifest/constraints.md`, add a section titled "Trait Consumer Policy" (or similar) documenting:
   - `trait.unused` PHPStan suppressions must **never** be added to `phpstan.neon`.
   - The test application (`tests/application/`) must implement concrete consumers for all library traits.
   - If a trait is unused, the correct action is to create a test-app consumer class, not to suppress the PHPStan notice.
   - **Reason:** `trait.unused` suppression disables static analysis of the trait's entire method body, creating a blind spot for type errors and logic bugs.

## Technical Constraints

- The file is Markdown documentation — no code changes.
- Follow the existing section structure and formatting of the constraints file.

## Acceptance Criteria

- [ ] `docs/agents/project-manifest/constraints.md` contains a "Trait Consumer Policy" section.
- [ ] The policy clearly states: never suppress `trait.unused`, always implement a test-app consumer.
- [ ] The rationale (PHPStan blind spot) is documented.

## Modified Files

- `docs/agents/project-manifest/constraints.md`

## Dependencies

None.

## Testing

No automated tests — documentation-only change. Verify by reading the file.

```
###  Path: `/docs/agents/plans/2026-03-13-api-caching-core-rework-1-rework-1/work/WP-007.md`

```md
# WP-007: Verification & Build

## Description

Run all verification steps to confirm the rework introduces no regressions, resolves the targeted issues, and produces clean generated documentation.

## Requirements

1. Run `composer dump-autoload` (if not already done for WP-005).
2. Run `composer test-suite -- api-cache` — verify no regressions in cache tests.
3. Run `composer test-filter -- CountryRequest` — verify any existing tests still pass.
4. Run `composer analyze` — verify:
   - `CountryRequestTrait` consumer resolves the `trait.unused` notice.
   - No new PHPStan errors from any changes.
5. Run `composer build` — verify `.context/` regeneration and module docs complete successfully.

## Technical Constraints

- Do **not** run the full test suite (`composer test`) — only targeted test commands.
- If any verification step fails, document the failure for investigation.

## Acceptance Criteria

- [ ] `composer test-suite -- api-cache` passes.
- [ ] `composer test-filter -- CountryRequest` passes (or confirms no tests exist to break).
- [ ] `composer analyze` produces no new errors related to the changes.
- [ ] `composer build` completes successfully.

## Dependencies

- WP-001 (corrupt-cache logging)
- WP-002 (filemtime guard)
- WP-003 (@throws annotation)
- WP-004 (YAML documentation)
- WP-005 (CountryRequestTrait consumer)
- WP-006 (trait consumer policy documentation)

## Testing

All verification commands listed in Requirements above.

```
###  Path: `/docs/agents/project-manifest/README.md`

```md
# Project Manifest

Canonical "Source of Truth" for AI agent sessions working with the Application Framework codebase.

> **Important:** Due to the size of this codebase (1,545+ class files), a single monolithic manifest
> cannot cover the full API surface and data flows. This manifest handles **cross-cutting concerns**
> (conventions, testing, orientation). Module-specific architecture and API documentation is maintained
> separately in the `/.context/` folder via the CTX Generator — see the Context Documentation section below.

---

## Sections

| Section | File | Description |
|---|---|---|
| **Context Documentation** | [context-documentation.md](context-documentation.md) | How codebase knowledge is structured: the two-tier manifest + CTX module docs strategy, `.context/` folder layout, and agent orientation guide. **Read this first.** |
| **Constraints & Conventions** | [constraints.md](constraints.md) | Coding rules, naming conventions, architectural patterns, and non-obvious gotchas. |
| **Testing** | [testing.md](testing.md) | Test infrastructure, suites, base classes, configuration, and execution commands. |
| **Modules Overview** | [modules-overview.md](modules-overview.md) | Auto-generated overview of all modules in the codebase. Lists module IDs, labels, descriptions, source paths, CTX doc paths, and inter-module relationships. Regenerate with `composer build-dev`. |
| **Module Keyword Glossary** | [module-glossary.md](module-glossary.md) | Auto-generated keyword-to-module lookup for opaque domain terms. Regenerate with `composer build-dev`. |

```
###  Path: `/docs/agents/project-manifest/constraints.md`

```md
# Constraints & Conventions

Project-specific rules, conventions, and non-obvious gotchas for the Application Framework codebase.
For detailed examples and patterns, refer to the [agent documentation](/docs/agents/).

---

## PHP Version & Language Features

- **Minimum PHP version:** 8.4+ (`"php": "^8.4"` in `composer.json`).
- **Strict typing is the standard.** All new files must use `declare(strict_types=1);`.
- **No PHP enums.** Enum-like patterns are implemented via class constants and dedicated type classes.
- **No `readonly` properties.** Prefer conventional private/protected properties with getter methods.

---

## Namespace Migration (Ongoing)

The codebase is transitioning from a legacy pre-namespace class naming convention to fully namespaced classes.

### Legacy Convention (being phased out)

Legacy classes use **underscore-separated names** that mirror the directory path:

```php
class Application_Admin_Area_Settings extends Application_Admin_Area
```

These classes have **no `namespace` declaration**.

### Modern Convention (standard for new code)

New classes must use proper **PHP namespaces** matching the directory structure:

```php
namespace Application\Composer\ModulesOverview;

class ModulesOverviewGenerator
```

### Migration Status

- **~1,031 files use namespaces** (67%).
- **~514 files still use the legacy convention** (33%).
- Legacy classes are concentrated in older modules: Admin screens, AjaxMethods, DBHelper UI, and various Application subsystems.
- Migration is incremental — legacy classes may reference namespaced classes and vice versa.

### Autoloading

The project uses Composer's **classmap** autoloading (not PSR-4). This means both legacy and namespaced classes coexist without autoloader issues, but it requires running `composer dump-autoload` after adding or renaming files.

```json
"autoload": {
    "classmap": [
        "src/classes/",
        "src/themes/default/templates",
        "tests/application/assets/classes",
        "tests/AppFrameworkTestClasses"
    ]
}
```

---

## Array Syntax

**Always use the verbose `array()` syntax** for array initialization. Do not use short array syntax `[]` for creating arrays.

```php
// Correct
$data = array('key' => 'value');
$list = array();

// Wrong
$data = ['key' => 'value'];
$list = [];
```

Note: Square brackets for array access (`$arr['key']`, `$arr[] = $val`) are fine — this rule only applies to array **creation**.

---

## Naming Conventions

| Context | Convention | Example |
|---|---|---|
| PHP methods/properties | camelCase | `getLabel()`, `$campaignManager` |
| Database columns | snake_case | `record_id`, `created_at` |
| Class names | PascalCase | `AppFactory`, `DBHelper_BaseCollection` |
| Constants | UPPER_SNAKE_CASE | `ERROR_NO_DATA_AVAILABLE` |
| Short acronyms ("ID", "URL") | Always uppercase | `getID()`, `getUserID()` |
| Exception classes | Suffixed with `Exception` | `ApplicationException`, `ConnectorException` |
| Filter criteria classes | Named `FilterCriteria` or suffixed | `DBHelper_BaseFilterCriteria` |

---

## Class Architecture Patterns

### AppFactory

`AppFactory` is the **central factory class** for the framework. It provides static factory methods for all major collections and services:

```php
$media = AppFactory::createMedia();
$users = AppFactory::createUsers();
$countries = AppFactory::createCountries();
```

Application projects extend `AppFactory` into their own `ClassFactory` to register additional factory methods. All factory methods follow the `create{ModuleName}()` naming convention.

### Database Records & Collections

The framework provides a Collection/Record pattern for database entities:

- **Collections** extend `DBHelper_BaseCollection` or `BaseRevisionableCollection` — responsible for CRUD operations and querying.
- **Records** extend `DBHelper_BaseRecord` — represent individual database rows.
- **FilterCriteria** extend `DBHelper_BaseFilterCriteria` or `Application_FilterCriteria` — provide typed query building for collections.

### Type Checking

Use `ClassHelper::requireObjectInstanceOf()` for type assertions instead of manual `instanceof` checks with exceptions:

```php
use AppUtils\ClassHelper;

$record = ClassHelper::requireObjectInstanceOf(
    SomeRecord::class,
    $rawRecord
);
```

### Lazy Initialization

Use `isset()` to guard lazy initialization of nullable properties:

```php
private ?SomeManager $manager = null;

public function getManager(): SomeManager
{
    if (!isset($this->manager)) {
        $this->manager = new SomeManager();
    }
    return $this->manager;
}
```

---

## Exception Handling

### Exception Structure

All exceptions take four parameters in this order:

1. **User-facing message** — No technical details, safe to display.
2. **Developer details** — Full context for debugging (sprintf-formatted).
3. **Error code** — Unique integer constant.
4. **Previous exception** — For exception chaining.

### Error Code Generation

Error codes are **globally unique integers** obtained from a dedicated error code service. The format is `{unique_id}{error_number}`, e.g., `505001` where `505` is the class identifier and `001` is the error number (always 3 digits, zero-padded).

Error codes are defined as `public const ERROR_*` constants in the relevant exception or class:

```php
public const ERROR_NO_DATA_AVAILABLE = 44201;
public const ERROR_COULD_NOT_ENCODE_JSON = 44202;
```

### Exception Hierarchy

Module-specific exceptions extend the application's base exception, which extends `Application_Exception`:

```
Application_Exception
└── ApplicationException (namespaced alias)
    └── ModuleException
```

### Control Flow

Return success values early; throw exceptions at the end of the method (keep the "happy path" un-nested).

---

## Array Handling

Use `ArrayDataCollection` for type-safe access to associative array data:

```php
use AppUtils\ArrayDataCollection;

$data = ArrayDataCollection::create(array(
    'name' => 'John Doe',
    'integer' => 45,
));

$name = $data->getString('name');
$age = $data->getInt('integer');
```

---

## File & JSON Handling

- Use `FileInfo::factory()` / `FolderInfo::factory()` for filesystem operations.
- Use `JSONFile::factory()` for JSON file I/O.
- Use `JSONConverter::var2json()` / `JSONConverter::json2array()` instead of raw `json_encode()` / `json_decode()`.

---

## UI Localization

- Wrap user-facing strings with `t()` from `AppLocalize`.
- Use numbered `sprintf`-style placeholders: `t('Welcome, %1$s!', $username)`.
- Use `tex()` when translator context is needed for ambiguous placeholders.
- Systematically split sentences into multiple `t()` calls for maximum reuse.

---

## Event System

Modules register behavior through **event listeners** extending framework base listener classes. The framework supports:

- **Global events** — application-wide via `EventHandler`.
- **Instance-scoped events** — via the `Eventable` trait on objects.
- **Offline events** — just-in-time listeners discovered and registered at build time.

Listener classes are stored in an `Events/` subdirectory within their module folder.

---

## API Methods

Public API methods extend `Application_API_Method` and follow a composition pattern with interfaces and traits for request/response handling.

API method classes are stored in `API/Methods/` subdirectories within their module folder.

**Method names must be PascalCase** — the `.htaccess` rewrite rule generated by the OpenAPI submodule uses the regex `^([A-Za-z][A-Za-z0-9]*)$`. Method names containing underscores, hyphens, or dots will not be rewritten and will return 404 on the clean-path URL.

---

## Trait Consumer Policy

### Rule

**Never add `trait.unused` suppressions to `phpstan.neon`.** PHPStan's `trait.unused` notice indicates that a trait exists with no concrete consumer in the analysed codebase. The correct response is to **create a test-application consumer class**, not to suppress the notice.

### Why suppression is harmful

`trait.unused` suppression disables PHPStan's analysis of the **entire trait method body** for the duration of the suppression. This creates a static-analysis blind spot: type errors and logic bugs inside the trait's methods become invisible to the analyser. Every suppressed trait is untested dead code from PHPStan's perspective.

### What to do instead

When a library trait has no consumer in the main codebase:

1. Create a minimal screen or class in `tests/application/assets/classes/TestDriver/` that:
   - Implements the corresponding interface (if one exists).
   - Uses the trait.
   - Provides only the minimal stubs required to satisfy interface contracts.
2. Run `composer dump-autoload` so the classmap picks up the new file.
3. Verify with `composer analyze` that the `trait.unused` notice is gone and no new errors are introduced.

### Example

See [tests/application/assets/classes/TestDriver/Area/TestingScreen/CountryRequestScreen.php](../../../tests/application/assets/classes/TestDriver/Area/TestingScreen/CountryRequestScreen.php) as a reference implementation of this pattern for `CountryRequestTrait`.


```
###  Path: `/docs/agents/project-manifest/context-documentation.md`

```md
# Context Documentation

How codebase knowledge is structured and delivered to AI agents working on the Application Framework.

---

## Why Module-Based Context

The Application Framework codebase contains **1,545+ class files** across dozens of modules. A traditional project manifest with a single API surface document and monolithic data-flow descriptions would be too large to fit in an agent's context window and too slow to scan.

Instead, the project uses a **two-tier documentation strategy**:

| Tier | Location | Scope | Maintained By |
|---|---|---|---|
| **Project Manifest** | `docs/agents/project-manifest/` | Cross-cutting concerns (conventions, testing, this guide) | Manifest Curator agent |
| **CTX Module Docs** | `.context/` (root-level) and `.context/modules/` (per-module) | Per-module architecture, API surface, data flows, file structures | CTX Generator tool (`ctx generate`) |

The manifest provides the "how we work" layer; the CTX docs provide the "what the code does" layer.

---

## The `.context/` Folder

Generated by the [CTX Generator](https://github.com/context-hub/generator) from `context.yaml` configuration files distributed throughout the codebase. Each module defines its own `module-context.yaml` that declares which source files, READMEs, and structures to include.

### Generation

```bash
ctx generate
```

Run from the project root. Regenerates all `.context/` output files. The generation timestamp is recorded in `.context/generated-at.txt`.

### Root-Level Documents

| File | Contents |
|---|---|
| `framework-core-system-overview.md` | Agent coding guidelines, coding patterns, exception usage, file/JSON/array handling, UI localization, and folder structure. |
| `framework-file-structure.md` | Full annotated class file tree with character counts. |

### Module Documents (`.context/modules/`)

Each module folder contains a set of Markdown files covering that module's architecture, API surface, UI layer, and data models. The standard files are:

| File Pattern | Contents |
|---|---|
| `overview.md` | Module purpose, key concepts, entry points. |
| `architecture-core.md` | Core classes, public API signatures, class hierarchy. |
| `architecture-ui.md` / `architecture-admin.md` | Admin screens, list/detail views, UI controllers. |
| `architecture-api-methods.md` | REST API method classes and their signatures. |
| `architecture-*.md` | Additional architecture aspects (e.g., event architecture, request/response). |
| `file-structure.md` / `class-tree.md` | Directory tree for the module's source files. |

Not every module has every file — the set depends on the module's complexity.

### Available Modules

| Module | Path | Sub-Modules |
|---|---|---|
| **Application Sets** | `.context/modules/application-sets/` | — |
| **Composer** | `.context/modules/composer/` | — |
| **Connectors** | `.context/modules/connectors/` | — |
| **DB Helper** | `.context/modules/db-helper/` | — |
| **Event Handler** | `.context/modules/event-handler/` | — |
| **UI** | `.context/modules/ui/` | `admin-urls/`, `bootstrap/`, `client-resources/`, `datagrid/`, `form/`, `markup-editor/`, `page/`, `properties-grid/`, `themes/`, `tree/` |

---

## How Agents Should Use This

1. **Start with the manifest** (`docs/agents/project-manifest/`) for cross-cutting rules: coding conventions, testing practices, and this orientation guide.
2. **Read `.context/` files** for module-specific knowledge: architecture, API surface, data flows.
3. **Read the module's `overview.md` first** to understand purpose and key concepts before diving into architecture files.
4. **Fall back to source code** only when the context docs do not cover the specific detail needed.
5. **Check `.context/generated-at.txt`** to verify documentation freshness before relying on it.

---

## Configuration

The CTX generation is driven by YAML configuration files:

| File | Scope |
|---|---|
| `context.yaml` (project root) | Top-level project config, imports, root-level documents. |
| `src/classes/**/module-context.yaml` | Per-module context definitions (glob-imported by the root config). |

Each `module-context.yaml` declares the sources (files, trees, classes) that the CTX Generator aggregates into the module's `.context/modules/<name>/` output.

```
###  Path: `/docs/agents/project-manifest/module-glossary.md`

```md
> **Auto-generated** — do not edit. Regenerated by `composer build-dev`.
> Generated: 2026-03-18T20:35:53Z

# Keyword Glossary

| Keyword | Context | Module(s) |
|---------|---------|-----------|
| Admin URL | type-safe fluent builder for admin-screen navigation links using area/mode/submode routing | ui-admin-urls |
| AI tool | callable unit that returns structured data, discovered and executed by AI agents | ai |
| API client | registered external application authorized to consume the API; stored in api_clients | api-clients |
| API group | logical grouping of methods for documentation and API key permissions | api |
| API key | bearer token belonging to a client; stored in api_keys with a per-key method whitelist | api-clients |
| API method | named endpoint class registered with the APIManager; discovered via APIMethodIndex | api |
| APICacheException | exception class extending APIException; thrown by cache infrastructure for programming errors; error codes: ERROR_EMPTY_USER_CACHE_IDENTIFIER 59213009, ERROR_INVALID_METHOD_NAME 59213010, ERROR_CACHE_FILE_CORRUPT 59213011 | api-cache |
| APICacheManager | static utility; resolves storage paths under APP_STORAGE/api/cache/MethodName/; provides getCacheFolder, getMethodCacheFolder, invalidateMethod, clearAll, getCacheSize | api-cache |
| APICacheStrategyInterface | contract for cache validity strategies; requires getID returning a STRATEGY_ID string constant and isCacheFileValid accepting a JSONFile | api-cache |
| APIEnvelope | standard OpenAPI success response envelope schema | api-openapi |
| APIErrorEnvelope | standard OpenAPI error response envelope schema | api-openapi |
| APIInfo | OpenAPI schema for the api metadata sub-object in every JSON response | api-openapi |
| APIKeyMethodInterface | opt-in interface that makes an API method require bearer token authentication | api-clients |
| APIResponseCacheLocation | CacheControl integration; exposes the API response cache to the admin cache management UI; registered automatically via RegisterAPIResponseCacheListener | api-cache |
| AppAPIConnector | HTTP client for consuming another framework application's API remotely | api |
| Application Sets | configuration controlling which administration areas are active per application instance | application-sets |
| BigSelection widget | scrollable multi-item selector component built on Bootstrap v2 | ui-bootstrap |
| Cache strategy | pluggable caching policy per tool — fixed-duration or uncached | ai |
| CacheableAPIMethodInterface | interface API method classes implement to opt into file-based response caching; extends APIMethodInterface; requires getCacheStrategy() and getCacheKeyParameters() from the implementing class | api-cache |
| CacheableAPIMethodTrait | trait providing default implementations for getCacheKey, readFromCache, writeToCache, and invalidateCache; use alongside CacheableAPIMethodInterface | api-cache |
| CKEditor 5 | WYSIWYG rich-text editor integrated through the Markup Editor abstraction | ui-markup-editor |
| clearAll | APICacheManager static method; deletes the entire api/cache folder | api-cache |
| Collection | ORM-like container of typed database records with CRUD, filtering, and events | db-helper |
| common type | reusable domain-specific parameter preset like AliasParameter or EmailParameter | api-parameters |
| ComposerScripts | orchestrates all composer build steps: cache clearing, event/admin indexing, API method index, OpenAPI spec generation, .htaccess generation, CSS classes, context date, module docs | composer |
| convertParameter | converts a single API parameter to its OpenAPI representation; returns null for reserved parameters | api-openapi |
| convertParameters | batch-converts all parameters from APIParamManager into query/header and JSON-body buckets | api-openapi |
| convertResponses | returns a map of HTTP status codes to OpenAPI response objects for a given API method | api-openapi |
| DataGrid | tabular list component with column sorting, pagination, and bulk actions | ui-datagrid |
| DataTable | raw SQL result wrapper for manual query output | db-helper |
| dry-run | optional mode where a method validates and reports what it would do without side effects | api |
| Eventable | mixin trait that adds instance-level event emitter capabilities to any class | event-handler |
| FilterCriteria | query filter builder defining SQL WHERE conditions for a collection | db-helper |
| FilterSettings | persisted user-facing filter values for a collection's list view | db-helper |
| FixedDurationStrategy | built-in strategy with STRATEGY_ID=FixedDuration; cache file is valid if it is younger than the configured duration in seconds; ships with named constants DURATION_1MIN, DURATION_5MIN, DURATION_15MIN, DURATION_1HOUR, DURATION_6HOURS, DURATION_12HOURS, DURATION_24HOURS | api-cache |
| flavor | cross-cutting parameter behavior like header-sourced or always-required | api-parameters |
| foreign ID | unique external identifier assigned to an API client for cross-system correlation | api-clients |
| generateHtaccess | APIManager convenience method; generates the API .htaccess for RESTful URL rewriting using the running driver context | api-openapi |
| generateOpenAPISpec | APIManager convenience method; generates the OpenAPI spec using the running driver's app name and version | api-openapi |
| getCacheKey | CacheableAPIMethodTrait: builds a deterministic MD5 hash from method name, version, and ksort-ed getCacheKeyParameters; same inputs always produce the same key | api-cache |
| getCacheKeyParameters | CacheableAPIMethodInterface method; implementing class returns an associative array of parameter name to scalar value pairs used in cache key generation | api-cache |
| getCacheSize | APICacheManager static method; returns total byte size of all files in the cache folder | api-cache |
| getCacheStrategy | CacheableAPIMethodInterface method; implementing class returns the APICacheStrategyInterface instance that controls cache file validity | api-cache |
| getMethodCacheFolder | APICacheManager static method; throws APICacheException ERROR_INVALID_METHOD_NAME if the method name is empty or contains path-traversal characters | api-cache |
| GetOpenAPISpec | framework built-in API method; serves the pre-generated openapi.json as raw JSON over HTTP at /api/GetOpenAPISpec; bypasses the standard JSON envelope; returns 500 when the spec file is missing | api-openapi |
| getSecuritySchemes | returns the components/securitySchemes definition for the HTTP Bearer API key scheme | api-openapi |
| getSpecURL | GetOpenAPISpec static helper; returns the absolute URL to the spec endpoint using APP_URL; used by APIMethodsOverviewTmpl to render the OpenAPI button on the API docs overview page | api-openapi |
| getUserCacheIdentifier | UserScopedCacheInterface method; returns a unique non-empty string identifying the current user context; must never return empty or APICacheException is thrown by UserScopedCacheTrait | api-cache |
| getUserScopedCacheKeyParameters | UserScopedCacheInterface method; returns method-specific cache key parameters excluding user identification; _userScope is a reserved key and the trait-injected value always takes precedence | api-cache |
| HtaccessGenerator | generates Apache .htaccess for RESTful URL rewriting of /api/ paths | api-openapi |
| HTML_QuickForm2 | underlying PHP form library wrapped and extended by the UI Form module | ui-form |
| HTTP connector | base class for building typed REST API clients with GET/POST/PUT/DELETE support | connectors |
| invalidateCache | CacheableAPIMethodTrait: deletes all cache files for this method by delegating to APICacheManager::invalidateMethod | api-cache |
| invalidateMethod | APICacheManager static method; deletes the entire MethodName cache subfolder; no-op if folder does not exist | api-cache |
| isCacheFileValid | APICacheStrategyInterface method; given a JSONFile returns whether the cached response is still valid | api-cache |
| JSON envelope | standard response wrapper with state/code/data/message keys | api |
| KeywordGlossaryGenerator | build-time tool that produces module-glossary.md from module keywords | composer |
| list builder | pluggable data source implementation that populates a DataGrid | ui-datagrid |
| load key | deduplication token ensuring each JS/CSS asset is injected once per page | ui-client-resources |
| ManualOnlyStrategy | built-in strategy with STRATEGY_ID=ManualOnly; cached file never expires automatically; invalidation is triggered only via invalidateCache or APICacheManager::clearAll | api-cache |
| MCP server | Model Context Protocol server exposing tools over stdio transport | ai |
| method index | cached class map of all API methods; rebuilt by composer build | api |
| method whitelist | per-key list of API methods the key is authorized to call; managed by APIKeyMethods | api-clients |
| MethodConverter | converts a single APIMethodInterface to an OpenAPI path item; adds security requirement for APIKeyMethodInterface methods and x-validation-rules for methods with parameter rules | api-openapi |
| ModulesOverviewGenerator | build-time tool that produces modules-overview.md from module-context.yaml files | composer |
| offline event listener | deferred JIT listener registered before its target class is instantiated | event-handler |
| OpenAPIGenerator | main orchestrator; iterates all API methods, assembles the complete OpenAPI 3.1 document including securitySchemes and x-validation-rules, writes it as a JSON file | api-openapi |
| OpenAPISchema | OpenAPI 3.1 component schemas for APIEnvelope, APIErrorEnvelope, APIInfo and security schemes for APIKeyMethodInterface authentication | api-openapi |
| page frame | container template defining the overall admin page structure — header, sidebar, footer | ui-page |
| param handler | internal pipeline component that reads parameter values during method processing | api-parameters |
| ParameterConverter | converts APIParameterInterface instances to OpenAPI parameter objects or request body schema properties | api-openapi |
| ParamTypeSelector | fluent builder returned by addParam for choosing the parameter type | api-parameters |
| processReturn | test helper on BaseAPIMethod; executes the method without sending a response | api |
| Properties Grid | key/value table component for rendering record detail views | ui-properties-grid |
| readFromCache | CacheableAPIMethodTrait: returns null on missing file, invalid strategy check, or corrupt JSON; on a corrupt file the file is automatically deleted before returning null; returns parsed array on a valid cache hit | api-cache |
| Record | typed wrapper for a single database row with field accessors and lifecycle events | db-helper |
| Redactor | alternative WYSIWYG rich-text editor available through the Markup Editor | ui-markup-editor |
| reserved parameter | framework-owned parameter name that application code cannot register | api-parameters |
| ResponseConverter | converts API method response metadata into OpenAPI 3.1 response objects for 200/400/500 status codes | api-openapi |
| rule | cross-parameter constraint evaluated after individual validation; e.g. OrRule, RequiredIfOtherIsSetRule | api-parameters |
| SECURITY_SCHEME_API_KEY | OpenAPISchema constant identifying the 'apiKey' security scheme name used in both components definition and per-method security requirements | api-openapi |
| selectable value | fixed set of allowed values declared by a parameter for validation and documentation | api-parameters |
| StatementBuilder | fluent SQL SELECT/INSERT/UPDATE/DELETE query builder | db-helper |
| theme override | mechanism for transparently replacing framework templates at the application level | ui-themes |
| Tool container | orchestrator that boots the environment, checks cache, runs a tool, stores the result | ai |
| Tree widget | hierarchical navigation component with nestable nodes supporting icons and action buttons | ui-tree |
| TypeMapper | maps framework API parameter type labels to OpenAPI 3.1 type/format pairs | api-openapi |
| UI singleton | central access point for all framework rendering components | ui |
| UserScopedCacheInterface | sub-interface of CacheableAPIMethodInterface for API methods returning user-specific data; declares getUserCacheIdentifier returning a unique non-empty string user identifier, and getUserScopedCacheKeyParameters returning method-specific parameters array; use with UserScopedCacheTrait; _userScope is a reserved key name | api-cache |
| UserScopedCacheTrait | pair trait for UserScopedCacheInterface; uses CacheableAPIMethodTrait internally; overrides getCacheKeyParameters to inject _userScope key via array union operator; throws APICacheException ERROR_EMPTY_USER_CACHE_IDENTIFIER if getUserCacheIdentifier returns empty; annotated with @phpstan-require-implements UserScopedCacheInterface | api-cache |
| writeToCache | CacheableAPIMethodTrait: writes response data to the resolved JSON cache file; parent directory is created automatically | api-cache |
| x-validation-rules | OpenAPI extension field added by MethodConverter to document inter-parameter validation constraints such as OrRule and RequiredIfOtherIsSetRule | api-openapi |

```
###  Path: `/docs/agents/project-manifest/modules-overview.md`

```md
# Modules Overview

> Auto-generated on 2026-03-18 21:35:53. Do not edit manually.

Total: 22 modules across 1 package.

## mistralys/application_framework

| ID | Label | Description | Source Path | Context Docs | Related Modules |
|----|-------|-------------|-------------|--------------|-----------------|
| `ai` | AI Tools | Infrastructure for exposing application logic as cacheable AI tools and serving them via an MCP server over stdio. | `src/classes/Application/AI/` | `.context/modules/ai/` | event-handler |
| `api` | API | Central API subsystem providing request dispatching, method registration, versioning, response envelope construction, documentation, and remote API consumption. The APIManager singleton processes requests by resolving method names via APIMethodIndex, instantiating method classes extending BaseAPIMethod, and delegating the full lifecycle (parameter validation, execution, response). Methods declare group affiliation (APIGroupInterface), version support (VersionedAPIInterface/VersionedAPITrait with per-version response classes), and response format via mix-in traits (JSONResponseInterface, JSONRequestInterface, DryRunAPIInterface). AppAPIConnector provides an HTTP client for consuming other framework applications' APIs remotely. | `src/classes/Application/API/` | `.context/modules/api/` | api-parameters, api-cache, api-openapi, api-clients, connectors, event-handler |
| `api-cache` | API Cache | File-based response caching for API methods. Provides a two-tier interface/trait composition pattern: (1) CacheableAPIMethodInterface + CacheableAPIMethodTrait for stateless, non-user-scoped methods; (2) UserScopedCacheInterface + UserScopedCacheTrait for user-specific methods that require per-user cache isolation — the trait automatically injects _userScope into the cache key using the array union operator so a user identifier can never be silently omitted or overwritten. Covers the full caching pipeline: strategy abstraction (APICacheStrategyInterface, FixedDurationStrategy, ManualOnlyStrategy with STRATEGY_ID PascalCase constants), file system management (APICacheManager: storage layout, method-level invalidation with path-traversal guard via APICacheException, cache size reporting), deterministic cache key generation via MD5 hash of method name + version + json_encode of sorted key parameters, read/write/invalidate operations (CacheableAPIMethodTrait: readFromCache returns null on miss, expired entry, or corrupt file — corrupt files are automatically deleted; writeToCache auto-creates parent dirs via JSONFile::putData; invalidateCache delegates to APICacheManager::invalidateMethod), error handling (APICacheException with ERROR_EMPTY_USER_CACHE_IDENTIFIER, ERROR_INVALID_METHOD_NAME, ERROR_CACHE_FILE_CORRUPT constants), and CacheControl admin UI integration (APIResponseCacheLocation). | `src/classes/Application/API/Cache/` | `.context/modules/api-cache/` | api-openapi |
| `api-clients` | API Clients | Manages API client registrations and their API keys using the Collection/Record pattern. An API client represents an external application authorized to consume the API, with multiple API keys each having their own activation status and method permissions. Provides bearer token authentication for API methods via APIKeyMethodInterface/APIKeyMethodTrait, the complete admin UI for client and key management (APIClientsArea with list/create/view modes and key sub-modes), and type-safe URL builders for all admin screens. | `src/classes/Application/API/Clients/` | `.context/modules/api/clients/` | api, api-openapi, db-helper, ui |
| `api-openapi` | API OpenAPI | End-to-end support for generating OpenAPI 3.1 specifications from the framework API system and serving them over HTTP. Covers parameter type mapping (TypeMapper), reusable component schemas and security schemes for the standard API response envelopes (OpenAPISchema), Apache .htaccess generation for RESTful URL rewriting (HtaccessGenerator), conversion of framework API parameters to OpenAPI parameter objects and request body schema properties (ParameterConverter), conversion of API method response metadata to OpenAPI response objects for 200/400/500 status codes (ResponseConverter), full spec assembly with error resilience and authentication/validation documentation (OpenAPIGenerator, MethodConverter), HTTP serving of the pre-generated spec as raw JSON (GetOpenAPISpec), and application-level convenience entry points (APIManager::generateOpenAPISpec, APIManager::generateHtaccess). The composer build pipeline calls both generation steps automatically via ComposerScripts. | `src/classes/Application/API/OpenAPI/` | `.context/modules/openapi/` | — |
| `api-parameters` | API Parameters | Complete parameter type system for API methods. Provides typed parameter classes (StringParameter, IntegerParameter, BooleanParameter, JSONParameter, IDListParameter), reusable domain-specific common types (AliasParameter, DateParameter, EmailParameter, etc.), a fluent registration API (APIParamManager, ParamTypeSelector), cross-parameter validation rules (OrRule, RequiredIfOtherIsSetRule, RequiredIfOtherValueEquals), per-parameter validations (RequiredValidation, EnumValidation, RegexValidation, CallbackValidation), selectable value lookups (SelectableValueParamInterface), header parameter support (APIHeaderParameterInterface), and the internal handler pipeline (BaseParamHandler, BaseRuleHandler) that bridges parameters and rules into the API method processing lifecycle. | `src/classes/Application/API/Parameters/` | `.context/modules/api/parameters/` | api, api-openapi, api-cache |
| `application-sets` | Application Sets | Configuration-level system to control which administration areas are enabled per application instance, supporting multiple feature configurations. | `src/classes/Application/AppSets/` | `.context/modules/application-sets/` | db-helper |
| `composer` | Application Composer | Build-time utilities that generate Markdown documentation artefacts (Modules Overview and Keyword Glossary) from module-context.yaml files discovered throughout the codebase, generate the OpenAPI 3.1 specification JSON, and generate the API .htaccess for RESTful URL rewriting. Includes a shared BuildMessages registry for build-time notices. All steps are orchestrated by ComposerScripts::build(). | `src/classes/Application/Composer/` | `.context/modules/composer/` | event-handler |
| `connectors` | Connectors | Scaffold for building HTTP connector classes to access external APIs, supporting GET, POST, PUT, and DELETE methods. | `src/classes/Connectors/` | `.context/modules/connectors/` | — |
| `db-helper` | DBHelper | Provides database abstraction for manual SQL operations and an ORM-like record collection system with filtering, events, and CRUD operations. | `src/classes/DBHelper/` | `.context/modules/db-helper/` | event-handler, ui, ui-datagrid, application-sets |
| `event-handler` | Event Handling | Comprehensive event handling system supporting global events, instance-scoped Eventable objects, and offline just-in-time event listeners. | `src/classes/Application/EventHandler/` | `.context/modules/event-handler/` | ui, ui-form, db-helper, composer |
| `ui` | User Interface | Central rendering layer of the framework: the UI singleton, Bootstrap component abstractions, page composition, client-side resource management, and the PHP-based theming engine. | `src/classes/UI/` | `.context/modules/ui/` | event-handler, db-helper, ui-datagrid, ui-tree, ui-markup-editor, ui-page, ui-form, ui-admin-urls, ui-bootstrap, ui-client-resources, ui-properties-grid, ui-themes |
| `ui-admin-urls` | UI Admin URLs | Type-safe fluent URL builder for constructing admin-screen navigation links using the framework's area/mode/submode/action routing scheme. | `src/classes/UI/AdminURLs/` | `.context/modules/ui/admin-urls/` | ui, ui-tree |
| `ui-bootstrap` | UI Bootstrap Components | PHP abstractions for Bootstrap v2 UI components — dropdowns, tabs, button groups, popovers, and the BigSelection widget — each exposing a fluent builder API. | `src/classes/UI/Bootstrap/` | `.context/modules/ui/bootstrap/` | ui |
| `ui-client-resources` | UI Client Resources | Manages client-side JS and CSS resource registration with load-key deduplication, ensuring each asset is injected into the page exactly once across both full-page loads and AJAX requests. | `src/classes/UI/ClientResource/` | `.context/modules/ui/client-resources/` | ui |
| `ui-datagrid` | UI DataGrid | Renders tabular data with built-in column sorting, pagination, per-user column configuration, row selection, and bulk actions driven by a pluggable list-builder source. | `src/classes/UI/DataGrid/` | `.context/modules/ui/datagrid/` | ui, db-helper |
| `ui-form` | UI Form | Handles form creation, element composition, pluggable rendering, and server-side validation, built on HTML_QuickForm2 with framework-layer conventions for AJAX submit and typed rules. | `src/classes/UI/Form/` | `.context/modules/ui/form/` | ui, event-handler, ui-markup-editor |
| `ui-markup-editor` | UI Markup Editor | Integrates WYSIWYG rich-text editors (CKEditor 5 and Redactor) into forms through a unified abstract API with configurable toolbar composition. | `src/classes/UI/MarkupEditor/` | `.context/modules/ui/markup-editor/` | ui, ui-form, ui-themes |
| `ui-page` | UI Page | Orchestrates full-page composition by aggregating the header, sidebar, footer, breadcrumb trail, and named navigations, delegating rendering to the active theme's frame template. | `src/classes/UI/Page/` | `.context/modules/ui/page/` | ui, ui-themes |
| `ui-properties-grid` | UI Properties Grid | Renders a key/value property table for detail views, supporting typed property variants, inline action buttons, and conditional row visibility. | `src/classes/UI/PropertiesGrid/` | `.context/modules/ui/properties-grid/` | ui |
| `ui-themes` | UI Themes | PHP-based template and theming engine where templates are PHP classes; framework templates can be transparently overridden by application-level themes without copying the entire theme. | `src/classes/UI/Themes/` | `.context/modules/ui/themes/` | ui, ui-markup-editor, ui-page |
| `ui-tree` | UI Tree | Hierarchical tree widget composed of nestable nodes that support icons, URL or JavaScript link targets, active/selected states, and per-node action buttons. | `src/classes/UI/Tree/` | `.context/modules/ui/tree/` | ui, ui-admin-urls |

## Module Relationships

- **ai** → event-handler
- **api** → api-parameters, api-cache, api-openapi, api-clients, connectors, event-handler
- **api-cache** → api-openapi
- **api-clients** → api, api-openapi, db-helper, ui
- **api-parameters** → api, api-openapi, api-cache
- **application-sets** → db-helper
- **composer** → event-handler
- **db-helper** → event-handler, ui, ui-datagrid, application-sets
- **event-handler** → ui, ui-form, db-helper, composer
- **ui** → event-handler, db-helper, ui-datagrid, ui-tree, ui-markup-editor, ui-page, ui-form, ui-admin-urls, ui-bootstrap, ui-client-resources, ui-properties-grid, ui-themes
- **ui-admin-urls** → ui, ui-tree
- **ui-bootstrap** → ui
- **ui-client-resources** → ui
- **ui-datagrid** → ui, db-helper
- **ui-form** → ui, event-handler, ui-markup-editor
- **ui-markup-editor** → ui, ui-form, ui-themes
- **ui-page** → ui, ui-themes
- **ui-properties-grid** → ui
- **ui-themes** → ui, ui-markup-editor, ui-page
- **ui-tree** → ui, ui-admin-urls

```
###  Path: `/docs/agents/project-manifest/testing.md`

```md
# Testing

Comprehensive guide to the Application Framework's test infrastructure, conventions, and execution.

---

## Overview

| Aspect | Detail |
|---|---|
| **Framework** | PHPUnit >= 13.0 |
| **PHP version** | 8.4+ |
| **Config file** | `phpunit.xml` (project root) |
| **Bootstrap** | `tests/bootstrap.php` |
| **Test count** | ~155 unit test files + 2 integration test files |
| **Test suite** | Single suite: `Framework Tests` (all tests under `tests/AppFrameworkTests/`) |

---

## Directory Structure

```
tests/
├── bootstrap.php                    # PHPUnit bootstrap — boots the framework test application
├── AppFrameworkTests/               # All unit test files (one folder per module)
│   ├── Admin/
│   ├── Ajax/
│   ├── API/
│   ├── AppFactory/
│   ├── Application/
│   ├── AppSets/
│   ├── AppSettings/
│   ├── Collection/
│   ├── Composer/
│   ├── Connectors/
│   ├── Countries/
│   ├── DataGrids/
│   ├── DBHelper/
│   ├── DeploymentRegistry/
│   ├── Disposables/
│   ├── Driver/
│   ├── ErrorLog/
│   ├── EventHandling/
│   ├── Eventables/
│   ├── Forms/
│   ├── Functions/
│   ├── Global/
│   ├── Helpers/
│   ├── Installer/
│   ├── LDAP/
│   ├── Locales/
│   ├── MarkdownParser/
│   ├── Media/
│   ├── News/
│   ├── OAuth/
│   ├── Ratings/
│   ├── RequestLogTests/
│   ├── Revisionables/
│   ├── SessionTests/
│   ├── SourceFolders/
│   ├── SystemMail/
│   ├── Tags/
│   ├── TypeHinter/
│   ├── UI/
│   ├── User/
│   ├── Users/
│   └── Validatable/
├── AppFrameworkIntegrationTests/    # Integration tests
│   ├── LDAP/
│   └── Logging/
├── AppFrameworkTestClasses/         # Base test cases, traits, mocks, stubs
│   ├── API/                         # API test cases and stubs
│   ├── Collection/
│   ├── Stubs/                       # Stub implementations for testing
│   ├── Traits/                      # Shared test traits (paired with interfaces)
│   ├── ApplicationTestCase.php      # Primary base test case (extends PHPUnit TestCase)
│   ├── ApplicationTestCaseInterface.php
│   └── ...                          # Domain-specific base test cases
├── application/                     # Framework Test Application (working implementation)
│   └── assets/classes/              # Test application classes
├── assets/                          # Test fixtures
├── files/                           # Test-related files
├── phpstan/                         # PHPStan test-related config
└── sql/                             # Source SQL files for the test database
```

---

## Running Tests

### Composer Scripts

All test execution goes through Composer scripts defined in `composer.json`:

| Command | Purpose | Example |
|---|---|---|
| `composer test-file -- <path>` | Run a single test file | `composer test-file -- tests/AppFrameworkTests/DBHelper/CollectionTest.php` |
| `composer test-filter -- <pattern>` | Run tests matching a name filter | `composer test-filter -- CollectionTest::testSomeMethod` |
| `composer test-suite -- <name>` | Run all tests in a named suite | `composer test-suite -- "Framework Tests"` |
| `composer test-group -- <group>` | Run tests in a PHPUnit group | `composer test-group -- SomeGroup` |

All scripts pass `--no-progress` to PHPUnit by default (except `composer test`).

### Choosing the Right Scope

1. **Changed a single class?** → `composer test-file` with its test file.
2. **Changed a module?** → `composer test-filter -- ModuleName` to match by class name.
3. **Unsure which tests cover a change?** → `composer test-filter -- ClassName`.
4. **Full suite** → `composer test` (runs all tests).

---

## Base Test Cases

All tests extend `ApplicationTestCase`, which extends PHPUnit's `TestCase`. Specialized base classes provide domain-specific setup:

| Base Class | Namespace | Purpose |
|---|---|---|
| `ApplicationTestCase` | `AppFrameworkTestClasses` | Primary base — boots the application, provides common helpers, transaction management |
| `AjaxTestCase` | `AppFrameworkTestClasses` | AJAX method testing |
| `APITestCase` | `AppFrameworkTestClasses\API` | API method testing |
| `APIClientTestCase` | `AppFrameworkTestClasses\API` | API client testing |
| `CountriesTestCase` | `AppFrameworkTestClasses` | Country collection tests |
| `DBHelperTestCase` | `AppFrameworkTestClasses` | Database helper tests |
| `FormTestCase` | `AppFrameworkTestClasses` | Form building/validation tests |
| `LDAPTestCase` | `AppFrameworkTestClasses` | LDAP connectivity tests |
| `MediaTestCase` | `AppFrameworkTestClasses` | Media library tests |
| `NewsTestCase` | `AppFrameworkTestClasses` | News central tests |
| `RequestLogTestCase` | `AppFrameworkTestClasses` | Request logging tests |
| `RevisionableTestCase` | `AppFrameworkTestClasses` | Revisionable record tests |
| `TaggingTestCase` | `AppFrameworkTestClasses` | Tagging system tests |
| `UserTestCase` | `AppFrameworkTestClasses` | User management tests |

---

## Shared Test Traits

Reusable test behavior is implemented via trait + interface pairs in `tests/AppFrameworkTestClasses/Traits/`. A test class implements the interface and uses the trait:

| Trait | Interface | Purpose |
|---|---|---|
| `ConnectorTestTrait` | `ConnectorTestInterface` | Connector testing |
| `DBHelperTestTrait` | `DBHelperTestInterface` | DBHelper testing |
| `DataGridTestTrait` | — | DataGrid testing |
| `ImageMediaTestTrait` | `ImageMediaTestInterface` | Image media testing |
| `MythologyTestTrait` | `MythologyTestInterface` | Test application mythology testing |
| `OperationResultTestTrait` | `OperationResultTestInterface` | Operation result testing |
| `RevisionableTestTrait` | — | Revisionable record testing |

API-specific traits in `tests/AppFrameworkTestClasses/API/`:

| Trait | Interface | Purpose |
|---|---|---|
| `APIClientTestTrait` | `APIClientTestInterface` | API client testing |
| `APIMethodTestTrait` | `APIMethodTestInterface` | API method testing |

---

## Bootstrap Process

The test bootstrap (`tests/bootstrap.php`):

1. Defines `APP_ROOT` pointing to `tests/application/` (the framework test application).
2. Defines `TESTS_ROOT`, `APP_INSTALL_FOLDER`, `APP_VENDOR_PATH` constants.
3. Sets `APP_FRAMEWORK_TESTS = true` to signal the framework's own test suite.
4. Requires the framework bootstrap (`src/classes/Application/Bootstrap/Bootstrap.php`).
5. Initializes `Application_Bootstrap` and boots the `TestSuiteBootstrap` class.

This means the full application stack is available in tests (database, services, configuration).

---

## Framework Test Application

The framework includes a working test application in `tests/application/` that provides concrete implementations of framework abstractions. This application is used by the test suite to exercise framework features against real database tables and screens.

---

## Local Test Environment Setup

The test suite requires local configuration files that are **not committed** to the repository. Before running tests for the first time, copy the distribution templates and configure them for the local environment:

1. Copy `tests/application/config/test-db-config.dist.php` → `tests/application/config/test-db-config.php`
2. Copy `tests/application/config/test-ui-config.dist.php` → `tests/application/config/test-ui-config.php`
3. Copy `tests/application/config/test-cas-config.dist.php` → `tests/application/config/test-cas-config.php`

Edit `test-db-config.php` and set the correct database host, name, user, and password. The database name defaults to `app_framework_testsuite`; import `tests/sql/testsuite.sql` to initialise it.

Edit `test-ui-config.php` and adjust `TESTS_BASE_URL` to the URL at which `tests/application` is reachable on the local webserver.

`test-cas-config.php` is only required when `TESTS_SESSION_TYPE` is set to `CAS` in `test-ui-config.php`. For most local runs the default `NoAuth` session type is sufficient and the CAS config can be left as-is.

---

## Stubs

Located in `tests/AppFrameworkTestClasses/Stubs/`:

| Stub | Purpose |
|---|---|
| `ClientFormStub` | Client form stub |
| `HiddenVariablesStub` | Hidden variables stub |
| `IDTableCollectionStub` | ID table collection stub |
| `LegacyUIRenderableStub` | Legacy UI renderable stub |
| `PropertizableStub` | Propertizable stub |
| `StringableStub` | Stringable stub |
| `ValidatableStub` | Validatable stub |

Additional stubs exist in subdirectories: `Stubs/Admin/`, `Stubs/DBHelper/`, `Stubs/Revisionables/`, `Stubs/Session/`, `Stubs/UI/`.

---

## API Test Stub Placement

API method stubs that invoke `processReturn()` must be placed in the test application's source directory:

```
tests/application/assets/classes/TestDriver/API/
```

**Do not** place such stubs in `tests/AppFrameworkTestClasses/` — they will not be discovered by the method index.

### Why this matters

`APIMethodParameter` validates that the method name passed to `processReturn()` exists in the framework's API method index. This index is built by scanning the test application's source folders (`tests/application/assets/classes/`). Classes in `AppFrameworkTestClasses/` are invisible to this discovery process and will trigger a "method not found" validation error.

Simple stubs that do not invoke `processReturn()` (e.g., those only used via `createStub()` / `createMock()`) can still reside in `AppFrameworkTestClasses/`.

---

## PHPUnit Mock Conventions

PHPUnit 13 emits a **Notice** — "No expectations were configured for the mock object for X"
— when `createMock()` is used without any `expects()` call. Use `createStub()` instead when
the test only needs a double that returns values (no call-count verification):

```php
// Correct — test only needs the object to return a value; no expectation needed
$method = $this->createStub(APIMethodInterface::class);

// Avoid — triggers a PHPUnit Notice when no expects() are added
$method = $this->createMock(APIMethodInterface::class);
```

When writing helper methods that return a test double, annotate the return type with
`&\PHPUnit\Framework\MockObject\Stub` (not `MockObject`) to match `createStub()`'s
actual return type:

```php
/** @return APIMethodInterface&\PHPUnit\Framework\MockObject\Stub */
private function createMethodStub() : APIMethodInterface
{
    return $this->createStub(APIMethodInterface::class);
}
```

Use `createMock()` only when the test explicitly verifies interaction (e.g., `expects(once())`).


```
###  Path: `/docs/agents/projects/api-caching-system.md`

```md
# Project: API Response Caching System

## Goal

Add file-based response caching to the framework's API method layer. Methods opt in via an interface + trait pair. Cached responses bypass the expensive `collectRequestData()` / `collectResponseData()` pipeline. Three invalidation strategies are supported: fixed-duration TTL, manual-only, and DBHelper-collection-aware automatic invalidation.

## Research

See [/docs/agents/research/2026-03-13-api-caching-system.md](/docs/agents/research/2026-03-13-api-caching-system.md) for the full research report.

---

## Architecture Overview

```
BaseAPIMethod::_process()
  │
  ├─ validate()                          ← runs always (needed for cache key)
  ├─ getActiveVersion()                  ← runs always (needed for cache key)
  │
  ├─ ★ CACHE CHECK (new)                ← if CacheableAPIMethodInterface: read cache
  │     └─ HIT → sendSuccessResponse()  ← short-circuits, skips everything below
  │
  ├─ collectRequestData()               ← only on MISS
  ├─ collectResponseData()              ← only on MISS
  │
  ├─ ★ CACHE WRITE (new)               ← if CacheableAPIMethodInterface: write cache
  │
  └─ sendSuccessResponse()
```

### Design Pattern

Follows the framework's existing **Interface + Trait composition** model, identical to `DryRunAPIInterface`/`DryRunAPITrait` and `JSONResponseInterface`/`JSONResponseTrait`.

---

## Implementation Status

| Step | File | WP | Status |
|---|---|---|---|
| 1 | `APICacheStrategyInterface` | WP-001 | ✅ Done |
| 2 | `FixedDurationStrategy` | WP-001 | ✅ Done |
| 3 | `ManualOnlyStrategy` | WP-001 | ✅ Done |
| 4 | `APICacheManager` | WP-002 | ✅ Done |
| 5 | `CacheableAPIMethodInterface` | WP-003 | ✅ Done |
| 6 | `CacheableAPIMethodTrait` | WP-003 | ✅ Done |
| 7 | `DBHelperAwareStrategy` | Phase 2 | ⬜ Pending |
| 8 | Modify `BaseAPIMethod::_process()` | WP-004 | ✅ Done |
| 9 | `APIResponseCacheLocation` | WP-005 | ✅ Done |
| 10 | `RegisterAPIResponseCacheListener` | WP-005 | ✅ Done |

---

## New Files to Create

All new files belong to the framework project.

### 1. Cache Strategy Interface

**File:** `src/classes/Application/API/Cache/APICacheStrategyInterface.php`  
**Namespace:** `Application\API\Cache`

```php
<?php

declare(strict_types=1);

namespace Application\API\Cache;

use AppUtils\FileHelper\JSONFile;

interface APICacheStrategyInterface
{
    /**
     * Returns a unique identifier for this strategy (e.g. 'FixedDuration', 'ManualOnly').
     *
     * @return string
     */
    public function getID() : string;

    /**
     * Given a cache file, returns whether it is still considered valid.
     *
     * @param JSONFile $cacheFile
     * @return bool
     */
    public function isCacheFileValid(JSONFile $cacheFile) : bool;
}
```

### 2. Fixed Duration Strategy

**File:** `src/classes/Application/API/Cache/Strategies/FixedDurationStrategy.php`  
**Namespace:** `Application\API\Cache\Strategies`

Modeled after the existing `Application\AI\Cache\Strategies\FixedDurationStrategy`.

```php
<?php

declare(strict_types=1);

namespace Application\API\Cache\Strategies;

use Application\API\Cache\APICacheStrategyInterface;
use AppUtils\FileHelper\JSONFile;

class FixedDurationStrategy implements APICacheStrategyInterface
{
    public const int DURATION_1MIN = 60;
    public const int DURATION_5MIN = 300;
    public const int DURATION_15MIN = 900;
    public const int DURATION_1HOUR = 3600;
    public const int DURATION_6HOURS = 21600;
    public const int DURATION_12HOURS = 43200;
    public const int DURATION_24HOURS = 86400;

    private int $durationInSeconds;

    public function __construct(int $durationInSeconds = self::DURATION_1HOUR)
    {
        $this->durationInSeconds = $durationInSeconds;
    }

    public function getID() : string
    {
        return 'FixedDuration';
    }

    public function isCacheFileValid(JSONFile $cacheFile) : bool
    {
        return (time() - filemtime($cacheFile->getPath())) < $this->durationInSeconds;
    }
}
```

### 3. Manual-Only Strategy

**File:** `src/classes/Application/API/Cache/Strategies/ManualOnlyStrategy.php`  
**Namespace:** `Application\API\Cache\Strategies`

```php
<?php

declare(strict_types=1);

namespace Application\API\Cache\Strategies;

use Application\API\Cache\APICacheStrategyInterface;
use AppUtils\FileHelper\JSONFile;

class ManualOnlyStrategy implements APICacheStrategyInterface
{
    public function getID() : string
    {
        return 'ManualOnly';
    }

    public function isCacheFileValid(JSONFile $cacheFile) : bool
    {
        return true;
    }
}
```

### 4. DBHelper-Aware Strategy

**File:** `src/classes/Application/API/Cache/Strategies/DBHelperAwareStrategy.php`  
**Namespace:** `Application\API\Cache\Strategies`

Extends `FixedDurationStrategy` and additionally declares which DBHelper collection classes the method depends on. When those collections fire create/delete events, the cache for the method is invalidated.

The TTL from the parent class acts as a **safety net** for record updates, since `BaseRecord::save()` does not fire a collection-level event.

```php
<?php

declare(strict_types=1);

namespace Application\API\Cache\Strategies;

class DBHelperAwareStrategy extends FixedDurationStrategy
{
    public const string STRATEGY_ID = 'dbhelper_aware';

    /**
     * @var class-string<\DBHelper_BaseCollection>[]
     */
    private array $collectionClasses;

    /**
     * @param class-string<\DBHelper_BaseCollection>[] $collectionClasses
     * @param int $durationInSeconds TTL safety net for record updates.
     */
    public function __construct(array $collectionClasses, int $durationInSeconds = self::DURATION_24HOURS)
    {
        parent::__construct($durationInSeconds);
        $this->collectionClasses = $collectionClasses;
    }

    public function getID(): string
    {
        return self::STRATEGY_ID;
    }

    /**
     * @return class-string<\DBHelper_BaseCollection>[]
     */
    public function getCollectionClasses(): array
    {
        return $this->collectionClasses;
    }
}
```

### 5. Cacheable API Method Interface

**File:** `src/classes/Application/API/Cache/CacheableAPIMethodInterface.php`  
**Namespace:** `Application\API\Cache`

```php
<?php

declare(strict_types=1);

namespace Application\API\Cache;

use Application\API\APIMethodInterface;

/**
 * Interface for API methods that support response caching.
 *
 * Use the trait {@see CacheableAPIMethodTrait} to implement this interface.
 *
 * @package API
 * @subpackage Cache
 * @see CacheableAPIMethodTrait
 */
interface CacheableAPIMethodInterface extends APIMethodInterface
{
    /**
     * Returns the cache strategy for this method.
     */
    public function getCacheStrategy(): APICacheStrategyInterface;

    /**
     * Returns the parameter names whose values contribute to the cache key.
     *
     * The cache key is built from: method name + version + sorted parameter values.
     * Return an empty array if the method has no parameters or if parameters
     * don't affect the response.
     *
     * @return string[]
     */
    public function getCacheKeyParameters(): array;

    /**
     * Generates the cache key hash for the given API version.
     */
    public function getCacheKey(string $version): string;

    /**
     * Reads cached response data if still valid.
     *
     * @return array<int|string,mixed>|null Cached data or null on miss.
     */
    public function readFromCache(string $version): ?array;

    /**
     * Writes response data to the cache.
     *
     * @param string $version
     * @param array<int|string,mixed> $data
     */
    public function writeToCache(string $version, array $data): void;

    /**
     * Deletes all cached entries for this method (all parameter combinations).
     */
    public function invalidateCache(): void;
}
```

### 6. Cacheable API Method Trait

**File:** `src/classes/Application/API/Cache/CacheableAPIMethodTrait.php`  
**Namespace:** `Application\API\Cache`

Provides the full default implementation. Methods using this trait only define `getCacheStrategy()` and `getCacheKeyParameters()`.

```php
<?php

declare(strict_types=1);

namespace Application\API\Cache;

use Application\AppFactory;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\FileHelper\JSONFile;

/**
 * Default implementation for {@see CacheableAPIMethodInterface}.
 *
 * Requires the implementing class to also extend {@see BaseAPIMethod}
 * (provides {@see self::getMethodName()} and {@see self::getParam()}).
 *
 * @package API
 * @subpackage Cache
 * @see CacheableAPIMethodInterface
 */
trait CacheableAPIMethodTrait
{
    public function getCacheKey(string $version): string
    {
        $parts = array($this->getMethodName(), $version);

        foreach ($this->getCacheKeyParameters() as $paramName) {
            $value = $this->getParam($paramName, '');
            if (is_array($value)) {
                $parts[] = $paramName . '=' . md5(serialize($value));
            } else {
                $parts[] = $paramName . '=' . (string)$value;
            }
        }

        return md5(implode('|', $parts));
    }

    public function getCacheFile(string $version): JSONFile
    {
        return JSONFile::factory(
            APICacheManager::getCacheFolder()
            . '/' . $this->getMethodName()
            . '/' . $this->getCacheKey($version) . '.json'
        );
    }

    public function readFromCache(string $version): ?array
    {
        $cacheFile = $this->getCacheFile($version);

        if (!$cacheFile->exists()) {
            return null;
        }

        if (!$this->getCacheStrategy()->isCacheFileValid($cacheFile)) {
            return null;
        }

        try
        {
            return $cacheFile->parse();
        }
        catch(\Throwable $e)
        {
            // Cache file is corrupt — log the event for operator observability, then
            // delete the file best-effort and signal a cache miss.
            AppFactory::createLogger()->logError(
                sprintf(
                    'Corrupt API cache file detected and deleted (error code %d). Path: %s | Error: %s',
                    APICacheException::ERROR_CACHE_FILE_CORRUPT,
                    $cacheFile->getPath(),
                    $e->getMessage()
                )
            );
            try { $cacheFile->delete(); } catch(\Throwable $ignored) {}
            return null;
        }
    }

    public function writeToCache(string $version, array $data): void
    {
        $this->getCacheFile($version)->putData($data);
    }

    public function invalidateCache(): void
    {
        APICacheManager::invalidateMethod($this->getMethodName());
    }
}
```

### 7. API Cache Manager

**File:** `src/classes/Application/API/Cache/APICacheManager.php`  
**Namespace:** `Application\API\Cache`

Static utility class for cache folder management, size calculation, per-method invalidation, and global clearing.

```php
<?php

declare(strict_types=1);

namespace Application\API\Cache;

use Application\Application;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FolderInfo;

class APICacheManager
{
    private const CACHE_SUBFOLDER = 'api/cache';

    /**
     * Returns the base cache folder, creating it if it does not exist.
     *
     * @return FolderInfo
     */
    public static function getCacheFolder() : FolderInfo
    {
        return FolderInfo::factory(Application::getStorageSubfolderPath(self::CACHE_SUBFOLDER));
    }

    /**
     * Returns the cache subfolder for a specific API method.
     * The folder is not created automatically.
     *
     * @param string $methodName
     * @return FolderInfo
     */
    public static function getMethodCacheFolder(string $methodName) : FolderInfo
    {
        return FolderInfo::factory(
            Application::getStorageSubfolderPath(self::CACHE_SUBFOLDER) . '/' . $methodName
        );
    }

    /**
     * Deletes all cached responses for a specific API method.
     * No-op if the method's cache folder does not exist.
     *
     * @param string $methodName
     * @return void
     * @throws APICacheException {@see APICacheException::ERROR_INVALID_METHOD_NAME}
     */
    public static function invalidateMethod(string $methodName) : void
    {
        $folder = self::getMethodCacheFolder($methodName);

        if($folder->exists())
        {
            FileHelper::deleteTree($folder);
        }
    }

    /**
     * Deletes all cached API response data.
     *
     * @return void
     */
    public static function clearAll() : void
    {
        $folder = self::getCacheFolder();

        if($folder->exists())
        {
            FileHelper::deleteTree($folder);
        }
    }

    /**
     * Returns the total byte size of all files in the cache folder.
     * Returns 0 if the folder does not exist or is empty.
     *
     * @return int
     */
    public static function getCacheSize() : int
    {
        $folder = self::getCacheFolder();

        if($folder->exists())
        {
            return $folder->getSize();
        }

        return 0;
    }
}
```

### 8. Cache Location (CacheControl Integration)

**File:** `src/classes/Application/API/Cache/APIResponseCacheLocation.php`  
**Namespace:** `Application\API\Cache`

```php
<?php

declare(strict_types=1);

namespace Application\API\Cache;

use Application\CacheControl\BaseCacheLocation;

class APIResponseCacheLocation extends BaseCacheLocation
{
    public const string CACHE_ID = 'APIResponseCache';

    public function getID(): string
    {
        return self::CACHE_ID;
    }

    public function getLabel(): string
    {
        return t('API Response Cache');
    }

    public function getByteSize(): int
    {
        return APICacheManager::getCacheSize();
    }

    public function clear(): void
    {
        APICacheManager::clearAll();
    }
}
```

### 9. Cache Location Registration Listener

**File:** `src/classes/Application/API/Events/RegisterAPIResponseCacheListener.php`  
**Namespace:** `Application\API\Events`

Follows the same pattern as the existing `RegisterAPIIndexCacheListener` in the same folder.

```php
<?php

declare(strict_types=1);

namespace Application\API\Events;

use Application\API\Cache\APIResponseCacheLocation;
use Application\CacheControl\Events\BaseRegisterCacheLocationsListener;

class RegisterAPIResponseCacheListener extends BaseRegisterCacheLocationsListener
{
    protected function getCacheLocations(): array
    {
        return array(new APIResponseCacheLocation());
    }
}
```

---

## Existing File to Modify

### `BaseAPIMethod::_process()` — Add Cache Check and Cache Write

**File:** `src/classes/Application/API/BaseMethods/BaseAPIMethod.php`

The `_process()` method currently looks like this (lines 142–179):

```php
private function _process(): void
{
    $this->time = Microtime::createNow();

    $this->validate();

    $version = $this->getActiveVersion();

    try {
        $this->collectRequestData($version);
    } catch (Throwable $e) {
        if($e instanceof APIResponseDataException) {
            throw $e;
        }

        $this->errorResponse(APIMethodInterface::ERROR_REQUEST_DATA_EXCEPTION)
            ->makeInternalServerError()
            ->setErrorMessage('Failed collecting request data: %s', $e->getMessage())
            ->send();
    }

    $response = ArrayDataCollection::create();

    try {
        $this->collectResponseData($response, $version);
    } catch (Throwable $e) {
        if($e instanceof APIResponseDataException) {
            throw $e;
        }

        $this->errorResponse(APIMethodInterface::ERROR_RESPONSE_DATA_EXCEPTION)
            ->makeInternalServerError()
            ->setErrorMessage('Failed collecting response data: %s', $e->getMessage())
            ->send();
    }

    $this->sendSuccessResponse($response);
}
```

**Target state** after modification:

```php
private function _process(): void
{
    $this->time = Microtime::createNow();

    $this->validate();

    $version = $this->getActiveVersion();

    // Serve from cache if available.
    if ($this instanceof CacheableAPIMethodInterface) {
        $cached = $this->readFromCache($version);
        if ($cached !== null) {
            $this->sendSuccessResponse(ArrayDataCollection::create($cached));
        }
    }

    try {
        $this->collectRequestData($version);
    } catch (Throwable $e) {
        if($e instanceof APIResponseDataException) {
            throw $e;
        }

        $this->errorResponse(APIMethodInterface::ERROR_REQUEST_DATA_EXCEPTION)
            ->makeInternalServerError()
            ->setErrorMessage('Failed collecting request data: %s', $e->getMessage())
            ->send();
    }

    $response = ArrayDataCollection::create();

    try {
        $this->collectResponseData($response, $version);
    } catch (Throwable $e) {
        if($e instanceof APIResponseDataException) {
            throw $e;
        }

        $this->errorResponse(APIMethodInterface::ERROR_RESPONSE_DATA_EXCEPTION)
            ->makeInternalServerError()
            ->setErrorMessage('Failed collecting response data: %s', $e->getMessage())
            ->send();
    }

    // Write response to cache on miss.
    if ($this instanceof CacheableAPIMethodInterface) {
        $this->writeToCache($version, $response->getData());
    }

    $this->sendSuccessResponse($response);
}
```

**Changes:**
1. Add `use Application\API\Cache\CacheableAPIMethodInterface;` to the imports.
2. Insert the cache check block (5 lines) after `getActiveVersion()`.
3. Insert the cache write block (3 lines) before the final `sendSuccessResponse()`.

**Why this position works:**
- `validate()` runs before the cache check because parameter values are needed for the cache key (via `getParam()` in the trait's `getCacheKey()`).
- `sendSuccessResponse()` is typed `never` — on a cache hit, execution stops there. On cache miss, it falls through to the existing pipeline.
- `processReturn()` (test mode) works identically: `sendSuccessResponse()` throws `APIResponseDataException` in return mode, which `processReturn()` catches — no change needed.

---

## DBHelper Automatic Invalidation (Phase 2)

This adds a manager that wires collection events to cache invalidation automatically.

### Invalidation Manager

**File:** `src/classes/Application/API/Cache/APICacheInvalidationManager.php`  
**Namespace:** `Application\API\Cache`

```php
<?php

declare(strict_types=1);

namespace Application\API\Cache;

use Application\API\APIManager;
use Application\API\Cache\Strategies\DBHelperAwareStrategy;

class APICacheInvalidationManager
{
    /**
     * Registers AfterCreateRecord and AfterDeleteRecord listeners on all
     * DBHelper collections referenced by cacheable API methods using the
     * DBHelperAwareStrategy.
     *
     * Call once during application boot (e.g., from a startup listener).
     */
    public static function registerListeners(): void
    {
        $bindings = self::collectBindings();

        foreach ($bindings as $collectionClass => $methodNames) {
            $collection = new $collectionClass();

            $invalidator = static function () use ($methodNames): void {
                foreach ($methodNames as $methodName) {
                    APICacheManager::invalidateMethod($methodName);
                }
            };

            $collection->onAfterCreateRecord($invalidator);
            $collection->onAfterDeleteRecord($invalidator);
        }
    }

    /**
     * Scans all API methods for CacheableAPIMethodInterface implementations
     * using DBHelperAwareStrategy and builds a map:
     *   collection class → [method names that depend on it]
     *
     * @return array<class-string<\DBHelper_BaseCollection>, string[]>
     */
    private static function collectBindings(): array
    {
        $bindings = array();
        $api = APIManager::getInstance();

        foreach ($api->getMethodCollection()->getAll() as $method) {
            if (!$method instanceof CacheableAPIMethodInterface) {
                continue;
            }

            $strategy = $method->getCacheStrategy();

            if (!$strategy instanceof DBHelperAwareStrategy) {
                continue;
            }

            foreach ($strategy->getCollectionClasses() as $collectionClass) {
                if (!isset($bindings[$collectionClass])) {
                    $bindings[$collectionClass] = array();
                }
                $bindings[$collectionClass][] = $method->getMethodName();
            }
        }

        return $bindings;
    }
}
```

### How to Wire the Invalidation Manager

The `registerListeners()` call should be placed in a framework startup hook or called from the application's bootstrap. Options:

- **Option A:** Create an offline event listener for a `BootCompleted` or similar event.
- **Option B:** Call `APICacheInvalidationManager::registerListeners()` from `APIManager::process()` on first call (lazy).
- **Option C:** Let each application call it explicitly from its bootstrap.

**Recommendation:** Option B (lazy in `APIManager::process()`) keeps it automatic and requires no application-level changes.

---

## Storage Layout

```
{APP_STORAGE}/api/cache/             ← APICacheManager::getCacheFolder()
  ├── GetTenantsAPI/                 ← one folder per method name
  │   └── a1b2c3d4e5f6...json       ← hash of method+version+params
  ├── GetComtypesAPI/
  │   ├── f6g7h8i9j0k1...json       ← tenant_id=1
  │   └── l2m3n4o5p6q7...json       ← tenant_id=2
  └── GetMailingLayoutAPI/
      ├── r8s9t0u1v2w3...json       ← template_id=5, locale=de_DE
      └── x4y5z6a7b8c9...json       ← template_id=5, locale=en_US
```

Per-method subdirectories allow `invalidateMethod()` to delete one method's entire cache (all parameter combinations) with a single folder delete, without affecting other methods.

---

## New File Summary

| # | File | Namespace | Type |
|---|---|---|---|
| 1 | `src/classes/Application/API/Cache/APICacheStrategyInterface.php` | `Application\API\Cache` | Interface |
| 2 | `src/classes/Application/API/Cache/Strategies/FixedDurationStrategy.php` | `Application\API\Cache\Strategies` | Class |
| 3 | `src/classes/Application/API/Cache/Strategies/ManualOnlyStrategy.php` | `Application\API\Cache\Strategies` | Class |
| 4 | `src/classes/Application/API/Cache/Strategies/DBHelperAwareStrategy.php` | `Application\API\Cache\Strategies` | Class |
| 5 | `src/classes/Application/API/Cache/CacheableAPIMethodInterface.php` | `Application\API\Cache` | Interface |
| 6 | `src/classes/Application/API/Cache/CacheableAPIMethodTrait.php` | `Application\API\Cache` | Trait |
| 7 | `src/classes/Application/API/Cache/APICacheManager.php` | `Application\API\Cache` | Class (static) |
| 8 | `src/classes/Application/API/Cache/APIResponseCacheLocation.php` | `Application\API\Cache` | Class |
| 9 | `src/classes/Application/API/Events/RegisterAPIResponseCacheListener.php` | `Application\API\Events` | Listener |
| 10 | `src/classes/Application/API/Cache/APICacheInvalidationManager.php` | `Application\API\Cache` | Class (Phase 2) |

**Modified file:**

| File | Change |
|---|---|
| `src/classes/Application/API/BaseMethods/BaseAPIMethod.php` | Add cache check + cache write in `_process()`, add import |

---

## Coding Conventions Checklist

Per the framework's `constraints.md`:

- [ ] `declare(strict_types=1)` in every new file.
- [ ] `array()` syntax for all array creation — never `[]`.
- [ ] No PHP enums, no `readonly` properties.
- [ ] camelCase for methods/properties, PascalCase for classes, UPPER_SNAKE_CASE for constants.
- [ ] `ClassHelper::requireObjectInstanceOf()` for type assertions.
- [ ] `FolderInfo::factory()` / `JSONFile::factory()` for file I/O — never raw `file_get_contents`.
- [ ] `t()` for user-facing strings (labels).
- [ ] Run `composer dump-autoload` after creating new files (classmap autoloading).

---

## Testing Plan

### Unit Tests for Cache Infrastructure

| Test | Validates |
|---|---|
| `FixedDurationStrategy::isCacheFileValid()` returns `true` for fresh file | TTL logic |
| `FixedDurationStrategy::isCacheFileValid()` returns `false` for expired file | TTL expiry |
| `ManualOnlyStrategy::isCacheFileValid()` always returns `true` | Manual-only behavior |
| `CacheableAPIMethodTrait::getCacheKey()` is deterministic | Same inputs → same hash |
| `CacheableAPIMethodTrait::getCacheKey()` varies by version | Key includes version |
| `CacheableAPIMethodTrait::getCacheKey()` varies by parameters | Key includes param values |
| `APICacheManager::invalidateMethod()` deletes only the target method folder | Per-method isolation |
| `APICacheManager::clearAll()` deletes all method folders | Global clear |

### Integration Tests Using `processReturn()`

| Test | Validates |
|---|---|
| Call `processReturn()` twice — second returns same data and is served from cache file | Cache write + read |
| Call `processReturn()`, then `invalidateCache()`, then `processReturn()` — second computes fresh | Invalidation works |
| Cache file is written to expected path under `{APP_STORAGE}/api/cache/{MethodName}/` | Storage layout |
| `APIResponseCacheLocation::getByteSize()` returns >0 after caching | CacheControl integration |
| `APIResponseCacheLocation::clear()` removes all cached responses | CacheControl clear |

### Test Scope

Use `composer test-file` for individual test files and `composer test-filter` for pattern matching. **Never run the full test suite.**

---

## Implementation Phases

### Phase 1: Core Caching (Minimum Viable)

1. Create the `APICacheStrategyInterface`.
2. Create `FixedDurationStrategy` and `ManualOnlyStrategy`.
3. Create `CacheableAPIMethodInterface` and `CacheableAPIMethodTrait`.
4. Create `APICacheManager`.
5. Modify `BaseAPIMethod::_process()` (add cache check + write).
6. Create `APIResponseCacheLocation` and `RegisterAPIResponseCacheListener`.
7. Run `composer dump-autoload`.
8. Write unit tests for strategies and cache key generation.
9. Write integration test using a test API method that implements `CacheableAPIMethodInterface`.

### Phase 2: DBHelper Invalidation

1. Create `DBHelperAwareStrategy`.
2. Create `APICacheInvalidationManager`.
3. Wire `registerListeners()` into the application boot sequence.
4. Write integration tests that verify create/delete on a collection triggers cache invalidation.

### Phase 3: Adopt in HCP Editor (Separate Project)

After the framework work is complete, convert HCP Editor API methods to use caching. Good candidates in priority order:

| Method | Strategy | Key Parameters | Rationale |
|---|---|---|---|
| `GetCountriesAPI` | `ManualOnlyStrategy` | none | Static data, never changes at runtime |
| `GetMailingOutputFormatsAPI` | `ManualOnlyStrategy` | none | Static enumeration |
| `GetMailingLayoutAreaRolesAPI` | `ManualOnlyStrategy` | none | Static enumeration |
| `GetGlobalContentStatesAPI` | `ManualOnlyStrategy` | none | Static enumeration |
| `GetMailServersAPI` | `ManualOnlyStrategy` | none | Static configuration |
| `GetTenantsAPI` | `FixedDuration(24h)` | none | Rarely changes |
| `GetComtypesAPI` | `DBHelperAware([ComtypesCollection], 6h)` | `tenant_id`, `tenant_name` | Changes on comtype CRUD |
| `GetBusinessAreasAPI` | `DBHelperAware([BusinessAreasCollection], 6h)` | `mail_server` | Changes on area CRUD |
| `GetMailingLayoutsAPI` | `FixedDuration(1h)` | none | Template list changes occasionally |
| `GetMailingLayoutAPI` | `FixedDuration(15min)` | `template_id`, `locale` | Template details change on editing |
| `GetComgroupsAPI` | `DBHelperAware([ComGroupsCollection], 6h)` | none | Changes on group CRUD |
| `GetVariableSourcesAPI` | `FixedDuration(1h)` | none | Variable sources change infrequently |

**Not cacheable** (mutations): `CreateMailAPI`, `CreateMailAudienceAPI`, and any POST/PUT/DELETE methods.  
**Not recommended** (unbounded key space): `GetMailExportBatchAPI` (accepts up to 250 mail IDs).

---

## Reference: Existing Patterns Used as Models

| Pattern | Example in Codebase | Location |
|---|---|---|
| Interface + Trait for API methods | `DryRunAPIInterface` / `DryRunAPITrait` | `src/classes/Application/API/Traits/` |
| `instanceof` check in `_process()` | _New_ (but follows the same structural pattern as checking for `APIKeyMethodInterface` in `initReservedParams()`) | `BaseAPIMethod.php` |
| Cache strategy with `filemtime()` | `Application\AI\Cache\Strategies\FixedDurationStrategy` | `src/classes/Application/AI/Cache/Strategies/` |
| Cache location for CacheControl | `APICacheLocation` (API method index) | `src/classes/Application/AppFactory/APICacheLocation.php` |
| Cache location listener | `RegisterAPIIndexCacheListener` | `src/classes/Application/API/Events/` |
| JSONFile for cache storage | `APIMethodIndex::getDataFile()` | `src/classes/Application/API/Collection/APIMethodIndex.php` |
| FolderInfo for folder ops | `BaseAICacheStrategy::getCacheFolder()` | `src/classes/Application/AI/Cache/BaseAICacheStrategy.php` |
| Collection events | `AfterCreateRecordEvent` / `AfterDeleteRecordEvent` | `src/classes/DBHelper/BaseCollection/Event/` |

```
###  Path: `/docs/agents/readme.md`

```md
# AGENT GUIDES

## Purpose

Canonical coding paradigms and patterns used across this project. Agents and contributors
should follow these rules when implementing interfaces, traits, and classes.

## Documents Index

- [Array Handling in the Framework](array-handling.md)
- [Coding Guidelines](coding-guidelines.md)
- [Coding Patterns](coding-patterns.md)
- [Exception Usage](exception-usage.md)
- [File Handling](file-handling.md)
- [Framework Folder Structure](folder-structure.md)
- [JSON Handling](json-handling.md)
- [UI Localization](ui-localization.md)
- [WHATSNEW XML Changelog Maintenance](whatsnew-editing.md)

## Individual Module Documentation

- [Application\Composer](/src/classes/Application/Composer/README.md) - Build-time module documentation generators (ModulesOverview, KeywordGlossary, BuildMessages)
- [DBHelper](/src/classes/DBHelper/README.md) - The database abstraction layer
- [Connectors](/src/classes/Connectors/README.md) - External service connection layer
- [UI](/src/classes/UI/README.md) - The user interface layer

```
###  Path: `/docs/agents/references/module-context-reference.md`

```md
# Module Context File Reference

Reference guide for creating and maintaining `module-context.yaml` files in the Application Framework project.

---

## Purpose

`module-context.yaml` files define what context documents the CTX generator produces for a given module. They serve two roles:

1. **CTX Generation** — The `documents` section tells the CTX generator which source files to extract, how to filter them, and where to write the output Markdown files in `.context/`.
2. **Module Metadata** — The `moduleMetaData` section provides machine-readable identity and relationship data for the module, used by tooling (e.g., a module index generator).

---

## File Location

Each `module-context.yaml` is placed **at the root of its module directory**, co-located with the source code it describes:

```
src/classes/DBHelper/module-context.yaml
src/classes/Connectors/module-context.yaml
src/classes/UI/module-context.yaml
src/classes/UI/DataGrid/module-context.yaml        # submodule
src/classes/UI/Bootstrap/module-context.yaml       # submodule
```

### Discovery

The root `context.yaml` auto-discovers all module-context files via a glob import:

```yaml
import:
  - path: "src/classes/**/module-context.yaml"
```

This means new modules are picked up automatically — no registration step is needed.

### Submodules

A module's subdirectory can have its own `module-context.yaml` when the submodule is large enough to warrant separate context documents. The parent-child relationship is implicit from the directory tree. Examples:

- `UI/` → parent module (`ui`)
  - `UI/DataGrid/` → submodule (`ui-datagrid`)
  - `UI/Bootstrap/` → submodule (`ui-bootstrap`)
  - `UI/Form/` → submodule (`ui-form`)
  - `UI/Tree/` → submodule (`ui-tree`)

---

## File Structure

A `module-context.yaml` has two top-level sections:

```yaml
# 1. Module metadata (custom, ignored by CTX schema)
moduleMetaData:
  id: "db-helper"
  label: "DBHelper"
  description: "Provides database abstraction for manual SQL operations and an ORM-like record collection system."
  relatedModules:
    - event-handler
    - ui

# 2. Document definitions (CTX schema)
documents:
  - description: '...'
    outputPath: '...'
    sources:
      - ...
```

---

## Section 1: `moduleMetaData`

Custom metadata block. The CTX generator ignores unknown top-level keys, so this is safe to include without affecting generation.

### Fields

| Field | Required | Type | Description |
|---|---|---|---|
| `id` | Yes | string | Machine-friendly lowercase slug. Must be unique across all modules. Used as an identifier in `relatedModules` references and should match the output folder name in `.context/modules/{id}/`. |
| `label` | Yes | string | Human-readable module name for display purposes. |
| `description` | Yes | string | One-sentence summary of the module's purpose and responsibility. Should answer "what does this module do?" |
| `relatedModules` | No | string[] | List of other module `id` values that this module has a significant relationship with. Captures cross-module dependencies that cannot be inferred from the directory tree. |

### Conventions

- **`id` format**: Lowercase, hyphen-separated. Examples: `db-helper`, `event-handler`, `ui-datagrid`, `ui-admin-urls`.
- **`description` length**: One sentence, ~10-25 words. Focus on the module's responsibility, not its implementation.
- **`relatedModules`**: Only list modules with direct, significant relationships (data dependencies, shared domain concepts). Do not list every module that happens to import a class.

### Example

```yaml
moduleMetaData:
  id: "ui-datagrid"
  label: "UI DataGrid"
  description: "Renders tabular data with built-in column sorting, pagination, per-user column configuration, and bulk actions."
  keywords:
    - DataGrid (tabular list component with column sorting, pagination, and bulk actions)
  relatedModules:
    - ui
    - db-helper
```

### Keyword Value Syntax Constraints

> **Warning:** Keyword values containing a colon followed by a space (`: `) **must be quoted**. Symfony YAML 
> parses an unquoted `word: text` as a mapping key, not a string scalar.

#### Why this matters

The `keywords` list items are YAML scalar strings. If a list item contains `: ` (colon + space), Symfony YAML
interprets everything before the colon as a mapping key and the rest as a nested mapping value. The
`ModulesOverviewGenerator::buildModuleInfo()` method receives an associative `array` instead of a `string`,
causing an `Array to string conversion` error during `composer build`.

#### Examples

```yaml
# CORRECT — plain string, no colon+space issue
keywords:
  - DataGrid (tabular list component with sorting and pagination)

# CORRECT — quoted string, colon+space is safe inside quotes
keywords:
  - "CacheableAPIMethodTrait: provides transparent read-through caching for API methods"

# BROKEN — Symfony YAML parses this as { "CacheableAPIMethodTrait" => "provides caching" }
keywords:
  - CacheableAPIMethodTrait: provides caching
```

**Rule:** If a keyword value must contain `: `, wrap the entire value in double quotes.

---

## Section 2: `documents`

Defines the context documents generated by the CTX generator. Each entry produces one Markdown file in `.context/`.

### Standard Document Types

Most modules should produce these document types (where applicable):

| Document | Output Path Pattern | Purpose |
|---|---|---|
| **Overview** | `modules/{id}/overview.md` | Module README and supplementary documentation. Human-written prose explaining purpose, concepts, and usage. |
| **Core Architecture** | `modules/{id}/architecture-core.md` | Public API signatures extracted from core module classes (collections, records, services, events, utilities). |
| **UI Architecture** | `modules/{id}/architecture-ui.md` | Public API signatures from the Admin UI layer (`Admin/` folder). |
| **API Methods** | `modules/{id}/architecture-api-methods.md` | Public API signatures from API method classes (`API/` folder). |
| **File Structure** | `modules/{id}/file-structure.md` | Directory tree of the module's files. Useful for large modules. |

Additional documents can be added when a module has distinct subdomains worth separating (e.g., `architecture-filters.md`, `architecture-events.md` for the DBHelper module).

### Document Entry Fields

```yaml
- description: 'DBHelper - Core Architecture'
  outputPath: 'modules/db-helper/architecture-core.md'
  overwrite: true                    # optional, default: true
  sources:
    - type: file
      description: "Interfaces and abstract classes"
      sourcePaths:
        - ./BaseCollection
        - ./BaseRecord
        - ./Interfaces
      excludePatterns:               # optional
        - 'Admin/'
      filePattern: "*.php"
      contains:                      # optional: only include files containing these strings
        - "interface"
        - "abstract class"
      modifiers:
        - name: php-content-filter
          options:
            method_visibility: [ "public" ]
            exclude_methods: [ "__construct" ]
            property_visibility: [ "public" ]
            constant_visibility: [ "public" ]
            keep_method_bodies: false
            keep_doc_comments: true
```

### Source Types

#### `file` — Extract content from files

The primary source type. Reads PHP or Markdown files and optionally applies filters.

**Key properties:**
- `sourcePaths`: Directories or files to scan, relative to the `module-context.yaml` location.
- `filePattern`: Glob for filename matching (e.g., `"*.php"`, `"README.md"`).
- `contains`: Optional string filter — only include files that contain at least one of these strings.
- `excludePatterns`: Optional directory/file patterns to skip.
- `modifiers`: Post-processing filters applied to the extracted content.

#### `tree` — Generate a directory tree

Produces an ASCII directory tree visualization.

```yaml
- type: tree
  description: 'PHP Files Structure'
  sourcePaths:
    - ./
  filePattern: '*'
  renderFormat: ascii
  showCharCount: true
  maxDepth: 5
```

### The `php-content-filter` Modifier

This is the most important modifier. It extracts public API signatures from PHP files while stripping implementation details.

**Standard configuration** (use this for architecture documents):

```yaml
modifiers:
  - name: php-content-filter
    options:
      method_visibility: [ "public" ]
      exclude_methods: [ "__construct" ]
      property_visibility: [ "public" ]
      constant_visibility: [ "public" ]
      keep_method_bodies: false
      keep_doc_comments: true
```

**What this produces:**
- Public method signatures (name, parameters, return type) — no method bodies.
- Public properties and constants.
- PHPDoc comments for context.
- Class/interface/trait declarations with extends/implements.

**What it strips:**
- Private and protected members.
- Method implementations.
- Constructor signatures (excluded by convention).

---

## Output Path Conventions

All output paths are relative to the `.context/` directory.

### For top-level modules

```
modules/{module-id}/overview.md
modules/{module-id}/architecture-core.md
modules/{module-id}/architecture-ui.md
modules/{module-id}/architecture-api-methods.md
```

### For submodules

Submodules nest under their parent's output folder:

```
modules/{parent-id}/{submodule-slug}/overview.md
modules/{parent-id}/{submodule-slug}/architecture-core.md
```

**Examples:**

| Module (id) | Parent | Output Folder |
|---|---|---|
| `ui` | — | `modules/ui/` |
| `ui-datagrid` | ui | `modules/ui/datagrid/` |
| `ui-bootstrap` | ui | `modules/ui/bootstrap/` |
| `ui-form` | ui | `modules/ui/form/` |
| `ui-tree` | ui | `modules/ui/tree/` |

Note: The output folder slug does not have to match the `id` exactly. Use judgement for readability.

---

## Complete Example

A typical module with overview, core architecture, and UI architecture:

```yaml
## ------------------------------------------------------------
## DBHELPER MODULE
## ------------------------------------------------------------

moduleMetaData:
  id: "db-helper"
  label: "DBHelper"
  description: "Provides database abstraction for manual SQL operations and an ORM-like record collection system with filtering, events, and CRUD operations."
  keywords:
    - Collection (ORM-like container of typed database records with CRUD, filtering, and events)
    - Record (typed wrapper for a single database row with field accessors and lifecycle events)
  relatedModules:
    - event-handler
    - ui

documents:

- description: 'DBHelper - Overview'
  outputPath: 'modules/db-helper/overview.md'
  sources:
    - type: file
      description: "Documentation"
      sourcePaths:
        - README.md
      filePattern: "README.md"

- description: 'DBHelper - Core Architecture'
  outputPath: 'modules/db-helper/architecture-core.md'
  sources:
    - type: file
      description: "Public Interfaces and APIs"
      sourcePaths:
        - ./BaseCollection
        - ./BaseRecord
        - ./Interfaces
        - ./Traits
      filePattern: "*.php"
      modifiers:
        - name: php-content-filter
          options:
            method_visibility: [ "public" ]
            exclude_methods: [ "__construct" ]
            property_visibility: [ "public" ]
            constant_visibility: [ "public" ]
            keep_method_bodies: false
            keep_doc_comments: true

- description: 'DBHelper - Admin UI Architecture'
  outputPath: 'modules/db-helper/architecture-ui.md'
  sources:
    - type: file
      description: "Public Interfaces and APIs"
      sourcePaths:
        - ./Admin
      filePattern: "*.php"
      modifiers:
        - name: php-content-filter
          options:
            method_visibility: [ "public" ]
            exclude_methods: [ "__construct" ]
            property_visibility: [ "public" ]
            constant_visibility: [ "public" ]
            keep_method_bodies: false
            keep_doc_comments: true
```

---

## Existing Modules Registry

Current modules with `module-context.yaml` files in the Framework:

| Module | Location |
|---|---|
| Connectors | `src/classes/Connectors/` |
| DBHelper | `src/classes/DBHelper/` |
| Event Handler | `src/classes/Application/EventHandler/` |
| Application Sets | `src/classes/Application/AppSets/` |

---

## When to Create a New `module-context.yaml`

Create one when a code area:

1. **Has its own domain** — it represents a distinct functional concern (e.g., "variable management", "tenant administration").
2. **Has enough public API surface** — at least several classes with public methods worth documenting.
3. **Has or should have a README** — if it warrants a README, it warrants a context file.
4. **Is large enough to benefit from separate context** — a handful of simple helper classes probably don't need their own context.

### Checklist for a New Module

- [ ] Create `module-context.yaml` in the module's root directory.
- [ ] Add `moduleMetaData` with `id`, `label`, `description`, and `relatedModules`.
- [ ] Add at least an Overview document sourcing from `README.md`.
- [ ] Add architecture documents for the public API surface using `php-content-filter`.
- [ ] If the module has an Admin UI layer, add a separate UI architecture document.
- [ ] If the module has API methods, add a separate API methods document.
- [ ] Ensure the `id` is unique and follows the lowercase-hyphenated convention.
- [ ] Run `ctx generate` and verify the output in `.context/`.

---

## Maintenance Guidelines

### When to Update

- **New public classes or interfaces added** — verify the `sourcePaths` in architecture documents include the new directories.
- **Module restructured** — update `sourcePaths` and `excludePatterns` to match.
- **New submodule created** — create a new `module-context.yaml` in the submodule directory.
- **Cross-module dependency added/removed** — update `relatedModules`.

### What NOT to Include

- **Private/internal implementation** — the `php-content-filter` handles this, but also avoid pointing `sourcePaths` at purely internal helper directories that have no public API.
- **Test files** — tests have their own structure and are not part of module context.
- **Generated or vendored code** — only source files authored within the module.

### Validation

After any changes, run `ctx generate` and verify:

1. The output files are created in `.context/modules/{id}/`.
2. The architecture documents contain only public signatures (no method bodies).
3. The overview document includes the README content.
4. No errors are reported by the CTX generator.

```
###  Path: `/docs/agents/research/2026-03-13-api-caching-system.md`

```md
# Research Report: API Method Response Caching System

## Problem Statement

The framework's API method layer has no built-in response caching. Every API call — even for rarely-changing data like tenant lists, comtype catalogs, or layout metadata — executes the full pipeline (parameter validation → request data collection → response data collection → serialization) on every request. This is wasteful for the many read-only GET methods that return stable data.

The goal is to design a flexible, file-based caching system for API method responses that supports multiple invalidation strategies: fixed-duration TTL, manual invalidation, and automatic invalidation when underlying DBHelper collections change.

## Problem Decomposition

1. **Cache interception point**: Where in the `BaseAPIMethod::_process()` pipeline to intercept and serve cached responses.
2. **Opt-in mechanism**: How individual API methods declare themselves as cacheable and configure their caching behavior.
3. **Cache key generation**: How to produce deterministic, collision-free keys from method name + version + parameters.
4. **Cache storage**: File-based storage layout under `{APP_STORAGE}`.
5. **Invalidation strategies**: Fixed-duration, manual-only, and DBHelper-collection-aware automatic invalidation.
6. **DBHelper integration**: Mapping collections to API methods and listening for mutation events to trigger invalidation.
7. **Cache management UI**: Integration with the existing CacheControl system (CacheManager + admin screen).
8. **Test mode compatibility**: Ensuring `processReturn()` works correctly with caching.

## Context & Constraints

- **PHP 8.4+**, `declare(strict_types=1)` required.
- **`array()` syntax** — not `[]` — for all array creation.
- **No enums, no `readonly` properties.**
- **Classmap autoloading** — `composer dump-autoload` after new files.
- **File-based storage only** — no Redis, Memcached, or APCu. All caching must use the filesystem under `Application::getCacheFolder()`.
- **Existing patterns to follow**: AI cache strategies (`BaseAICacheStrategy`, `FixedDurationStrategy`) use `JSONFile` + `filemtime()` validation. CacheControl system (`CacheLocationInterface`, `BaseCacheLocation`) provides admin UI integration.
- **50+ API methods** exist in the HCP Editor project across modules (tenants, comtypes, templates, global contents, variables, connectors). Approximately 70% are read-only GET methods — prime caching candidates.
- **DBHelper events** available for invalidation: `AfterCreateRecordEvent`, `AfterDeleteRecordEvent` on collections; `KeyModifiedEvent` on records. No `AfterSave` event — only the `_postSave()` hook exists on records.
- **The `_process()` pipeline is `private`** — caching logic must be added inside `BaseAPIMethod` itself or via a check within `_process()`.

## Prior Art & Known Patterns

### Pattern 1: Interface + Trait Opt-In (Recommended)

- **Description:** API methods declare cacheability by implementing a `CacheableAPIMethodInterface` and using a `CacheableAPIMethodTrait`. The `_process()` method in `BaseAPIMethod` checks for the interface and shortcuts the pipeline when a valid cache entry exists. This mirrors the existing `JSONResponseInterface` / `JSONResponseTrait` and `DryRunAPIInterface` / `DryRunAPITrait` composition patterns already used in the framework.
- **Where used:** This is the established pattern throughout the framework's API layer for optional capabilities (JSON responses, API key auth, dry-run mode).
- **Strengths:** Fully opt-in, zero impact on non-cacheable methods. Each method controls its own cache key, strategy, and TTL. Follows the project's existing architecture exactly.
- **Weaknesses:** Requires a small change to `BaseAPIMethod::_process()` (adding the cache check). Each cacheable method must explicitly implement the interface.
- **Fit:** **Excellent.** Aligns perfectly with the interface+trait composition pattern already governing the API method layer.

### Pattern 2: Decorator / Middleware Wrapper

- **Description:** A `CachingAPIMethodDecorator` wraps any `APIMethodInterface` instance. `APIManager::loadMethod()` would wrap cacheable methods in the decorator before calling `process()`. The decorator checks the cache before delegating to the wrapped method.
- **Where used:** Common in PSR-15 middleware stacks (Slim, Laravel, Symfony).
- **Strengths:** Clean separation of concerns — caching logic is completely isolated from method logic.
- **Weaknesses:** Breaks the framework's established composition model. `BaseAPIMethod::process()` and `sendSuccessResponse()` are `final` / `private` / call `Application::exit()` — decorating around them requires fighting the architecture. The `processReturn()` test mode relies on exception-based flow control that a decorator would need to replicate exactly. `APIMethodInterface` has 20+ methods, making delegation boilerplate heavy.
- **Fit:** **Poor.** Fights the existing architecture rather than working with it.

### Pattern 3: Subclass-Level Manual Caching

- **Description:** Each API method individually implements caching in its `collectResponseData()` — checking a cache file, returning early if valid, or writing the cache after computing the response.
- **Where used:** `GetMailForgeAppInfo` in the HCP Editor uses a static property for single-request caching. This is the only existing example.
- **Strengths:** No framework changes needed.
- **Weaknesses:** Massive code duplication across 30+ methods. No centralized invalidation. No admin UI integration. No standard cache key generation. Every developer reinvents the wheel.
- **Fit:** **Poor** as a systematic solution. Acceptable only for one-off cases.

### Pattern 4: APIManager-Level Response Cache

- **Description:** `APIManager::process()` checks a cache before instantiating the method class at all. If a cached response exists for the method name + parameters, it sends the cached JSON directly without constructing or executing the method object.
- **Where used:** Reverse proxy caches (Varnish, Nginx), CDN caching.
- **Strengths:** Maximum performance — skips object construction entirely. Centralized.
- **Weaknesses:** Cannot access the method instance to determine cache configuration (TTL, key components), because the method hasn't been instantiated yet. Would require a separate configuration registry mapping method names to cache policies. Cannot generate proper response envelopes (the `api` metadata block with `requestTime`, `selectedVersion`, etc.) without the method instance. Doesn't support `processReturn()` test mode.
- **Fit:** **Moderate.** Fast, but requires a parallel configuration system and loses response metadata accuracy.

## Alternative & Creative Approaches

### Hybrid: Interface Opt-In + Lazy Manager Cache Check

Combine Pattern 1 and Pattern 4: `APIManager` instantiates the method (to access its cache configuration), then checks the cache *before* calling `process()`. If the cache hits, the manager sends the cached response directly. If it misses, it calls `process()` normally, and `BaseAPIMethod::_process()` writes the response to cache after `collectResponseData()` completes.

- **Rationale:** Gets the configuration from the method instance (Pattern 1 benefit) while keeping the cache-hit path extremely fast (Pattern 4 benefit). The method is instantiated (cheap — just constructor + `init()` for parameter registration) but the expensive `collectRequestData()` + `collectResponseData()` are skipped on cache hit.
- **Risk:** Parameter validation must still run before the cache check, because the cache key depends on validated parameter values. This means the full validation step executes even on cache hits. However, validation is cheap compared to database queries.

This hybrid is essentially Pattern 1 with the cache check positioned optimally inside `_process()`.

### DBHelper Invalidation via Offline Event Listeners

Rather than requiring API methods to manually register their collection dependencies at runtime, use the same **offline event listener** pattern the framework already uses for `RegisterCacheLocationsEvent`. An offline event like `RegisterAPICacheBindingsEvent` would let each module declare which collections invalidate which API methods — discovered at build time, cached in a JSON index.

- **Rationale:** Keeps invalidation configuration decoupled from runtime. The index can be rebuilt with `composer build`. Follows the established offline event pattern.
- **Risk:** Adds one more build step. The binding declarations must be kept in sync with actual method dependencies.

## Comparative Evaluation

| Criterion | Pattern 1: Interface+Trait | Pattern 2: Decorator | Pattern 3: Manual | Pattern 4: Manager-Level | Hybrid |
|---|---|---|---|---|---|
| **Complexity** | Low | High | None (per method: high) | Moderate | Low-Moderate |
| **Performance (cache hit)** | Good (skips collect*) | Good | Good | Best (skips instantiation) | Good |
| **Performance (cache miss)** | Negligible overhead | Moderate overhead | None | Low overhead | Negligible overhead |
| **Maintainability** | Excellent (follows existing patterns) | Poor (fights architecture) | Poor (duplication) | Moderate (separate config) | Excellent |
| **Risk** | Low | High | Low | Moderate | Low |
| **Time to implement** | Moderate | High | Per-method cost | Moderate | Moderate |
| **Framework alignment** | Perfect | Poor | N/A | Partial | Perfect |
| **Test mode support** | Full | Complex | Full | Complex | Full |
| **Admin UI integration** | Via CacheControl | Via CacheControl | None | Via CacheControl | Via CacheControl |

## Recommendation

**Pattern 1 (Interface + Trait Opt-In)** is the clear winner. It follows the framework's established composition model exactly, requires minimal changes to `BaseAPIMethod`, and gives each API method full control over its caching behavior.

The DBHelper invalidation binding should use a **method-declared approach** (the method itself declares which collections it depends on) rather than an external registry, since this keeps the knowledge close to the code that uses it.

### Recommended Architecture

#### 1. New Interface: `CacheableAPIMethodInterface`

```php
namespace Application\API\Cache;

interface CacheableAPIMethodInterface
{
    /**
     * Returns the cache strategy for this method.
     */
    public function getCacheStrategy(): APICacheStrategyInterface;

    /**
     * Returns the parameter names whose values contribute to the cache key.
     * The cache key is built from: method name + version + sorted parameter values.
     * Return an empty array if the method takes no parameters (or parameters
     * don't affect the response).
     *
     * @return string[]
     */
    public function getCacheKeyParameters(): array;
}
```

#### 2. New Trait: `CacheableAPIMethodTrait`

Provides default implementations for cache key generation, cache file resolution, cache read/write, and invalidation. Methods using the trait only need to implement `getCacheStrategy()` and `getCacheKeyParameters()`.

```php
namespace Application\API\Cache;

trait CacheableAPIMethodTrait
{
    public function getCacheKey(string $version): string
    {
        $parts = array($this->getMethodName(), $version);

        foreach ($this->getCacheKeyParameters() as $paramName) {
            $value = $this->getParam($paramName, '');
            $parts[] = $paramName . '=' . (is_array($value) ? md5(serialize($value)) : (string)$value);
        }

        return md5(implode('|', $parts));
    }

    public function getCacheFile(string $version): JSONFile
    {
        return JSONFile::factory(
            APICacheManager::getCacheFolder() . '/'
            . $this->getMethodName() . '/'
            . $this->getCacheKey($version) . '.json'
        );
    }

    public function readFromCache(string $version): ?array
    {
        $file = $this->getCacheFile($version);

        if (!$file->exists()) {
            return null;
        }

        if (!$this->getCacheStrategy()->isCacheFileValid($file)) {
            return null;
        }

        return $file->parse();
    }

    public function writeToCache(string $version, array $data): void
    {
        $this->getCacheFile($version)->putData($data);
    }

    public function invalidateCache(): void
    {
        $folder = FolderInfo::factory(
            APICacheManager::getCacheFolder() . '/' . $this->getMethodName()
        );

        if ($folder->exists()) {
            $folder->delete();
        }
    }
}
```

#### 3. Cache Strategies

Reuse the existing pattern from the AI cache system:

| Strategy | Class | Behavior |
|---|---|---|
| **FixedDuration** | `FixedDurationCacheStrategy` | TTL-based via `filemtime()` check. Configurable duration (1h, 6h, 12h, 24h, custom). |
| **ManualOnly** | `ManualOnlyCacheStrategy` | Never expires on its own. Only cleared via admin UI or programmatic `invalidateCache()` call. |
| **DBHelperAware** | `DBHelperAwareCacheStrategy` | Extends FixedDuration with collection binding. Declares which `DBHelper_BaseCollection` classes it depends on. Automatically invalidated when those collections fire `AfterCreateRecordEvent` or `AfterDeleteRecordEvent`. Falls back to TTL if events are missed (safety net). |

```php
namespace Application\API\Cache\Strategies;

interface APICacheStrategyInterface
{
    public function getID(): string;
    public function isCacheFileValid(JSONFile $cacheFile): bool;
}

class FixedDurationCacheStrategy implements APICacheStrategyInterface
{
    // Same pattern as AI's FixedDurationStrategy.
    // Validates: (time() - filemtime($file)) < $durationInSeconds
}

class ManualOnlyCacheStrategy implements APICacheStrategyInterface
{
    // isCacheFileValid() always returns true (file exists = valid).
}

class DBHelperAwareCacheStrategy extends FixedDurationCacheStrategy
{
    /**
     * @param class-string<DBHelper_BaseCollection>[] $collectionClasses
     */
    public function __construct(array $collectionClasses, int $durationInSeconds = self::DURATION_24_HOURS)
    {
        parent::__construct($durationInSeconds);
        $this->collectionClasses = $collectionClasses;
    }

    /**
     * @return class-string<DBHelper_BaseCollection>[]
     */
    public function getCollectionClasses(): array
    {
        return $this->collectionClasses;
    }
}
```

#### 4. Interception Point in `BaseAPIMethod::_process()`

Add a cache check after validation (because parameter values are needed for the cache key) but before `collectRequestData()` and `collectResponseData()`:

```php
private function _process(): void
{
    $this->time = Microtime::createNow();

    $this->validate();

    $version = $this->getActiveVersion();

    // --- NEW: Cache check ---
    if ($this instanceof CacheableAPIMethodInterface) {
        $cached = $this->readFromCache($version);
        if ($cached !== null) {
            $this->sendSuccessResponse(ArrayDataCollection::create($cached));
            // sendSuccessResponse() is `never` — execution stops here.
        }
    }
    // --- END cache check ---

    try {
        $this->collectRequestData($version);
    } catch (Throwable $e) {
        // ... existing error handling
    }

    $response = ArrayDataCollection::create();

    try {
        $this->collectResponseData($response, $version);
    } catch (Throwable $e) {
        // ... existing error handling
    }

    // --- NEW: Write to cache ---
    if ($this instanceof CacheableAPIMethodInterface) {
        $this->writeToCache($version, $response->getData());
    }
    // --- END write to cache ---

    $this->sendSuccessResponse($response);
}
```

This adds only two small blocks to `_process()`. On cache hit, the method short-circuits before any database access. On cache miss, the response is written to the cache file after being computed.

#### 5. DBHelper Invalidation Listener

A single `APICacheInvalidationListener` wired to collections at boot time:

```php
namespace Application\API\Cache;

class APICacheInvalidationManager
{
    /**
     * Registers event listeners on all collections referenced by
     * DBHelperAwareCacheStrategy instances. Called once during application boot.
     */
    public static function registerListeners(APIMethodCollection $methods): void
    {
        // Build a map: collection class → [method names]
        $bindings = self::collectBindings($methods);

        foreach ($bindings as $collectionClass => $methodNames) {
            $collection = self::instantiateCollection($collectionClass);

            $invalidator = static function () use ($methodNames): void {
                foreach ($methodNames as $methodName) {
                    APICacheManager::invalidateMethod($methodName);
                }
            };

            $collection->onAfterCreateRecord($invalidator);
            $collection->onAfterDeleteRecord($invalidator);
        }
    }
}
```

**Important design decision:** The `_postSave()` hook on records is *not* an event and cannot be listened to externally. For record *updates* to trigger invalidation, the recommended approach is:

- Use `KeyModifiedEvent` on individual records (fine-grained but requires per-record registration — impractical for bulk).
- **Or** rely on the TTL safety net from `FixedDurationCacheStrategy` — updates will be picked up when the TTL expires. This is the pragmatic recommendation for v1.
- **Or** add an `AfterSaveRecordEvent` to collections in a future framework version (separate enhancement).

For v1, the DBHelper-aware strategy provides automatic invalidation on **create** and **delete**, with TTL fallback for **updates**. This covers the overwhelming majority of use cases, since record creation and deletion change the shape of list-style API responses far more than field-level updates do.

#### 6. Cache Storage Layout

```
{APP_STORAGE}/api/cache/
  ├── GetTenantsAPI/
  │   └── a1b2c3d4e5...json        ← cached response (key = hash of method+version+params)
  ├── GetComtypesAPI/
  │   ├── f6g7h8i9j0...json        ← tenant_id=1
  │   └── k1l2m3n4o5...json        ← tenant_id=2
  ├── GetMailingLayoutAPI/
  │   ├── p6q7r8s9t0...json        ← template_id=5, locale=de_DE
  │   └── u1v2w3x4y5...json        ← template_id=5, locale=en_US
  ...
```

Per-method subdirectories allow `invalidateCache()` to delete an entire method's cache with a single `rmdir()` without affecting other methods.

#### 7. CacheControl Integration

Register an `APICacheLocation` (or rename the existing one) that reports the total size of `{APP_STORAGE}/api/cache/` and clears it:

```php
class APIResponseCacheLocation extends BaseCacheLocation
{
    public const string CACHE_ID = 'APIResponseCache';

    public function getID(): string { return self::CACHE_ID; }
    public function getLabel(): string { return t('API Response Cache'); }

    public function getByteSize(): int
    {
        return APICacheManager::getCacheSize();
    }

    public function clear(): void
    {
        APICacheManager::clearAll();
    }
}
```

Register it via a `RegisterCacheLocationsEvent` listener. This makes the API response cache visible and clearable in the admin cache control screen.

#### 8. Usage Example (HCP Editor Method)

```php
namespace Maileditor\Tenants\API\Methods;

use Application\API\Cache\CacheableAPIMethodInterface;
use Application\API\Cache\CacheableAPIMethodTrait;
use Application\API\Cache\Strategies\FixedDurationCacheStrategy;

class GetTenantsAPI extends BaseJSONMethod implements CacheableAPIMethodInterface
{
    use CacheableAPIMethodTrait;

    public function getCacheStrategy(): APICacheStrategyInterface
    {
        return new FixedDurationCacheStrategy(
            FixedDurationCacheStrategy::DURATION_24_HOURS
        );
    }

    public function getCacheKeyParameters(): array
    {
        return array(); // No parameters → single cache entry
    }

    // ... existing collectResponseData() unchanged
}
```

```php
namespace Maileditor\Comtypes\API\Methods;

class GetComtypesAPI extends BaseJSONMethod implements CacheableAPIMethodInterface
{
    use CacheableAPIMethodTrait;

    public function getCacheStrategy(): APICacheStrategyInterface
    {
        return new DBHelperAwareCacheStrategy(
            array(ComtypesCollection::class),
            FixedDurationCacheStrategy::DURATION_6_HOURS
        );
    }

    public function getCacheKeyParameters(): array
    {
        return array('tenant_id', 'tenant_name');
    }

    // ... existing collectResponseData() unchanged
}
```

### Proof-of-Concept Outline

1. Create `Application\API\Cache\` namespace with: `CacheableAPIMethodInterface`, `CacheableAPIMethodTrait`, `APICacheManager` (static utility for folder paths, size calculation, clearing).
2. Create `Application\API\Cache\Strategies\` with: `APICacheStrategyInterface`, `FixedDurationCacheStrategy`, `ManualOnlyCacheStrategy`.
3. Add the two cache check/write blocks to `BaseAPIMethod::_process()`.
4. Create `APIResponseCacheLocation` and its `RegisterCacheLocationsEvent` listener.
5. Convert one simple HCP Editor method (`GetCountriesAPI` — no parameters, fully static data) to implement `CacheableAPIMethodInterface`.
6. Verify: first call computes and caches; second call serves from cache; admin UI shows cache size and allows clearing.
7. Add `DBHelperAwareCacheStrategy` and `APICacheInvalidationManager`.
8. Convert a parameterized method (`GetComtypesAPI`) to use `DBHelperAwareCacheStrategy`.
9. Verify: creating a comtype in the admin UI invalidates the cached response.

## Open Questions

- **Record updates:** The `save()` method on `BaseRecord` does not fire an event. Should we add an `AfterSaveRecordEvent` to `BaseCollection` as a prerequisite, or is the TTL safety net acceptable for v1? The TTL approach means updates are picked up within the TTL window (e.g., 6 hours) rather than immediately.
- **Cache warmup:** Should there be a mechanism to pre-warm caches (e.g., a CLI command or admin action that calls `processReturn()` on all cacheable methods)? This could be useful after a deployment that clears the cache folder.
- **HTTP cache headers:** Should cacheable methods also set `Cache-Control`, `ETag`, or `Last-Modified` HTTP headers? This would enable client-side and CDN caching as a complementary layer. It's orthogonal to server-side response caching but could be added to the trait.
- **Cache key collision safety:** The MD5 hash of method+version+params is extremely unlikely to collide, but should the cache file also store the original key components for verification? This adds a small read overhead but eliminates theoretical collision risk.
- **Connector methods:** The HCP Editor also has 20+ connector methods (Pigeon, Hubspot, etc.) that call external APIs. These already have `Connectors_Request_Cache` for HTTP-level caching. Should they also use the new API response cache, or is one layer sufficient? They serve different purposes: connector cache caches the raw external response; API response cache caches the serialized API output.
- **Maximum cache size:** Should there be a configurable limit on total cache folder size, with LRU eviction? For file-based caching this adds complexity (scanning `filemtime` across all files). Possibly unnecessary if TTLs keep the cache self-pruning.

## References

- `BaseAPIMethod::_process()` — [src/classes/Application/API/BaseMethods/BaseAPIMethod.php](src/classes/Application/API/BaseMethods/BaseAPIMethod.php) (lines 142–179)
- AI cache strategies — [src/classes/Application/AI/Cache/](src/classes/Application/AI/Cache/)
- CacheControl system — [src/classes/Application/CacheControl/](src/classes/Application/CacheControl/)
- DBHelper collection events — [src/classes/DBHelper/BaseCollection/Event/](src/classes/DBHelper/BaseCollection/Event/)
- DBHelper record events — [src/classes/DBHelper/BaseRecord/Event/](src/classes/DBHelper/BaseRecord/Event/)
- HCP Editor API methods — [assets/classes/](assets/classes/) (Maileditor project, 37+ method classes)
- Only existing manual cache: `GetMailForgeAppInfo` — [assets/classes/MailForge/APIConnector/Method/GetMailForgeAppInfo.php](assets/classes/MailForge/APIConnector/Method/GetMailForgeAppInfo.php) (HCP Editor)
- Project brief — [docs/agents/projects/api-caching-system.md](docs/agents/projects/api-caching-system.md)

```
###  Path: `/docs/agents/ui-localization.md`

```md
# UI Localization

## Best Practices for Localizing User Interface Strings

- **Translation Function**: Wrap user-facing strings with `t()` from the `AppLocalize` package.
- **Placeholder Syntax**: Use placeholders for dynamic content with numbered placeholders, e.g. `%1$s`.
- **Placeholder Behavior**: Placeholders in translation functions work exactly like `sprintf`.
- **Splitting Sentences**: Systematically split sentences into multiple calls to `t()` for maximum reusability of texts.

Example:

```php
function getWelcomeMessage($username) {
    return t('Welcome, %1$s!', $username);
}
```

## Providing Context for Translators

For more complex texts with many placeholders or when the placeholder content is
difficult to infer from the context, context for translators can be added by using 
the `tex()` function. 

Example:

```php
function getOrderSummary($orderNumber, $itemCount, $totalPrice) 
{
    return tex(
        'Your order %1$s contains %2$s items totaling %3$s.',
        'Order summary message with 1) order number, 2) item count, and 3) total price.',
        $orderNumber,
        $itemCount,
        $totalPrice
    );
}
```

```
###  Path: `/docs/agents/whatsnew-editing.md`

```md
# Agent Guide: XML Changelog Maintenance

## Overview

The Application Framework project maintains a version changelog in XML format. These files document changes for end users across multiple languages. This guide explains the structure, constraints, and best practices for maintaining these changelog files.

## File Organization

### The Primary Changelog

The changelog is application-specific, and has a fixed location where it is
expected to be saved by the WhatsNew handling system:

- **File location:** `/WHATSNEW.xml`

### Changelog History Folder

To keep the size of the main `WHATSNEW.xml` file in check, previous versions
are archived in the changelog history folder, where a file is created for each
minor version within a folder for the main version.

Here is an example:

```
docs/changelog-history/
└── v19/              # Major version 19 (split by minor versions)
    ├── v19.0.xml
    ├── v19.1.xml
    ├── v19.2.xml
    ├── v19.3.xml
    ├── v19.4.xml
    ├── v19.5.xml
    ├── v19.6.xml
    └── v19.7.xml
```

### Organizational Rules

1. **Subfolders for each major version:** Each major version gets its own subfolder in the changelog history folder.
2. **Splitting by minor version**: Each minor version gets its own XML file within the major version folder.

## XML Structure

### Root Element

```xml
<?xml version="1.0" encoding="UTF-8"?>
<whatsnew>
    <version id="19.7.9">
        <!-- content -->
    </version>
</whatsnew>
```

### Version Entry Structure

Each version entry follows this structure:

```xml
<version id="MAJOR.MINOR.PATCH">
    <de>
        <!-- German language entries -->
    </de>
    <en>
        <!-- English language entries -->
    </en>
</version>
```

### Item Structure

Within each language block:

```xml
<de>
    <item category="Category Name">
        Description text that can span multiple lines
        and include formatting.
    </item>
    <item category="Another Category">
        Another change description.
    </item>
</de>
```

## Formatting Rules

### 1. XML Declaration

Always start with:
```xml
<?xml version="1.0" encoding="UTF-8"?>
```

### 2. Version Ordering

**Critical**: Versions must be ordered from newest to oldest (descending):

```xml
<whatsnew>
    <version id="19.7.9">...</version>  <!-- Newest first -->
    <version id="19.7.8">...</version>
    <version id="19.7.5">...</version>
    <version id="19.7.4">...</version>  <!-- Oldest last -->
</whatsnew>
```

### 3. Text Content

- Text content can span multiple lines
- Whitespace and indentation are preserved for readability
- Line breaks within text are natural and expected

### 4. Special Characters

XML special characters must be escaped:

| Character | Escaped Form | Example Context |
|-----------|--------------|-----------------|
| `<` | `&lt;` | `Menü Mail &gt; Erweitert` |
| `>` | `&gt;` | `Menü Mail &gt; Erweitert` |
| `&` | `&amp;` | `[Link](url?key=value&amp;other=123)` |
| `"` | `&quot;` | Used in attributes |

### 5. Markdown Links

Markdown-style links are supported within text:

```xml
<item category="API">
    Moe information in the [Online Guide](https://mistralys.eu/guide?paramA=A&amp;paramB=B).
</item>
```

**Important**: The `&` in URLs must be escaped as `&amp;`.

### 6. Code References

Use backticks for code/API references:

```xml
<item category="APIs">
    The `GetProducts`-API now contains all connected countries.
</item>
```

**Note**: Categories should be consistent within a language but may differ between German and English translations.

## Language Blocks

### Required Language Blocks

- `<de>`: German (primary language)
- `<en>`: English (required for all user-facing changes)

### Rules

1. **All user-facing changes must have both German and English entries**
2. **Content should match semantically between languages** (not necessarily word-for-word)
3. **Number of items should match** between `<de>` and `<en>` unless there's a good reason
4. **End-user facing changes only**, as developer-centric changes are handled separately

## Adding New Changelog Entries

### Step 1: Determine the Target File

New entries always go into the main `WHATSNEW.xml` file. 

> NOTE: The changelog version archiving is a separate process.

### Step 2: Create the Version Entry

```xml
<version id="19.7.10">
    <de>
        <item category="Appropriate Category">
            Deutsche Beschreibung der Änderung.
        </item>
    </de>
    <en>
        <item category="Appropriate Category">
            English description of the change.
        </item>
    </en>
</version>
```

### Step 3: Position the Entry

Insert the new version entry **at the top** of the file (after the root opening tag), maintaining descending version order.

**Before:**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<whatsnew>
    <version id="19.7.9">
        ...
    </version>
```

**After:**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<whatsnew>
    <version id="19.7.10">
        <de>
            <item category="System">
                Neue Funktion hinzugefügt.
            </item>
        </de>
        <en>
            <item category="System">
                Added new feature.
            </item>
        </en>
    </version>
    <version id="19.7.9">
        ...
    </version>
```

### Step 4: Multiple Changes in One Version

If a version has multiple changes, add multiple `<item>` elements within each language block:

```xml
<version id="19.7.10">
    <de>
        <item category="Mailings">
            Erste Änderung in Mailings.
        </item>
        <item category="API">
            Zweite Änderung in der API.
        </item>
        <item category="System">
            Dritte Änderung im System.
        </item>
    </de>
    <en>
        <item category="Mailings">
            First change in mailings.
        </item>
        <item category="API">
            Second change in the API.
        </item>
        <item category="System">
            Third change in the system.
        </item>
    </en>
</version>
```

## Complete Examples

### Example 1: Simple Bug Fix

```xml
<version id="19.7.10">
    <de>
        <item category="Mailings">
            Ein Fehler wurde behoben, der dazu führen konnte, dass
            Mailings nicht korrekt generiert wurden.
        </item>
    </de>
    <en>
        <item category="Mailings">
            A bug has been fixed that could cause mailings to not be
            generated correctly.
        </item>
    </en>
</version>
```

### Example 2: New Feature with API Reference

```xml
<version id="19.8.0">
    <de>
        <item category="API">
            Eine neue API wurde hinzugefügt: `CreateNotification`.
            Diese ermöglicht es, Push-Benachrichtigungen zu erstellen.
        </item>
        <item category="Benachrichtigungen">
            Push-Benachrichtigungen können jetzt über die Benutzeroberfläche
            verwaltet werden.
        </item>
    </de>
    <en>
        <item category="API">
            A new API has been added: `CreateNotification`.
            This allows creating push notifications.
        </item>
        <item category="Notifications">
            Push notifications can now be managed through the user interface.
        </item>
    </en>
</version>
```

### Example 3: With Links and Special Characters

```xml
<version id="19.8.2">
    <de>
        <item category="Dokumentation">
            Die API-Dokumentation wurde aktualisiert und ist jetzt
            unter [API Docs](https://maileditor.example.com/api?section=docs&amp;version=19.8)
            verfügbar.
        </item>
        <item category="Mailings">
            Das Menü wurde umstrukturiert: Mail &gt; Erweitert &gt; Einstellungen.
        </item>
    </de>
    <en>
        <item category="Documentation">
            The API documentation has been updated and is now available
            at [API Docs](https://maileditor.example.com/api?section=docs&amp;version=19.8).
        </item>
        <item category="Mailings">
            The menu has been restructured: Mail &gt; Advanced &gt; Settings.
        </item>
    </en>
</version>
```

## Validation Checklist

Before finalizing changelog entries, verify:

- [ ] XML declaration is present and correct
- [ ] Root element matches the file's existing convention
- [ ] Version ID is in `MAJOR.MINOR.PATCH` format
- [ ] New entry is positioned at the top (newest first)
- [ ] Both `<de>` and `<en>` blocks are present (for user-facing changes)
- [ ] Number of items matches between German and English
- [ ] Categories are consistent and follow established patterns
- [ ] Special characters (`<`, `>`, `&`) are properly escaped
- [ ] URLs in links use `&amp;` instead of `&`
- [ ] Code/API names are wrapped in backticks
- [ ] Text content is clear and concise
- [ ] File is properly indented (4 spaces per level)
- [ ] File ends with a closing `</whatsnew>` tag

## Tips and Best Practices

### 1. Writing Effective Changelog Entries

**Good:**
```xml
<item category="Mailings">
    Ein Fehler wurde behoben, der dazu führen konnte, dass Mailings
    mit bestimmten Inhalten nicht geöffnet werden konnten.
</item>
```

**Avoid (too vague):**
```xml
<item category="Mailings">
    Bug Fix.
</item>
```

### 2. Grouping Related Changes

Group related changes under the same version, but use separate items:

```xml
<version id="19.8.3">
    <de>
        <item category="Tenants">
            Neue Tenants wurden hinzugefügt: Brand A und Brand B.
        </item>
        <item category="Tenants">
            Die Tenant-Übersicht zeigt jetzt die Anzahl der Mailings
            pro Tenant an.
        </item>
    </de>
```

### 3. Version Number Gaps

It's normal to have gaps in patch versions (e.g., 19.7.5 followed by 19.7.8). 
This reflects internal development versioning, which is handled in a separate
developer-centric changelog.

### 4. Consistent Terminology

Maintain consistent terminology across versions:
- Use established category names
- Follow existing patterns for similar changes
- Review recent entries for style guidance

## Common Pitfalls

### ❌ Incorrect: Missing Translation

```xml
<version id="19.8.5">
    <de>
        <item category="API">
            Neue API hinzugefügt.
        </item>
    </de>
    <!-- Missing <en> block -->
</version>
```

### ❌ Incorrect: Unescaped Special Characters

```xml
<item category="Mailings">
    Menu: Mail > Advanced > Settings  <!-- Should be &gt; -->
    Link: https://example.com?key=value&other=123  <!-- Should be &amp; -->
</item>
```

### ❌ Incorrect: Wrong Version Order

```xml
<whatsnew>
    <version id="19.7.5">...</version>
    <version id="19.7.9">...</version>  <!-- Should be first -->
</whatsnew>
```

### ✅ Correct: Complete and Proper Entry

```xml
<version id="19.8.5">
    <de>
        <item category="API">
            Eine neue API wurde hinzugefügt: `GetStatistics`.
            Siehe [Dokumentation](https://example.com/api?method=GetStatistics&amp;v=19.8).
        </item>
    </de>
    <en>
        <item category="API">
            A new API has been added: `GetStatistics`.
            See [Documentation](https://example.com/api?method=GetStatistics&amp;v=19.8).
        </item>
    </en>
</version>
```

## Creating a New Major Version

When starting a new major version (e.g., v20):

1. **Create a subdirectory to archive the previous version**: `v19/`
2. **Create the minor version files**: `v19/v19.9.xml` (for each minor version in the WHATSNEW.xml)

### Creating a New Minor Version File

When creating a new minor version file (e.g., `v19.8.xml`):

1. **Create the file in the appropriate subdirectory** (e.g., `v19/`)
2. **Follow the naming convention**: `v{MAJOR}.{MINOR}.xml`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<whatsnew>
    <version id="19.8.0">
        <!-- First entry for this minor version -->
    </version>
</whatsnew>
```

## Integration with WHATSNEW.xml

The main `WHATSNEW.xml` file in the project root typically contains the most recent changes and may be periodically archived into the `docs/changelog-history/` structure. When archiving:

1. Copy relevant entries to the appropriate history file
2. Maintain version ordering (newest first)
3. Ensure all formatting and escaping is preserved
4. Update both XML and markdown versions if applicable

---

## Quick Reference

**Version Format**: `MAJOR.MINOR.PATCH` (e.g., `19.7.9`)

**File Location**:
- v13-v18: `docs/changelog-history/v{MAJOR}.xml`
- v19+: `docs/changelog-history/v{MAJOR}/v{MAJOR}.{MINOR}.xml`

**Required Blocks**: `<de>` and `<en>` for user-facing changes

**Entry Position**: Always at the top (newest first)

**Escape These**:
- `<` → `&lt;`
- `>` → `&gt;`
- `&` → `&amp;`

**Root Element**: Use `<whatsnew>`.

---

This guide should be updated as conventions evolve. When in doubt, examine recent entries in the appropriate version file for guidance.

```
---
**File Statistics**
- **Size**: 138.04 KB
- **Lines**: 4267
File: `framework-core-system-overview.md`
