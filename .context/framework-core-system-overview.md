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
        └── plans/
            ├── 2026-02-09-upgrade-helper-work.md
            ├── 2026-02-09-upgrade-helper.md
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
> Generated: 2026-03-04T14:52:14Z

# Keyword Glossary

| Keyword | Context | Module(s) |
|---------|---------|-----------|
| Admin URL | type-safe fluent builder for admin-screen navigation links using area/mode/submode routing | ui-admin-urls |
| APIEnvelope | standard OpenAPI success response envelope schema | api-openapi |
| APIErrorEnvelope | standard OpenAPI error response envelope schema | api-openapi |
| APIInfo | OpenAPI schema for the api metadata sub-object in every JSON response | api-openapi |
| Application Sets | configuration controlling which administration areas are active per application instance | application-sets |
| BigSelection widget | scrollable multi-item selector component built on Bootstrap v2 | ui-bootstrap |
| CKEditor 5 | WYSIWYG rich-text editor integrated through the Markup Editor abstraction | ui-markup-editor |
| Collection | ORM-like container of typed database records with CRUD, filtering, and events | db-helper |
| ComposerScripts | orchestrates all composer build steps: cache clearing, event/admin indexing, API method index, OpenAPI spec generation, .htaccess generation, CSS classes, context date, module docs | composer |
| convertParameter | converts a single API parameter to its OpenAPI representation; returns null for reserved parameters | api-openapi |
| convertParameters | batch-converts all parameters from APIParamManager into query/header and JSON-body buckets | api-openapi |
| convertResponses | returns a map of HTTP status codes to OpenAPI response objects for a given API method | api-openapi |
| DataGrid | tabular list component with column sorting, pagination, and bulk actions | ui-datagrid |
| DataTable | raw SQL result wrapper for manual query output | db-helper |
| Eventable | mixin trait that adds instance-level event emitter capabilities to any class | event-handler |
| FilterCriteria | query filter builder defining SQL WHERE conditions for a collection | db-helper |
| FilterSettings | persisted user-facing filter values for a collection's list view | db-helper |
| generateHtaccess | APIManager convenience method; generates the API .htaccess for RESTful URL rewriting using the running driver context | api-openapi |
| generateOpenAPISpec | APIManager convenience method; generates the OpenAPI spec using the running driver's app name and version | api-openapi |
| GetOpenAPISpec | framework built-in API method; serves the pre-generated openapi.json as raw JSON over HTTP at /api/GetOpenAPISpec; bypasses the standard JSON envelope; returns 500 when the spec file is missing | api-openapi |
| getSecuritySchemes | returns the components/securitySchemes definition for the HTTP Bearer API key scheme | api-openapi |
| getSpecURL | GetOpenAPISpec static helper; returns the absolute URL to the spec endpoint using APP_URL; used by APIMethodsOverviewTmpl to render the OpenAPI button on the API docs overview page | api-openapi |
| HtaccessGenerator | generates Apache .htaccess for RESTful URL rewriting of /api/ paths | api-openapi |
| HTML_QuickForm2 | underlying PHP form library wrapped and extended by the UI Form module | ui-form |
| HTTP connector | base class for building typed REST API clients with GET/POST/PUT/DELETE support | connectors |
| KeywordGlossaryGenerator | build-time tool that produces module-glossary.md from module keywords | composer |
| list builder | pluggable data source implementation that populates a DataGrid | ui-datagrid |
| load key | deduplication token ensuring each JS/CSS asset is injected once per page | ui-client-resources |
| MethodConverter | converts a single APIMethodInterface to an OpenAPI path item; adds security requirement for APIKeyMethodInterface methods and x-validation-rules for methods with parameter rules | api-openapi |
| ModulesOverviewGenerator | build-time tool that produces modules-overview.md from module-context.yaml files | composer |
| offline event listener | deferred JIT listener registered before its target class is instantiated | event-handler |
| OpenAPIGenerator | main orchestrator; iterates all API methods, assembles the complete OpenAPI 3.1 document including securitySchemes and x-validation-rules, writes it as a JSON file | api-openapi |
| OpenAPISchema | OpenAPI 3.1 component schemas for APIEnvelope, APIErrorEnvelope, APIInfo and security schemes for APIKeyMethodInterface authentication | api-openapi |
| page frame | container template defining the overall admin page structure — header, sidebar, footer | ui-page |
| ParameterConverter | converts APIParameterInterface instances to OpenAPI parameter objects or request body schema properties | api-openapi |
| Properties Grid | key/value table component for rendering record detail views | ui-properties-grid |
| Record | typed wrapper for a single database row with field accessors and lifecycle events | db-helper |
| Redactor | alternative WYSIWYG rich-text editor available through the Markup Editor | ui-markup-editor |
| ResponseConverter | converts API method response metadata into OpenAPI 3.1 response objects for 200/400/500 status codes | api-openapi |
| SECURITY_SCHEME_API_KEY | OpenAPISchema constant identifying the 'apiKey' security scheme name used in both components definition and per-method security requirements | api-openapi |
| StatementBuilder | fluent SQL SELECT/INSERT/UPDATE/DELETE query builder | db-helper |
| theme override | mechanism for transparently replacing framework templates at the application level | ui-themes |
| Tree widget | hierarchical navigation component with nestable nodes supporting icons and action buttons | ui-tree |
| TypeMapper | maps framework API parameter type labels to OpenAPI 3.1 type/format pairs | api-openapi |
| UI singleton | central access point for all framework rendering components | ui |
| x-validation-rules | OpenAPI extension field added by MethodConverter to document inter-parameter validation constraints such as OrRule and RequiredIfOtherIsSetRule | api-openapi |

```
###  Path: `/docs/agents/project-manifest/modules-overview.md`

```md
# Modules Overview

> Auto-generated on 2026-03-04 15:52:14. Do not edit manually.

Total: 17 modules across 1 package.

## mistralys/application_framework

| ID | Label | Description | Source Path | Context Docs | Related Modules |
|----|-------|-------------|-------------|--------------|-----------------|
| `api-openapi` | API OpenAPI | End-to-end support for generating OpenAPI 3.1 specifications from the framework API system and serving them over HTTP. Covers parameter type mapping (TypeMapper), reusable component schemas and security schemes for the standard API response envelopes (OpenAPISchema), Apache .htaccess generation for RESTful URL rewriting (HtaccessGenerator), conversion of framework API parameters to OpenAPI parameter objects and request body schema properties (ParameterConverter), conversion of API method response metadata to OpenAPI response objects for 200/400/500 status codes (ResponseConverter), full spec assembly with error resilience and authentication/validation documentation (OpenAPIGenerator, MethodConverter), HTTP serving of the pre-generated spec as raw JSON (GetOpenAPISpec), and application-level convenience entry points (APIManager::generateOpenAPISpec, APIManager::generateHtaccess). The composer build pipeline calls both generation steps automatically via ComposerScripts. | `src/classes/Application/API/OpenAPI/` | `.context/modules/openapi/` | — |
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
| **Test count** | ~141 unit test files + 2 integration test files |
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
- **Size**: 139.25 KB
- **Lines**: 4276
File: `framework-core-system-overview.md`
