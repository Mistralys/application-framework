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
        └── json-handling.md
        └── project-manifest/
            ├── README.md
            ├── constraints.md
            ├── context-documentation.md
            ├── module-glossary.md
            ├── modules-overview.md
            ├── testing.md
        └── readme.md
        └── references/
            ├── module-context-reference.md
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

## Testing Singletons

Singleton classes hold a static `$instance` that persists across test cases in the
same PHPUnit process. Without isolation, one test's state leaks into the next,
producing hard-to-diagnose failures.

**Convention:** any singleton that needs to be tested **must** expose a
`public static resetInstance() : void` method annotated with `@internal`. Test
classes covering that singleton **must** call `resetInstance()` in their
`tearDown()` method.

```php
<?php
// In the singleton class:

/**
 * Resets the singleton instance to null.
 *
 * @internal For use in tests only.
 * @return void
 */
public static function resetInstance() : void
{
    self::$instance = null;
}
```

```php
<?php
// In the corresponding test class:

protected function tearDown() : void
{
    MySingleton::resetInstance();
}
```

- `resetInstance()` is **unconditional** — no null-guard is needed because
  assigning `null` to an already-null property is a PHP no-op.
- `tearDown()` runs after every test, regardless of pass/fail, ensuring a clean
  slate for the next case.
- The `@internal` annotation signals that `resetInstance()` is not part of the
  public API and must not be called from production code.

See `IconCollection::resetInstance()` and `IconCollectionTest::tearDown()` for
the reference implementation.

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

---

## PHPStan Baseline (`phpstan-result.txt`)

### Rule

**Any commit that changes the PHPStan error count must also update `phpstan-result.txt` in the
same commit.** The file serves as a human-readable baseline snapshot of the current static-analysis
state. When the count drifts, agents in subsequent sessions compare against a stale baseline and
may incorrectly assess the static-analysis health of the codebase.

Regenerate with:

```
composer analyze-save
```

This is equivalent to running `composer analyze` and writing the output to `phpstan-result.txt`.

---

## Module Context YAML (`module-context.yaml`)

### Required fields

Every `module-context.yaml` file **must** include all three of these fields inside its `moduleMetaData` section:

| Field | Purpose |
|---|---|
| `id` | Unique machine-readable module identifier (used for glossary and overview indexing). |
| `label` | Human-readable display name. |
| `description` | Short description of the module's purpose. |

### Silent-skip behaviour

`ModuleInfoParser::parseFile()` is the single authoritative parser for all `module-context.yaml` files consumed by framework build generators (`KeywordGlossaryGenerator`, `ModulesOverviewGenerator`, and application-level generators). Its validation rules are:

1. The file must contain a `moduleMetaData` key that is an array.
2. All three fields — `id`, `label`, and `description` — must be present.

**If any required field is absent, the parser returns `null` and emits a `BuildMessages` warning; the file is silently skipped.** No exception is thrown. As a consequence:

- A module whose `module-context.yaml` has `id` and `keywords` but lacks `label` or `description` will **not** appear in the keyword glossary, even though the keywords are present.
- The same module will also be absent from the modules overview and any downstream JSON export.

To diagnose a missing module after `composer build`, inspect the build output for `ModuleInfoParser` warnings.

### Optional fields

`keywords` and `relatedModules` are optional and default to an empty array when omitted.


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
> Generated: 2026-04-29T08:45:29Z

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
| AppCountriesAPITrait | mix-in for API methods that need multiple country parameters | countries-api |
| AppCountriesParamInterface | plural interface declaring getCountries() | countries-api |
| AppCountriesParamsContainer | plural handler container — resolves to country array | countries-api |
| AppCountryAPITrait | mix-in for API methods that need a single country parameter | countries-api |
| AppCountryIDsValidation | per-ID validation for country ID lists | countries-api |
| AppCountryParamInterface | singular interface declaring getCountry() | countries-api |
| AppCountryParamsContainer | single-country handler container — resolves to one country | countries-api |
| Application Sets | configuration controlling which administration areas are active per application instance | application-sets |
| Application_Countries | singleton collection class extending DBHelper_BaseCollection; entry point for all country operations | countries |
| Application_Countries_Country | record class extending DBHelper_BaseRecord; represents a single country with ISO code, label, and locale bridge | countries |
| BigSelection widget | scrollable multi-item selector component built on Bootstrap v2 | ui-bootstrap |
| ButtonBar | persistent country selection widget storing user choice in user settings | countries |
| Cache strategy | pluggable caching policy per tool — fixed-duration or uncached | ai |
| CacheableAPIMethodInterface | interface API method classes implement to opt into file-based response caching; extends APIMethodInterface; requires getCacheStrategy() and getCacheKeyParameters() from the implementing class | api-cache |
| CacheableAPIMethodTrait | trait providing default implementations for getCacheKey, readFromCache, writeToCache, and invalidateCache; use alongside CacheableAPIMethodInterface | api-cache |
| CKEditor 5 | WYSIWYG rich-text editor integrated through the Markup Editor abstraction | ui-markup-editor |
| clearAll | APICacheManager static method; deletes the entire api/cache folder | api-cache |
| Collection | ORM-like container of typed database records with CRUD, filtering, and events | db-helper |
| common type | reusable domain-specific parameter preset like AliasParameter or EmailParameter | api-parameters |
| CommonMark | Markdown-to-HTML conversion engine used by the renderer | markdown-renderer |
| ComposerScripts | orchestrates all composer build steps: cache clearing, event/admin indexing, API method index, OpenAPI spec generation, .htaccess generation, CSS classes, context date, module docs | composer |
| convertParameter | converts a single API parameter to its OpenAPI representation; returns null for reserved parameters | api-openapi |
| convertParameters | batch-converts all parameters from APIParamManager into query/header and JSON-body buckets | api-openapi |
| convertResponses | returns a map of HTTP status codes to OpenAPI response objects for a given API method | api-openapi |
| CountriesCollection | utility wrapper for working with a resolved set of Application_Countries_Country instances; not a DBHelper collection | countries |
| countryID / countryISO | single-country parameter names | countries-api |
| countryIDs / countryISOs | multi-country parameter names | countries-api |
| CountrySettingsManager | Formable-based settings manager for country record editing | countries |
| Custom Tags | framework-specific tags like {media} and {api} processed around CommonMark | markdown-renderer |
| custom-icons.json | JSON source file defining application-specific FontAwesome icon mappings for the IconBuilder | composer |
| CustomIcon | application-level PHP class extending UI_Icon whose methods are auto-generated by the IconBuilder | composer |
| DataGrid | tabular list component with column sorting, pagination, and bulk actions | ui-datagrid |
| DataTable | raw SQL result wrapper for manual query output | db-helper |
| dry-run | optional mode where a method validates and reports what it would do without side effects | api |
| Eventable | mixin trait that adds instance-level event emitter capabilities to any class | event-handler |
| FilterCriteria | query filter builder defining SQL WHERE conditions for a collection | db-helper |
| FilterSettings | persisted user-facing filter values for a collection's list view | db-helper |
| FixedDurationStrategy | built-in strategy with STRATEGY_ID=FixedDuration; cache file is valid if it is younger than the configured duration in seconds; ships with named constants DURATION_1_MIN, DURATION_5_MIN, DURATION_15_MIN, DURATION_1_HOUR, DURATION_6_HOURS, DURATION_12_HOURS, DURATION_24_HOURS | api-cache |
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
| IconBuilder | build-time code generator that reads custom-icons.json and writes typed PHP and JS icon accessor methods into target files | composer |
| invalidateCache | CacheableAPIMethodTrait: deletes all cache files for this method by delegating to APICacheManager::invalidateMethod | api-cache |
| invalidateMethod | APICacheManager static method; deletes the entire MethodName cache subfolder; no-op if folder does not exist | api-cache |
| invariant country | special ISO code zz representing language-independent content; excluded by excludeInvariant filters | countries |
| isCacheFileValid | APICacheStrategyInterface method; given a JSONFile returns whether the cached response is still valid | api-cache |
| ISO alias | alternative ISO code mapped to the canonical code during lookup; rejected during creation | countries |
| JSON envelope | standard response wrapper with state/code/data/message keys | api |
| KeywordGlossaryGenerator | build-time tool that produces module-glossary.md from module keywords | composer |
| list builder | pluggable data source implementation that populates a DataGrid | ui-datagrid |
| load key | deduplication token ensuring each JS/CSS asset is injected once per page | ui-client-resources |
| LocaleCode | parses locale strings like de_DE into language and country components | countries |
| ManualOnlyStrategy | built-in strategy with STRATEGY_ID=ManualOnly; cached file never expires automatically; invalidation is triggered only via invalidateCache or APICacheManager::clearAll | api-cache |
| MarkdownRenderer | converts Markdown to styled HTML with CommonMark and custom tags | markdown-renderer |
| MCP server | Model Context Protocol server exposing tools over stdio transport | ai |
| method index | cached class map of all API methods; rebuilt by composer build | api |
| method whitelist | per-key list of API methods the key is authorized to call; managed by APIKeyMethods | api-clients |
| MethodConverter | converts a single APIMethodInterface to an OpenAPI path item; adds security requirement for APIKeyMethodInterface methods and x-validation-rules for methods with parameter rules | api-openapi |
| ModulesOverviewGenerator | build-time tool that produces modules-overview.md from module-context.yaml files | composer |
| Navigator | UI widget rendering a button bar for country selection | countries |
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
| Selector | form element for choosing countries from a filterable list | countries |
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

> Auto-generated on 2026-04-29 10:45:29. Do not edit manually.

Total: 25 modules across 1 package.

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
| `composer` | Application Composer | Build-time utilities that generate Markdown documentation artefacts (Modules Overview and Keyword Glossary) from module-context.yaml files discovered throughout the codebase, generate the OpenAPI 3.1 specification JSON, generate the API .htaccess for RESTful URL rewriting, and rebuild application custom icon methods from JSON definitions. Includes a shared BuildMessages registry for build-time notices. All steps are orchestrated by ComposerScripts::build(). | `src/classes/Application/Composer/` | `.context/modules/composer/` | event-handler |
| `connectors` | Connectors | Scaffold for building HTTP connector classes to access external APIs, supporting GET, POST, PUT, and DELETE methods. | `src/classes/Connectors/` | `.context/modules/connectors/` | — |
| `countries` | Countries | Country management following the DBHelper Collection/Record pattern. Application_Countries (singleton collection) manages Application_Countries_Country records, supporting lookup by ID or ISO code, ISO aliases (e.g. uk→gb), invariant country (zz) handling, locale code parsing, and filter criteria. Includes admin UI for country CRUD, AI tool integrations, domain events, user rights, and UI components (navigator, selector, button bar, flag icons). | `src/classes/Application/Countries/` | `.context/modules/countries/` | countries-api, db-helper, event-handler, ui, ai |
| `countries-api` | Countries API | Reusable trait-based infrastructure for country parameter handling in API methods. Provides two complementary patterns: AppCountryAPITrait (singular — resolves one Application_Countries_Country from countryID or countryISO) and AppCountriesAPITrait (plural — resolves Application_Countries_Country[] from countryIDs or countryISOs). Both enforce mutual exclusivity via OrRule. Parameter classes live in Params/; OrRule components live in ParamSets/. | `src/classes/Application/Countries/API/` | `.context/modules/countries/api/` | countries, api, api-parameters |
| `db-helper` | DBHelper | Provides database abstraction for manual SQL operations and an ORM-like record collection system with filtering, events, and CRUD operations. | `src/classes/DBHelper/` | `.context/modules/db-helper/` | event-handler, ui, ui-datagrid, application-sets |
| `event-handler` | Event Handling | Comprehensive event handling system supporting global events, instance-scoped Eventable objects, and offline just-in-time event listeners. | `src/classes/Application/EventHandler/` | `.context/modules/event-handler/` | ui, ui-form, db-helper, composer |
| `markdown-renderer` | Markdown Renderer | Converts Markdown text to styled HTML using CommonMark with GFM extensions, extended with custom tags for media library images and API documentation links. | `src/classes/Application/MarkdownRenderer/` | `.context/modules/markdown-renderer/` | ui |
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
- **countries** → countries-api, db-helper, event-handler, ui, ai
- **countries-api** → countries, api, api-parameters
- **db-helper** → event-handler, ui, ui-datagrid, application-sets
- **event-handler** → ui, ui-form, db-helper, composer
- **markdown-renderer** → ui
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
│   ├── GlobalTests/
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

## Test File Naming Convention

All unit test files under `tests/AppFrameworkTests/` must follow this convention:

| Element | Requirement | Example |
|---|---|---|
| **File name** | Must match the class name exactly (`.php` extension) | `CoreTest.php` |
| **Class name** | Must end in `Test`, PascalCase | `CoreTest` |
| **Namespace** | Must match the directory path under `AppFrameworkTests\` | `AppFrameworkTests\Disposables` |
| **Declaration** | Must include `declare(strict_types=1);` | — |

### Correct structure

```php
<?php

declare(strict_types=1);

namespace AppFrameworkTests\Disposables;

use AppFrameworkTestClasses\ApplicationTestCase;

class CoreTest extends ApplicationTestCase
{
    // ...
}
```

PHPUnit discovers tests by scanning the `tests/AppFrameworkTests/` directory tree for `.php` files. If the **class name does not match the file name**, PHPUnit emits a "Class X cannot be found" warning and the test is silently skipped. Always verify that both match before committing a new test file.

> **Note on `AppFrameworkTests\Global`:** `Global` is a reserved word in PHP. Tests that previously lived under the `Global/` directory have been placed in `AppFrameworkTests\GlobalTests` instead to avoid parser errors.

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

## Live-HTTP Tests

Some tests make real HTTP requests to `APP_URL` (the running test application). These tests are
marked with the `#[Group('live-http')]` PHP attribute (PHPUnit 13) and are **excluded from the
default `composer test` run** via the `<groups><exclude>` block in `phpunit.xml`.

> **PHPUnit 13 note:** The legacy `@group` docblock annotation is silently ignored by PHPUnit 13.
> Always use the `#[Group(...)]` PHP 8 attribute with a `use PHPUnit\Framework\Attributes\Group;`
> import. Using the annotation form has no effect and will not exclude the test from the default run.

To run live-HTTP tests, a web server must be available at `APP_URL` (configured in
`tests/application/config/test-ui-config.php` via the `TESTS_BASE_URL` constant). Run them
explicitly with:

```
composer test-group -- live-http
```

Tests in this group:

| Test Class | Methods |
|---|---|
| `AppFrameworkTests\Ajax\AjaxRequestTest` | All (class-level `#[Group('live-http')]`) |
| `AppFrameworkTests\Connectors\RequestTest` | `test_adapterSockets`, `test_adapterCURL` |

Do not remove the `#[Group('live-http')]` attribute or the `phpunit.xml` exclusion — without it,
the CI pipeline and local runs without a web server will fail with network errors.

---

## Superglobal Teardown

### Automatic $_REQUEST restore

`ApplicationTestCase` now automatically backs up `$_REQUEST` in `setUp()` and restores it in
`tearDown()`. This prevents inter-test pollution when test files write to `$_REQUEST` without
explicit cleanup.

**No per-class tearDown required for `$_REQUEST`.** Test classes that modify `$_REQUEST` no
longer need to manually unset those keys — the base class restore handles it globally.

### Other superglobals

`ApplicationTestCase` does **not** automatically restore `$_GET`, `$_POST`, or `$_SESSION`.
Test classes that write to these superglobals must still unset those keys in their own
`tearDown()` before calling `parent::tearDown()`.

### Why this matters

`BaseRecordSelectionTieIn::getRecord()` (and similar request-state-reading classes) caches
the first result it finds. If a previous test left a key in `$_REQUEST`, the next test in the
same file will read a stale cached value, causing assertions that depend on "nothing selected"
to fail — only when the tests are run as a file, not in isolation. This is a classic
inter-test pollution signature that is difficult to diagnose.

The automatic `$_REQUEST` backup/restore in `ApplicationTestCase` eliminates this category of
bug for the most common superglobal.

### Convention (for $_GET, $_POST, $_SESSION)

```php
protected function tearDown() : void
{
    // Unset every $_GET, $_POST, or $_SESSION key written by this test class.
    // (No need to unset $_REQUEST keys — ApplicationTestCase handles those automatically.)
    unset(
        $_GET['some_key'],
        $_POST['some_other_key']
    );

    parent::tearDown();
}
```

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
- **Size**: 182.16 KB
- **Lines**: 4099
File: `framework-core-system-overview.md`
