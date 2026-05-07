# Application Framework - Core System Overview
_SOURCE: Project README_
# Project README
```
// Structure of documents
└── README.md

```
###  Path: `README.md`

```md
<img src="src/themes/default/img/app-framework-logo.png" width="110" align="right">

# Application Framework

All-in-one PHP framework and UI layer for building web and intranet
applications.

## Introduction 

The framework is designed to be a solid foundation for custom-built web 
applications. The integrated functionality helps to focus on the application 
logic, while being able to create the necessary administration screens, 
APIs and more with minimal effort.

Note that it is not a CMS: it is exclusively a tool for building custom 
applications. Supporting features are available out of the box, like the 
notepad or image library, but anything your application needs to do must 
be implemented by you. 

One of the core functionalities of the framework is to provide an extensive
ecology of classes for accessing custom data stored in the database. 
This includes complex filtering capabilities as well as a versioning 
system with record state tracking (draft, published, etc.).

## Features overview

- PHP helper classes for UI elements
- Form building system - every screen is a form
- Rule-based application environment detection
- Advanced database-stored data handling tools
- Class-based extensible templating system
- Localization system for UI translation
- News central (release notes, etc.)
- Event handling system
- Image media library and UI
- Tagging system and UI
- Versioning system with state tracking
- Disposables system for automated garbage collection
- API response caching (file-based, opt-in via Interface + Trait; TTL and manual strategies)
- SSO via CAS
- Interface Translations: English, German, French
- Build-time module documentation generators (Modules Overview, Keyword Glossary)
- Own ecology of supporting libraries

## Requirements

- PHP 7.4 or higher (fully PHP 8 compatible)
- [Composer](https://getcomposer.org)
- MariaDB or MySQL database with InnoDB support
- Webserver 

## Installation

The framework can be installed as a regular Composer dependency.
However, the required application skeleton of folders and files
can currently only be generated dynamically using the 
[Framework Manager][], which is currently still a private project.

Documentation on how to set up an application using the framework
is still in progress. In the meantime, the example application can
be used as a reference (see [Example application](#example-application)).

## Example application

The framework includes a sample application which is used as a reference for 
available features, best practices, and testing. It can also be used as
the basis for a new application.

You will find it in the `tests/application` folder.

**Installation**

The quickest way to set up the example application is with the interactive
setup command:

```bash
composer setup
```

This will prompt you for database and UI settings, create the database if
it does not exist, import `docs/sql/pristine.sql`, and seed the test data
automatically. Re-running the command is safe — existing values are shown as
defaults and pressing Enter preserves them.

<details>
<summary>Manual setup (alternative)</summary>

1. Import the SQL file `docs/sql/pristine.sql` into a database.
2. Open the folder `tests/application/config`.
3. Copy `test-db-config.dist.php` to `test-db-config.php`.
4. Copy `test-ui-config.dist.php` to `test-ui-config.php`.
5. Edit the settings in both files.
6. Run `composer seed-tests` to seed the test data.

</details>

After setup, access the `tests/application` folder via the webserver.

> **Note:** The generated config files (`test-db-config.php`,
> `test-ui-config.php`) are listed in `.gitignore` and must never be
> committed to version control — they contain local credentials and are
> derived from the `.dist.php` templates.

## Composer commands

These are custom Composer commands that are available 
when developing locally.

### Build

The build step generates module documentation artefacts from `module-context.yaml`
files discovered throughout the codebase, updates the CTX `generated-at.txt`
timestamp, regenerates the API method index, and rebuilds offline event listeners.

```bash
composer build
```

For the full development build (includes module glossary and keyword index):

```bash
composer build-dev
```

### Setup

Interactive one-command setup for the local development environment.
Prompts for database and UI settings, generates `test-db-config.php` and
`test-ui-config.php` from their `.dist.php` templates, creates the database
if it does not exist, imports `docs/sql/pristine.sql`, and runs
`composer seed-tests` automatically on completion.

```bash
composer setup
```

Re-running the command is safe (idempotent): existing config values are read
back and shown as defaults; pressing Enter without typing preserves them.
If the database connection fails, the script re-prompts for credentials until
a successful connection is established.

The script can also be invoked directly:

```bash
php tools/setup-local.php
```

> **Requires an interactive terminal (TTY).** Do not pipe or redirect input to
> this command — `stty -echo` (used to suppress password echo) relies on an
> attached TTY and will behave unexpectedly in non-interactive contexts.

### Seed test database

Truncates all tables in the test database and re-populates them with the
standard framework seed data (system users, locales, and countries).
Run this once after importing `docs/sql/pristine.sql`, and again whenever
the test database needs to be reset to a known state.

```bash
composer seed-tests
```

The command runs two process-isolated steps in sequence:

1. `php tools/seed-truncate.php` — empties all base tables
2. `php tools/seed-insert.php` — inserts system users, locales, and countries

The command requires the test database to be accessible and configured
(see `tests/application/config/test-db-config.php`). It is **idempotent**:
running it on an already-seeded database produces the same result with
no errors. On failure it prints a human-readable error message and exits
with code 1, making it safe to use in CI pipelines.

### Clear caches

Clears all caches used by the framework, including the dynamic
class cache.

```bash
composer clear-caches
```

## Developer Tools

The `tools/` directory contains local-development CLI scripts. These are
version-controlled and intended for developer use only — they are not part
of the framework runtime.

### Getting started as a contributor

The fastest way to get your local environment up and running is the interactive
developer menu. From the project root, run:

```bash
# Unix / macOS
./menu.sh

# Windows
menu.cmd
```

The menu will automatically run `composer install` if `vendor/` is missing,
then present a numbered list of common tasks. Choose **option 1** to run the
interactive local-environment setup wizard, which configures the database and
generates the required config files.

### Setup Script

`tools/setup-local.php` is the interactive local-environment setup script
(see [`composer setup`](#setup) above). It can be run directly with
`php tools/setup-local.php` or via `composer setup`.

**Generated files and version control**

The script generates `tests/application/config/test-db-config.php` and
`tests/application/config/test-ui-config.php` from their `.dist.php`
counterparts. Both generated files are listed in `.gitignore` and **must not
be committed** — they contain local database credentials. The `.dist.php`
templates (which contain no credentials) are version-controlled and serve as
the canonical structure reference.

**Platform and TTY notes**

| Scenario | Behaviour |
|---|---|
| Unix / macOS (interactive TTY) | Password input is hidden via `stty -echo`. Ctrl-C restores echo via a `pcntl_signal` SIGINT handler. |
| Windows | Password input is **visible in the terminal** — `stty` is not available on Windows. A warning is printed before the password prompt. |
| Windows (some PHP builds) | `pcntl_signal` may not be compiled in. If you interrupt the script with Ctrl-C during a password prompt, terminal echo may remain suppressed. Run `stty echo` in your terminal to restore it manually. |
| Non-interactive / piped context | The script is not designed for non-interactive use. Running it without an attached TTY (e.g. via pipe or CI) may leave echo enabled and produce garbled output. |

**CAS authentication mode**

By default the setup script writes `TESTS_SESSION_TYPE = 'NoAuth'` into
`test-ui-config.php`. This setting is not prompted interactively — it must be
changed manually when CAS authentication is required.

To enable CAS mode:

1. Open `tests/application/config/test-ui-config.php` and change the constant:
   ```php
   const TESTS_SESSION_TYPE = 'CAS';
   ```
2. Copy the CAS configuration template and fill in your server details:
   ```bash
   cp tests/application/config/test-cas-config.dist.php \
      tests/application/config/test-cas-config.php
   ```
3. Edit `test-cas-config.php` and set the following constants to match your
   CAS / LDAP environment:

   | Constant | Description |
   |---|---|
   | `APP_CAS_HOST` | CAS server hostname (e.g. `cas.example.com`) |
   | `APP_CAS_PORT` | CAS server port (default `443`) |
   | `TESTS_CAS_FIELD_EMAIL` | CAS response field for the user's email address |
   | `TESTS_CAS_FIELD_FIRST_NAME` | CAS response field for given name |
   | `TESTS_CAS_FIELD_LAST_NAME` | CAS response field for family name |
   | `TESTS_CAS_FIELD_FOREIGN_ID` | CAS response field used as the external user ID |
   | `APP_LDAP_HOST` / `APP_LDAP_PORT` | LDAP server connection details |
   | `APP_LDAP_DN` / `APP_LDAP_USERNAME` / `APP_LDAP_PASSWORD` | LDAP bind credentials |

> `test-cas-config.php` is gitignored alongside the other generated config
> files — it must never be committed.

### CLI Utility Library

`tools/include/cli-utilities.php` is the shared helper library included by
every script in `tools/`. It provides four console I/O functions:

| Function | Signature | Description |
|---|---|---|
| `writeln` | `writeln(string $text = '') : void` | Writes a line to STDOUT followed by a newline. Pass an empty string for a blank line. |
| `color` | `color(string $text, string $color) : string` | Wraps text in ANSI colour codes. Supported values: `green`, `red`, `yellow`, `cyan`, `bold`. Returns plain text (no ANSI codes) when the colour name is unrecognised, or when running on Windows (`PHP_OS_FAMILY === 'Windows'`). The Windows fallback is intentionally conservative — plain text is returned for all Windows environments to avoid a dependency on runtime terminal-capability detection. Modern terminals such as Windows Terminal and PowerShell 7+ do support ANSI, but detecting them reliably requires additional checks. |
| `prompt` | `prompt(string $label, string $default = '') : string` | Displays a labelled prompt and reads a trimmed line from STDIN. Returns `$default` when the user submits an empty line. |
| `promptPassword` | `promptPassword(string $label, string $default = '') : string` | Like `prompt`, but suppresses character echo on Unix-like systems via `stty -echo`. On Windows or when `stty` is unavailable, input is read with echo visible and a warning is shown. |

All functions are guarded with `function_exists()` so the file can be safely
included more than once.

### Developer Menu

An interactive numbered menu that groups the most common developer tasks in a
single entry point. Launch it from the project root with:

```bash
# Unix
./menu.sh

# Windows
menu.cmd

# Direct (any platform)
php tools/menu.php
```

If the `vendor/` directory is missing when the menu starts, `composer install`
is run automatically before the menu is displayed.

**Available options**

| # | Label | Command invoked |
|---|---|---|
| 1 | Setup local environment | `php tools/setup-local.php` |
| 2 | Build | `composer build` |
| 3 | Run tests | `composer test` (full) or `composer test-filter -- <pattern>` |
| 4 | Clear caches | `composer clear-caches` |
| 5 | Seed test database | `composer seed-tests` |
| 6 | PHPStan analysis | `composer analyze` |
| 0 | Exit | — |

Option **3 (Run tests)** sub-prompts for an optional filter pattern. Leaving it
empty runs the full test suite; entering a pattern runs only matching tests.

The menu loops after each action completes so you can run multiple tasks in
sequence without re-launching the script.

**Launchers**

| File | Platform | Notes |
|---|---|---|
| `menu.sh` | Unix / macOS | Executable (`chmod +x`). Uses `#!/usr/bin/env bash` for portability. |
| `menu.cmd` | Windows | Uses `cd /d "%~dp0"` to handle drive-letter differences. |
| `tools/menu.php` | Any | Core implementation; can be invoked directly with `php`. |

## Build-Time Documentation Generators

The `Application\Composer` namespace provides build-time utilities that generate
two Markdown documentation artefacts automatically on every `composer build`:

- **Modules Overview** (`docs/agents/project-manifest/modules-overview.md`) — a
  Markdown table of all modules, their source paths, context doc locations, and
  inter-module dependencies. Discovered from `module-context.yaml` files.
- **Keyword Glossary** (`docs/agents/project-manifest/module-glossary.md`) — a
  Markdown glossary mapping opaque domain terms to the modules that define them.
  Application modules can contribute custom sections via the `DecorateGlossaryEvent`
  offline event.

To register a module for discovery, add a `module-context.yaml` next to its classes:

```yaml
moduleMetaData:
  id: "my-module"
  label: "My Module"
  description: "What this module does."
  keywords:
    - "Widget (the core UI component)"
```

See [`src/classes/Application/Composer/README.md`](src/classes/Application/Composer/README.md)
for full API documentation and the offline-event integration guide.

## CTX Integration

The project uses CTX to generate context files for AI-assisted development,
and to provide an MCP server for integration with IDEs like PHPStorm to 
access the framework's AI tools.

### Related commands

#### Start the MCP Server

```bash
ctx server
```

#### Generate all files

```bash
ctx generate 
```

#### Server information

This fetches a JSON-lines list of configurations and tools available 
in the server.

```bash
(echo '{"jsonrpc":"2.0","id":1,"method":"initialize","params":{"protocolVersion":"2024-11-05","capabilities":{},"clientInfo":{"name":"test-client","version":"1.0.0"}}}'; \
 echo '{"jsonrpc":"2.0","method":"notifications/initialized"}'; \
 echo '{"jsonrpc":"2.0","id":2,"method":"tools/list","params":{}}') | ctx server -c context.yaml
```

## Documentation

The framework's documentation is available locally by pointing a browser to 
the `docs` folder, and online in the separate [Framework Documentation][]
package. 

> It is ideally viewed through the framework's documentation screen, as there
> are some features that are only available there (like code samples that are
> included dynamically). 

### Vendor Package

For convenience, a copy of the documentation is automatically checked out into 
the vendor folder during the Composer install process.

It can be found at [mistralys/application-framework-docs](/vendor/mistralys/application-framework-docs/README.md).

## History

The framework has its origins in several projects where the same development
paradigms were used and refined over time. In 2013, it started to crystallize
into a recognizable entity, and in 2015, it was officially split off into its
own project.

It was migrated to Github in february 2021, and modernizing the code has been
ongoing ever since. As a result, the current state of the code is a mix of
namespaced and non-namespaced code, with the goal of eventually moving to a
fully namespaced codebase.

[Framework Manager]: https://github.com/Mistralys/appframework-manager
[Framework Documentation]: https://github.com/Mistralys/application-framework-docs

```
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
            ├── 2026-05-05-test-db-seed-data-rework-1/
            │   ├── audit.md
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
            ├── 2026-05-05-test-db-seed-data/
            │   ├── audit.md
            │   ├── dependency-analysis.md
            │   ├── pipeline-configuration.md
            │   ├── plan.md
            │   ├── synthesis.md
            │   ├── work-packages-draft.md
            │   ├── work.md
            │   ├── work/
            │   │   └── WP-001.md
            │   │   └── WP-002.md
            │   │   └── WP-003.md
            │   │   └── WP-004.md
            │   │   └── WP-005.md
            ├── 2026-05-06-composer-setup-command/
            │   ├── plan.md
            │   ├── work-packages-draft.md
            │   ├── work.md
            │   ├── work/
            │   │   └── WP-001.md
            │   │   └── WP-002.md
            │   │   └── WP-003.md
            │   │   └── WP-004.md
            │   │   └── WP-005.md
            │   │   └── WP-006.md
            ├── 2026-05-07-seed-rework-followup/
            │   └── plan.md
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
###  Path: `/docs/agents/plans/2026-05-05-test-db-seed-data-rework-1/audit.md`

```md
# Plan Audit Report

## Plan Under Review
- **Plan:** `docs/agents/plans/2026-05-05-test-db-seed-data-rework-1/plan.md`
- **Date:** 2026-05-06
- **Auditor:** Plan Auditor Agent

## Verdict: PASS WITH FINDINGS

### Summary
The plan is well-structured, clearly motivated, and grounded in the actual codebase. The process-isolation approach is sound and achieves the stated goal of eliminating manual ORM cache resets. However, one critical finding (hallucinated API method) blocks Step 5 as written, and one major finding regarding the downstream contract change to `doSeedTests()` requires explicit resolution before implementation.

### Finding Counts
- **Critical:** 1
- **Major:** 1
- **Minor:** 1

---

## Findings

### Critical

| # | Category | Finding | Location in Plan | Recommendation |
|---|----------|---------|-------------------|----------------|
| 1 | Grounding | **`DBHelper::fetchColumnInt()` does not exist as a static method.** The plan proposes `DBHelper::fetchColumnInt('locales_application', 'locale_id', array('locale_name' => $name))` in Step 5. In the codebase, `fetchColumnInt()` exists only as an instance method on `DBHelper\FetchMany` (a fluent query builder at `src/classes/DBHelper/FetchMany.php:85`), not as a static method on `DBHelper`. The proposed code will cause a fatal error. | Step 5 — code example for idempotent `seedLocales()` | Use `DBHelper::recordExists(string $table, array $where) : bool` instead (confirmed at `src/classes/DBHelper/DBHelper.php:1914`). The corrected pattern: `if(!DBHelper::recordExists('locales_application', array('locale_name' => $name)))`. This matches the existing `seedCountries()` pattern of checking existence before insert. |

### Major

| # | Category | Finding | Location in Plan | Recommendation |
|---|----------|---------|-------------------|----------------|
| 1 | Consistency | **Removing `truncateAllTables()` from `doSeedTests()` breaks the documented contract for the HCP Editor plan.** The HCP Editor's planned `seedTests()` (at `docs/agents/plans/2026-05-05-test-db-seed-data/plan.md:84–96`) calls `\Application\Composer\ComposerScripts::doSeedTests()` expecting it to perform truncation + framework seeds in a single process. Removing truncation from `doSeedTests()` forces the HCP Editor plan to either: (a) add its own `truncateAllTables()` call before `doSeedTests()`, or (b) restructure into process-isolated CLI scripts like the framework. The plan's Risks section mentions this but proposes only a docblock update — it does not specify how the HCP Editor plan should adapt. | Step 4 — "Keep `doSeedTests()` as a convenience method"; Risks — "`doSeedTests()` called externally without prior truncation" | Either: (A) Keep `truncateAllTables()` inside `doSeedTests()` (the ORM cache staleness is handled by process isolation when called via Composer, and is harmless for the HCP Editor since it would call `doSeedTests()` immediately at startup before caches are populated), or (B) explicitly document the migration path for the HCP Editor plan: "HCP Editor's `seedTests()` must call `TestSuiteBootstrap::truncateAllTables()` before calling `doSeedTests()`." Option (A) is simpler — `doSeedTests()` can safely include truncation, and the `resetCollection()` calls can still be removed because process isolation eliminates their need when called via Composer, and because the HCP Editor calls `doSeedTests()` early enough that ORM caches are not yet populated. |

### Minor

| # | Category | Finding | Location in Plan | Recommendation |
|---|----------|---------|-------------------|----------------|
| 1 | Completeness | **The Assumptions section mentions `DBHelper::fetchColumnInt()` as the lookup method and offers a "raw SELECT COUNT(*)" fallback, but neither the correct method (`DBHelper::recordExists()`) nor its actual signature is identified.** This propagates the Critical finding into the assumptions. | Assumptions — bullet 2 | Replace with: "The existence check uses `DBHelper::recordExists(string $table, array $where) : bool`, which is verified to exist at `src/classes/DBHelper/DBHelper.php:1914`." |

---

## Alternative Approaches Considered

### Codebase-Internal Alternatives

**Alternative for Step 5 (idempotent `seedLocales()`):**

The `seedCountries()` method (at `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php:279`) already demonstrates the idempotency pattern using collection-level `isoExists()`. For locales, since there is no `LocaleCollection` with an `exists()` method, `DBHelper::recordExists()` is the appropriate low-level check. This is consistent with the plan's intent — only the specific method name needs correction.

**Alternative for Step 4 (preserving `doSeedTests()` contract):**

Rather than splitting the responsibility and requiring callers to "assume truncation was already performed," keep `doSeedTests()` self-contained (truncate + seed). The process isolation benefit is achieved at the Composer script level — `seed-truncate.php` calls `truncateAllTables()` directly, and `seed-insert.php` calls only the three seed methods directly, bypassing `doSeedTests()` entirely. This way `doSeedTests()` retains its original contract for programmatic callers (HCP Editor), while the Composer scripts use the lower-level methods directly for process isolation.

### Ecosystem-Sourced Alternatives

No ecosystem alternatives are applicable — the plan addresses internal infrastructure using appropriate internal patterns.

| Alternative | Source / Evidence | Trade-Off vs. Plan's Approach | Recommendation |
|---|---|---|---|
| Keep `doSeedTests()` as truncate+seed; have CLI scripts call low-level methods directly | Existing pattern in `ComposerScripts.php:127–145` | No contract break for downstream consumers; CLI scripts are slightly more explicit but not more complex | **Use instead** — eliminates the Major finding with zero additional complexity |
| Use `DBHelper::recordExists()` for locale existence check | `src/classes/DBHelper/DBHelper.php:1914` | Direct replacement for the hallucinated `fetchColumnInt()`; same semantics, correct API | **Use instead** — required fix for Critical finding |

---

## Completeness Assessment

| Plan Section | Status | Notes |
|--------------|--------|-------|
| Summary | OK | Clear, concise, covers all four work items. |
| Architectural Context | OK | Accurate description of the current flow with correct file references. |
| Approach / Architecture | OK | Process isolation rationale is sound; Composer array semantics are correct. |
| Rationale | OK | Clear comparison with the `resetAllCollections()` alternative. |
| Detailed Steps | Gap | Step 5 code example uses a non-existent API (Critical finding). |
| Dependencies | OK | Correctly identifies the prior plan as complete. |
| Required Components | OK | All listed files verified to exist. |
| Assumptions | Gap | Bullet 2 references a non-existent method (propagated from Critical). |
| Constraints | OK | Array syntax rule, `declare(strict_types=1)`, and `dump-autoload` exemption all correct. |
| Out of Scope | OK | HCP Editor seeding, test isolation, and `resetAllCollections()` correctly excluded. |
| Acceptance Criteria | OK | All criteria are testable and specific. |
| Testing Strategy | OK | Covers idempotency, empty-database, and regression scenarios. |
| Risks & Mitigations | Gap | The `doSeedTests()` contract-change risk is identified but the mitigation (docblock only) is insufficient for the HCP Editor's planned integration (Major finding). |

```
###  Path: `/docs/agents/plans/2026-05-05-test-db-seed-data-rework-1/plan.md`

```md
# Plan

## Summary

Follow-up rework addressing the strategic recommendations from the `2026-05-05-test-db-seed-data` synthesis. This plan covers: (1) refactoring the seed command to use process isolation instead of brittle manual ORM cache resets, (2) harmonizing the seed idempotency contract so all methods are safely re-runnable, (3) fixing stale documentation references to the deleted `tests/sql/testsuite.sql`, and (4) fixing the `$port` defensive initialization in `tools/setup-local.php`.

The test isolation gap (synthesis recommendation #3) was investigated and determined to be a **non-issue**: no test class calls `truncateAllTables()`, and all tests use per-test transactions that roll back in `tearDown()`. This item requires no code change.

The HCP Editor application-specific seeding (synthesis recommendation #5) is out of scope — it belongs in the HCP Editor project as a separate plan.

## Architectural Context

The seed infrastructure was implemented in the prior plan with this flow:

```
composer seed-tests
  → ComposerScripts::seedTests()         [PHP callback, runs in Composer's process]
    → define('APP_SEED_MODE', true)
    → init()                              [loads tests/bootstrap.php, boots framework]
    → doSeedTests()
      → truncateAllTables()               [DDL: TRUNCATE all base tables]
      → resetCollection() × N            [manually invalidate ORM caches]
      → seedSystemUsers()
      → seedLocales()
      → seedCountries()
```

The ORM cache invalidation after `truncateAllTables()` is required because `init()` boots the full framework (loading `tests/bootstrap.php` → `Application_Bootstrap::init()` → `bootClass(TestSuiteBootstrap::class)`), which may populate singleton collection caches (via `AppFactory::$instances` and individual `::$instance` statics). After truncation, these caches hold references to now-deleted rows.

**Key singleton mechanisms:**
- `AppFactory::$instances` — factory-level static array cache (`createClassInstance()`)
- `Application_Countries::$instance` — class-level `getInstance()` singleton
- `BaseCollection::$idLookup` / `$records` / `$allRecords` — per-collection internal memory caches

Currently, developers must add a `resetCollection()` call for EACH collection that might be cached before truncation. This is a maintenance trap documented in `testing.md` as a maintenance note.

## Approach / Architecture

### Process Isolation for Seeding

Replace the single-process PHP callback with a **two-phase CLI approach**:

1. **Phase 1 — Truncation** (`php tools/seed-truncate.php`): Boots the framework in seed mode, truncates all tables, exits. Process terminates — all ORM caches are destroyed with it.

2. **Phase 2 — Insertion** (`php tools/seed-insert.php`): Boots the framework in seed mode in a fresh process. Since the process just started, NO ORM caches exist. Calls all seed methods sequentially. No `resetCollection()` calls needed.

Composer script definition:
```json
"seed-tests": [
    "php tools/seed-truncate.php",
    "php tools/seed-insert.php"
]
```

Each element in a Composer script array runs as a separate shell command (= separate PHP process). This provides natural process isolation without any code complexity.

### Idempotency Harmonization

Make `seedLocales()` idempotent by adding an existence check before each insert (matching the `seedCountries()` pattern). This eliminates the mixed-contract maintenance trap.

### Documentation and Code Quality Fixes

- Fix 2 stale references to `tests/sql/testsuite.sql` in `docs/agents/project-manifest/testing.md`
- Add defensive `$port = 'null'` initialization before the do-while loop in `tools/setup-local.php`

## Rationale

**Why process isolation over `resetAllCollections()` helper:**

The synthesis recommended a `resetAllCollections()` helper. While that reduces per-developer burden, it still requires:
- Maintaining an exhaustive list of all framework-level ORM singletons
- Updating the list when new collections are added
- Running correctly regardless of which collections were actually populated

Process isolation eliminates the problem at its root: after truncation completes and the process exits, ALL in-memory state (including static properties, singletons, and ORM caches) is destroyed. The insertion process starts clean with zero stale state. This is:
- **Zero-maintenance:** No list of collections to maintain
- **Future-proof:** Works automatically as new collections are added
- **Simpler mental model:** Each phase has a clean environment

**Performance cost:** Two process bootstraps (~0.5–1s each) instead of one. Total ~1–2s for the seed command. Acceptable for a developer-facing CLI tool.

**Why make `seedLocales()` idempotent:**
- Consistent contract across all seed methods (no ordering surprises)
- Safe for partial re-runs during development
- Matches the pattern already used by `seedCountries()` and `seedSystemUsers()`

## Detailed Steps

### Step 1: Create `tools/seed-truncate.php`

Create a minimal CLI script that:
1. Defines `APP_SEED_MODE = true`
2. Requires the test bootstrap
3. Calls `TestSuiteBootstrap::truncateAllTables()`
4. Prints a status message

**New file:** `tools/seed-truncate.php`

### Step 2: Create `tools/seed-insert.php`

Create a CLI script that:
1. Defines `APP_SEED_MODE = true`
2. Requires the test bootstrap
3. Calls `TestSuiteBootstrap::seedSystemUsers()`, `TestSuiteBootstrap::seedLocales()`, `TestSuiteBootstrap::seedCountries()` in sequence
4. Prints a status message

**Note:** This script calls the seed methods directly — it does NOT call `doSeedTests()`. The process isolation between `seed-truncate.php` and `seed-insert.php` eliminates the need for `resetCollection()` calls without changing the `doSeedTests()` contract.

**New file:** `tools/seed-insert.php`

### Step 3: Update Composer script definition

Change `composer.json`:
```json
"seed-tests": [
    "php tools/seed-truncate.php",
    "php tools/seed-insert.php"
]
```

### Step 4: Refactor `ComposerScripts`

- Remove `seedTests()` static method (no longer called as a Composer PHP callback)
- Keep `doSeedTests()` **self-contained**: it retains `truncateAllTables()` + `resetCollection()` calls + all three seed calls. This preserves the existing contract for programmatic callers (the HCP Editor's `ComposerScripts::seedTests()` calls `doSeedTests()` expecting truncation + seeding in one shot).
- The `resetCollection()` calls remain necessary inside `doSeedTests()` because programmatic callers run in a single process (no process isolation). They are harmless but required for correctness in that context.
- Update the `doSeedTests()` docblock to note that it is intended for programmatic callers; the Composer `seed-tests` script uses the process-isolated CLI scripts instead.

**Rationale:** The CLI scripts (`seed-truncate.php` / `seed-insert.php`) achieve process isolation by calling the low-level methods directly. `doSeedTests()` keeps its original contract intact so downstream projects (HCP Editor) do not need changes.

### Step 5: Make `seedLocales()` idempotent

In `TestSuiteBootstrap::seedLocales()`, add an existence check before each insert using `DBHelper::recordExists()` (confirmed at `src/classes/DBHelper/DBHelper.php:1914`):
```php
foreach(self::SEED_LOCALES as $name)
{
    if(!DBHelper::recordExists('locales_application', array('locale_name' => $name)))
    {
        DBHelper::insertDynamic('locales_application', array('locale_name' => $name));
    }
    if(!DBHelper::recordExists('locales_content', array('locale_name' => $name)))
    {
        DBHelper::insertDynamic('locales_content', array('locale_name' => $name));
    }
}
```

Update the PHPDoc to remove the non-idempotency warning.

### Step 6: Fix `$port` defensive initialization

In `tools/setup-local.php`, add `$port = 'null';` before the do-while loop at line ~202 to satisfy static analysis and make the code's intent explicit.

### Step 7: Fix stale documentation references

In `docs/agents/project-manifest/testing.md`, replace:
- Line 230: `tests/sql/testsuite.sql` → `docs/sql/pristine.sql`
- Line 238: `After importing tests/sql/testsuite.sql` → `After importing docs/sql/pristine.sql`

### Step 8: Update seed documentation in `testing.md`

Update the "Seeding the Test Database" section to reflect the new process-isolated architecture:
- Remove references to `APP_SEED_MODE` internal implementation
- Remove the "Maintenance note" about manual `resetCollection()` calls (no longer applicable)
- Document the two-phase process model
- Remove the non-idempotency warning from the Seeding Locales subsection

## Dependencies

- Prior plan `2026-05-05-test-db-seed-data` must be fully committed (it is — status COMPLETE)
- No external dependencies

## Required Components

- `tools/seed-truncate.php` (new)
- `tools/seed-insert.php` (new)
- `composer.json` (modify `seed-tests` script)
- `src/classes/Application/Composer/ComposerScripts.php` (remove `seedTests()`, update `doSeedTests()` docblock)
- `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php` (make `seedLocales()` idempotent)
- `tools/setup-local.php` (defensive `$port` initialization)
- `docs/agents/project-manifest/testing.md` (fix stale refs + update seed docs)

## Assumptions

- The framework's test bootstrap (`tests/bootstrap.php`) works correctly when `APP_SEED_MODE` is defined before requiring it
- The existence check uses `DBHelper::recordExists(string $table, array $where) : bool` (verified at `src/classes/DBHelper/DBHelper.php:1914`)
- Composer script arrays execute each element as a separate shell process (confirmed by Composer documentation)

## Constraints

- Array syntax: `array()` only (project rule)
- All PHP files must have `declare(strict_types=1)`
- Run `composer dump-autoload` is NOT needed (no new class files; only CLI scripts)
- The `doSeedTests()` public method must be preserved for programmatic use (the HCP Editor may call it)
- The `APP_SEED_MODE` constant guard in `TestSuiteBootstrap::_boot()` remains unchanged

## Out of Scope

- **HCP Editor application-specific seeding** — separate project, separate plan
- **Test isolation between suites** — investigated and confirmed as non-issue (tests use per-test transactions; no test calls `truncateAllTables()`)
- **`resetAllCollections()` helper** — superseded by the process-isolation approach; no longer needed
- **PHPStan baseline for `tools/setup-local.php`** — the file has 91 `function.notFound` errors due to locally-defined functions not visible to PHPStan; out of scope

## Acceptance Criteria

- `composer seed-tests` succeeds on a schema-only (empty) database
- `composer seed-tests` is idempotent (running twice produces no errors)
- No `resetCollection()` calls exist in the CLI seed scripts (`tools/seed-truncate.php`, `tools/seed-insert.php`)
- `doSeedTests()` retains its self-contained contract (truncate + reset + seed) for programmatic callers
- `seedLocales()` can be called on an already-seeded database without throwing
- PHPStan reports no new errors in modified files (beyond pre-existing baseline)
- All framework tests pass after seeding (`composer test`)
- `testing.md` contains no references to `tests/sql/testsuite.sql`
- `$port` variable in `tools/setup-local.php` is initialized before the do-while loop

## Testing Strategy

1. **Idempotency test:** Run `composer seed-tests` twice in succession; both runs must succeed with exit code 0.
2. **Empty-database test:** Import only `docs/sql/pristine.sql` (schema only), then run `composer seed-tests`; must succeed.
3. **Regression:** Run `composer test` (full framework suite) after seeding; all tests must pass.
4. **Locales idempotency:** Call `seedLocales()` manually after seeding; must not throw duplicate-key exception.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **Double bootstrap overhead (~1–2s)** | Acceptable for a CLI developer tool; documented as intentional trade-off. |
| **`doSeedTests()` called externally by HCP Editor** | `doSeedTests()` remains self-contained (truncate + reset + seed). No contract change for programmatic callers. The process-isolated Composer scripts bypass `doSeedTests()` by calling methods directly. |
| **Locale existence check uses wrong column name** | Use `DBHelper::recordExists()` with `array('locale_name' => $name)` — the `locale_name` column is the natural key in both locale tables. Verify against `docs/sql/pristine.sql` before implementing. |
| **Composer script array order not guaranteed** | Composer documentation confirms sequential execution for arrays; add a comment in `composer.json` for visibility. |
| **`tests/bootstrap.php` changes break seed scripts** | Both seed scripts use the same bootstrap path; changes are caught by the test suite. |

```
###  Path: `/docs/agents/plans/2026-05-05-test-db-seed-data-rework-1/synthesis.md`

```md
# Synthesis Report — Test DB Seed Data Rework (Pass 1)

**Plan:** `2026-05-05-test-db-seed-data-rework-1`  
**Date:** 2026-05-06  
**Status:** ✅ COMPLETE — all 6 work packages delivered, all pipelines PASS  

---

## Executive Summary

This plan delivered the four strategic improvements recommended by the prior synthesis (`2026-05-05-test-db-seed-data`). The centrepiece change replaced the brittle single-process Composer PHP callback with a **two-phase, process-isolated CLI seed workflow**, permanently eliminating a class of ORM cache-bleed bugs that required manual `resetCollection()` maintenance calls. Alongside that, `seedLocales()` was made idempotent (matching the `seedCountries()` contract), a PHPStan undefined-variable warning in `tools/setup-local.php` was resolved, and all stale `tests/sql/testsuite.sql` references in the developer documentation were corrected.

The test isolation gap identified in the prior synthesis was **investigated and confirmed a non-issue**: no test class calls `truncateAllTables()` directly, and all tests use per-test transactions that roll back in `tearDown()`. No code change was required for that item.

### What Was Built

| # | Work Package | Summary |
|---|---|---|
| WP-001 | Process-isolated seed scripts | Created `tools/seed-truncate.php` and `tools/seed-insert.php`; updated `composer.json` `seed-tests` to a two-command array |
| WP-002 | Fix stale `testsuite.sql` references | Audited `docs/agents/project-manifest/testing.md`; both references already corrected by WP-005 documentation pass |
| WP-003 | `seedLocales()` idempotency | Added `DBHelper::recordExists()` guards for both `locales_application` and `locales_content`; updated PHPDoc |
| WP-004 | `$port` defensive initialization | Added `$port = 'null';` before the do-while port loop in `tools/setup-local.php`; eliminated PHPStan warning |
| WP-005 | Remove `seedTests()` + doc cleanup | Removed the now-dead `ComposerScripts::seedTests()` wrapper; updated `doSeedTests()` docblock; updated narrative docs |
| WP-006 | Final documentation audit | Final audit of `testing.md`; removed residual `APP_SEED_MODE` internal detail and stale `resetCollection()` sentence |

---

## Metrics

### Pipeline Health

| WP | Stages Run | All PASS | Security Issues | Tests Passed | Tests Failed |
|---|---|---|---|---|---|
| WP-001 | implementation · qa · security-audit · code-review · documentation | ✅ | 0 | 5/5 | 0 |
| WP-002 | documentation | ✅ | — | — | — |
| WP-003 | implementation · qa · code-review · documentation | ✅ | — | 9/9 | 0 |
| WP-004 | implementation · qa · code-review · documentation | ✅ | — | 2/2 | 0 |
| WP-005 | implementation · qa · code-review · documentation | ✅ | — | 4/4 | 0 |
| WP-006 | documentation | ✅ | — | — | — |

**Total acceptance criteria:** 15 across all WPs — **all met (15/15)**  
**Security audit (WP-001):** 0 Critical, 0 High, 0 Medium, 0 Low findings on the new files  
**PHPStan:** Zero new errors introduced; WP-004 eliminated one pre-existing undefined-variable warning  

### Files Modified

| File | Changed By |
|---|---|
| `tools/seed-truncate.php` | WP-001 (created) |
| `tools/seed-insert.php` | WP-001 (created) |
| `composer.json` | WP-001 |
| `src/classes/Application/Composer/ComposerScripts.php` | WP-001 docs, WP-005, WP-005 code-review fix-forward |
| `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php` | WP-003, WP-005 code-review fix-forward |
| `tools/setup-local.php` | WP-004 |
| `README.md` | WP-001 docs |
| `.context/framework-core-system-overview.md` | WP-001 docs |
| `.context/modules/composer/architecture-core.md` | WP-001 docs, WP-005 docs |
| `docs/agents/project-manifest/testing.md` | WP-005 docs, WP-006 docs |

---

## Incidents & Notable Events

One tooling incident was logged during execution (medium priority, resolved):

> **`ledger_begin_work` stale active-WP pointer** — When the Documentation agent attempted to claim WP-002, `ledger_begin_work` rejected the claim citing a stale pointer to the already-COMPLETE WP-005. The WP-002 acceptance criteria had already been satisfied as part of the WP-005 documentation pass. Resolved by claiming and completing WP-002 via `ledger_claim_work_package` + `ledger_start_pipeline` directly.

Two low-priority artifact traceability warnings were also recorded (WP-002 and WP-004 documentation pipelines completed with no `files_modified` declared), as no files actually required modification in those pipelines.

---

## Strategic Recommendations (Gold Nuggets)

### 1. 🏗️ Dead Code Path: `ComposerScripts::doSeedTests()`

`doSeedTests()` (and its now-removed `seedTests()` wrapper) is no longer invoked by `composer seed-tests`. The method is retained as a **programmatic entry point for direct callers**, but its `resetCollection()` calls make it subtly inconsistent with the new process-isolated model. A future plan should evaluate:
- Whether any direct callers of `doSeedTests()` remain in the codebase (none found currently).
- Whether `doSeedTests()` should be formally deprecated or removed, or whether the `resetCollection()` calls inside it should be documented more explicitly as a "single-process only" contract.

### 2. 🔒 Floating `dev-master` Dependency: `shark/simple_html_dom`

The `composer.json` has `"shark/simple_html_dom": "dev-master"` pinned to a floating branch. This was flagged during the security audit (WP-001) as an **OWASP A06 (Vulnerable & Outdated Components)** risk:
- Floating `dev-master` pins bypass Composer's security advisory checks.
- PHP 8.4 deprecation notices from this package (`$http_response_header` on lines 99, 102, 113) clutter every seed run and test output.
- **Recommended action:** Pin to a specific release tag, or evaluate replacing the package entirely.

### 3. 📚 PHPDoc Cross-Reference Quality

The Reviewer identified an opportunity to improve discoverability of the seeding surface. The `seedLocales()` PHPDoc now cross-references `@see self::SEED_LOCALES` and `@see self::seedCountries()` (delivered in WP-003). This pattern — bidirectional `@see` tags between related seed methods — should be applied consistently to `seedSystemUsers()` and `seedCountries()` as well, so contributors can navigate the full seeding surface from any entry point.

### 4. 📝 Pre-Existing PHPStan Baseline (91 `function.notFound` Errors)

PHPStan currently reports 91 pre-existing errors in `tools/setup-local.php`, all of the form `function.notFound` for helpers loaded via runtime `require`. These are not introduced by this plan and are out of scope, but they represent ongoing noise in static analysis. A future task should audit whether these helpers can be declared in a PHPStan stub file or moved to an autoloadable location to clean up the baseline.

### 5. ✅ Seed Idempotency Contract Is Now Uniform

All three seed methods (`seedSystemUsers()`, `seedLocales()`, `seedCountries()`) now share the same idempotency contract — safe to call on a pre-seeded database. The two-phase process-isolated model reinforces this: `seed-truncate.php` and `seed-insert.php` can be run repeatedly without risk of duplicate-key exceptions or stale state. This makes the seed infrastructure suitable for use in CI pipelines without pre-flight checks.

---

## Next Steps for the Planner

1. **Create a task** to address the `shark/simple_html_dom` `dev-master` pin — either pin to a release or replace the package. This will clean up PHP 8.4 deprecation notices across all seed/test runs.
2. **Consider a follow-up plan** to formally remove or deprecate `ComposerScripts::doSeedTests()` if no direct callers are confirmed. This removes a confusing dead code path and its stale `resetCollection()` calls.
3. **Consider extending the PHPDoc `@see` pattern** for `seedSystemUsers()` and `seedCountries()` to match the cross-references added to `seedLocales()`.
4. **The PHPStan `function.notFound` baseline** in `tools/setup-local.php` (91 errors) warrants a dedicated cleanup task.
5. **HCP Editor application-specific seeding** (synthesis recommendation #5 from the prior plan) remains out of scope here — it should be planned as a separate task in the HCP Editor project.

```
###  Path: `/docs/agents/plans/2026-05-05-test-db-seed-data-rework-1/work.md`

```md
# Work Packages — Test DB Seed Data Rework

| WP | Title | Status | Dependencies | Pipeline Stages |
|----|-------|--------|--------------|------------------|
| WP-001 | Create Process-Isolated CLI Seed Scripts | READY | — | `implementation` → `qa` → `security-audit` → `code-review` → `documentation` |
| WP-002 | Fix Stale testsuite.sql References | READY | — | `documentation` |
| WP-003 | Make seedLocales() Idempotent | READY | — | `implementation` → `qa` → `code-review` → `documentation` |
| WP-004 | Fix $port Defensive Initialization | READY | — | `implementation` → `qa` → `code-review` → `documentation` |
| WP-005 | Refactor ComposerScripts to Remove seedTests() | BLOCKED | WP-001 | `implementation` → `qa` → `code-review` → `documentation` |
| WP-006 | Update Seed Documentation | BLOCKED | WP-001, WP-002, WP-003, WP-005 | `documentation` |

## Dependency Chain

```
WP-001 ──┬──→ WP-005 ──┐
          │              │
          └──────────────┼──→ WP-006
                         │
WP-002 ─────────────────┤
                         │
WP-003 ─────────────────┘

WP-004 (independent)
```

```
###  Path: `/docs/agents/plans/2026-05-05-test-db-seed-data-rework-1/work/WP-001.md`

```md
# WP-001: Create Process-Isolated CLI Seed Scripts

## Description

Create two standalone CLI PHP scripts (`tools/seed-truncate.php` and `tools/seed-insert.php`) that perform database truncation and seeding as separate processes, and wire them into a Composer `seed-tests` script array.

## Scope

- `tools/seed-truncate.php` — new CLI script for table truncation
- `tools/seed-insert.php` — new CLI script for seed data insertion
- `composer.json` — update `seed-tests` script definition

## Dependencies

- None

## Acceptance Criteria

1. `tools/seed-truncate.php` exists with `declare(strict_types=1)`, defines `APP_SEED_MODE`, requires test bootstrap, calls `TestSuiteBootstrap::truncateAllTables()`, and prints a status message
2. `tools/seed-insert.php` exists with `declare(strict_types=1)`, defines `APP_SEED_MODE`, requires test bootstrap, calls `seedSystemUsers()`, `seedLocales()`, `seedCountries()` in order, and prints a status message
3. Neither CLI script contains any `resetCollection()` calls
4. `composer.json` `seed-tests` script is an array of two shell commands: `php tools/seed-truncate.php` and `php tools/seed-insert.php`
5. `composer seed-tests` succeeds on a schema-only (empty) database

## Active Pipeline Stages

`implementation` → `qa` → `security-audit` → `code-review` → `documentation`

```
###  Path: `/docs/agents/plans/2026-05-05-test-db-seed-data-rework-1/work/WP-002.md`

```md
# WP-002: Fix Stale testsuite.sql References

## Description

Update `docs/agents/project-manifest/testing.md` to replace all stale references to `tests/sql/testsuite.sql` with the correct path `docs/sql/pristine.sql`.

## Scope

- `docs/agents/project-manifest/testing.md` — replace `tests/sql/testsuite.sql` references

## Dependencies

- None

## Acceptance Criteria

1. `docs/agents/project-manifest/testing.md` contains no references to `tests/sql/testsuite.sql`
2. All former `tests/sql/testsuite.sql` references now correctly point to `docs/sql/pristine.sql`

## Active Pipeline Stages

`documentation`

```
###  Path: `/docs/agents/plans/2026-05-05-test-db-seed-data-rework-1/work/WP-003.md`

```md
# WP-003: Make seedLocales() Idempotent

## Description

Refactor `seedLocales()` to use `DBHelper::recordExists()` checks before each insert so the function can be called multiple times without throwing duplicate-key exceptions.

## Scope

- `seedLocales()` function — add idempotency guards for `locales_application` and `locales_content` tables
- PHPDoc on `seedLocales()` — remove non-idempotency warning

## Dependencies

- None

## Acceptance Criteria

1. `seedLocales()` uses `DBHelper::recordExists()` before each insert for both `locales_application` and `locales_content` tables
2. `seedLocales()` can be called on an already-seeded database without throwing a duplicate-key exception
3. PHPDoc on `seedLocales()` no longer contains a non-idempotency warning
4. Uses `array()` syntax per project coding convention (no `[]` short syntax)

## Active Pipeline Stages

`implementation` → `qa` → `code-review` → `documentation`

```
###  Path: `/docs/agents/plans/2026-05-05-test-db-seed-data-rework-1/work/WP-004.md`

```md
# WP-004: Fix $port Defensive Initialization

## Description

Add a defensive initialization of the `$port` variable before the do-while loop in `tools/setup-local.php` to prevent potential undefined variable warnings.

## Scope

- `tools/setup-local.php` — add `$port = 'null';` initialization before do-while loop

## Dependencies

- None

## Acceptance Criteria

1. `$port` variable is initialized with `$port = 'null';` before the do-while loop in `tools/setup-local.php`
2. No new PHPStan errors introduced in `tools/setup-local.php` beyond the pre-existing baseline

## Active Pipeline Stages

`implementation` → `qa` → `code-review` → `documentation`

```
###  Path: `/docs/agents/plans/2026-05-05-test-db-seed-data-rework-1/work/WP-005.md`

```md
# WP-005: Refactor ComposerScripts to Remove seedTests()

## Description

Remove the now-obsolete `seedTests()` static method from `ComposerScripts.php` while preserving `doSeedTests()` for programmatic callers, and update its docblock to reflect the new process-isolated Composer script model.

## Scope

- `ComposerScripts.php` — remove `seedTests()`, update `doSeedTests()` docblock

## Dependencies

- WP-001

## Acceptance Criteria

1. The `seedTests()` static method no longer exists in `ComposerScripts.php`
2. `doSeedTests()` retains its self-contained contract: truncate + resetCollection + seed all three data sets
3. `doSeedTests()` docblock updated to explain it is for programmatic callers; the Composer `seed-tests` script uses process-isolated CLI scripts instead
4. PHPStan reports no new errors in `ComposerScripts.php`

## Active Pipeline Stages

`implementation` → `qa` → `code-review` → `documentation`

```
###  Path: `/docs/agents/plans/2026-05-05-test-db-seed-data-rework-1/work/WP-006.md`

```md
# WP-006: Update Seed Documentation

## Description

Update the seed documentation in `testing.md` to document the new two-phase process-isolated seeding model and remove outdated references to manual `resetCollection()` maintenance, `APP_SEED_MODE` internals, and non-idempotency warnings.

## Scope

- `docs/agents/project-manifest/testing.md` — update 'Seeding the Test Database' section and Seeding Locales subsection

## Dependencies

- WP-001
- WP-002 (Fix Stale testsuite.sql References)
- WP-003
- WP-005 (Refactor ComposerScripts to Remove seedTests())

## Acceptance Criteria

1. The 'Seeding the Test Database' section in `testing.md` documents the two-phase process-isolated model (`seed-truncate.php` → `seed-insert.php`)
2. No references to manual `resetCollection()` maintenance remain in the seed documentation
3. No `APP_SEED_MODE` internal implementation details are exposed in the seed documentation
4. No non-idempotency warning exists in the Seeding Locales subsection

## Active Pipeline Stages

`documentation`

```
###  Path: `/docs/agents/plans/2026-05-05-test-db-seed-data/audit.md`

```md
# Plan Audit Report

## Plan Under Review
- **Plan:** `docs/agents/plans/2026-05-05-test-db-seed-data/plan.md`
- **Date:** 2026-05-06
- **Auditor:** Plan Auditor Agent

## Verdict: PASS WITH FINDINGS

### Summary
The plan is well-structured with accurate grounding in the codebase. All referenced files, methods, and constants are verified to exist. The approach is sound and follows established patterns. However, the plan has two major findings related to the recently-added CLI infrastructure (`composer setup` / `tools/setup-local.php`) that it only partially accounts for, plus a data mismatch between the existing `testsuite.sql` country ISOs and the plan's seed set.

### Finding Counts
- **Critical:** 0
- **Major:** 3
- **Minor:** 2

---

## Findings

### Critical

(none)

### Major

| # | Category | Finding | Location in Plan | Recommendation |
|---|----------|---------|-------------------|----------------|
| 1 | Completeness | The plan's Step 4 says to update `tools/setup-local.php` to import `docs/sql/pristine.sql`, but does not mention the need to update the `AGENTS.md` "Local Environment Setup" section which currently states: _"imports `tests/sql/testsuite.sql`"_ (line 217 of `AGENTS.md`). Developers relying on the documentation would see stale instructions. | Step 4 / Required Components | Add a row to the "Modified Files" table for `AGENTS.md` — update the Local Environment Setup section to reference `docs/sql/pristine.sql`. |
| 2 | Consistency | The existing `testsuite.sql` seeds country ISO `uk` for United Kingdom (line 238). The plan proposes seeding `gb` instead. A dedicated test file (`tests/AppFrameworkTests/Countries/UKGBTest.php`) verifies that both `uk` and `gb` resolve to the same country (the `filterCode()` normalizes `uk` → `gb`). While using `gb` is technically correct (it's the canonical ISO 3166-1 alpha-2 code), this is a silent behavioral change: the raw DB value changes from `uk` to `gb`. If any downstream application or test performs a raw SQL `WHERE iso = 'uk'` query, it would break. | Step 3 / SEED_COUNTRIES constant | Either: (a) Keep `gb` but add a note to the plan acknowledging the change from the previous `uk` seed data and confirming no downstream test uses raw SQL with `WHERE iso = 'uk'`. Or (b) Use `uk` to preserve backward compatibility — the `createNewCountry()` method normalizes it to `gb` internally via `filterCode()`. |
| 3 | Completeness | The plan references `AppFactory::createCountries()` in Step 3, but the existing `TestSuiteBootstrap::seedSystemUsers()` method accesses the application via `Application::createInstaller()`. The plan does not explain how `AppFactory` is available during seed mode — specifically whether `AppFactory` is fully initialized after `ComposerScripts::init()` boots the test application. While this is likely fine (the init call boots the full application), the assumption should be explicit, especially because `APP_SEED_MODE` skips `configureUsers()` which might affect factory availability. | Step 3 / Assumptions | Add to the Assumptions section: "After `ComposerScripts::init()`, `AppFactory` is fully initialized and all factory methods are available, including `createCountries()`." Verify by checking that `init()` reaches a full boot state. |

### Minor

| # | Category | Finding | Location in Plan | Recommendation |
|---|----------|---------|-------------------|----------------|
| 1 | Completeness | The plan doesn't mention the interactive developer menu (`tools/menu.php`, option 5: "Seed test database"). While the menu doesn't need code changes (it calls `composer seed-tests` which is unchanged), acknowledging it in the Architectural Context section would give implementers full visibility into all entry points for seeding. | Architectural Context | Add a note: "The developer menu (`tools/menu.php`, option 5) and `composer setup` both invoke `composer seed-tests` — no changes needed to those entry points." |
| 2 | Risk | The plan removes `tests/sql/testsuite.sql` which contains 41 CREATE TABLE statements. `docs/sql/pristine.sql` contains 50 CREATE TABLE statements. The plan assumes `pristine.sql` is a superset of `testsuite.sql`'s schema but does not explicitly verify this. If any table in `testsuite.sql` is missing from `pristine.sql`, the seeder could fail on insert. | Step 4 / Risks | Add a risk entry: "Verify that `docs/sql/pristine.sql` contains all tables present in `tests/sql/testsuite.sql` before deletion." Given that pristine.sql (50 tables, 1583 lines) is larger than testsuite.sql (41 tables, 1291 lines), this is low-risk but should be confirmed. |

---

## Alternative Approaches Considered

### Codebase-Internal Alternatives

The plan's approach of extending `TestSuiteBootstrap` with static methods is the most consistent choice. The alternative of creating new installer task classes (like `InitSystemUsers`) was explicitly ruled out in "Out of Scope" — this is reasonable since the seed data is simple enough for direct API calls.

Another option would be to keep `testsuite.sql` but strip its INSERT statements, turning it into the schema file. This avoids maintaining two SQL files (`testsuite.sql` for legacy, `pristine.sql` for the new flow) but the plan's approach of consolidating on `pristine.sql` is cleaner.

### Ecosystem-Sourced Alternatives

| Alternative | Source / Evidence | Trade-Off vs. Plan's Approach | Recommendation |
|---|---|---|---|
| Doctrine Migrations or Phinx for schema management | Industry-standard PHP migration tools | Massive architectural change, inappropriate for this scope. The framework uses raw SQL schema files. | Do not use — overkill for this task. |
| PHPUnit `@beforeClass` data seeding | Built into PHPUnit | Would run before each test class rather than once globally. Slower for a full suite. The existing `composer seed-tests` approach is a one-time setup. | Do not use — plan's approach is correct for this project. |

No ecosystem alternative improves on the plan's approach for this specific project context.

---

## Completeness Assessment

| Plan Section | Status | Notes |
|--------------|--------|-------|
| Summary | OK | Clear and concise. |
| Architectural Context | OK | All referenced files and methods verified. Minor gap: doesn't mention the CLI/menu entry points (see Minor #1). |
| Approach / Architecture | OK | Sound design, follows existing patterns. |
| Rationale | OK | Well-justified. |
| Detailed Steps | OK | Each step is actionable. Minor: Step 3 assumes AppFactory availability (see Major #3). |
| Dependencies | OK | Correctly identifies step ordering. |
| Required Components | Gap | Missing `AGENTS.md` from the Modified Files table (see Major #1). |
| Assumptions | Gap | Missing assumption about AppFactory availability in seed mode (see Major #3). |
| Constraints | OK | Correctly identifies array syntax, strict types, typed constants. |
| Out of Scope | OK | Well-bounded. |
| Acceptance Criteria | OK | Specific and testable. |
| Testing Strategy | OK | Covers manual verification, idempotency, and smoke tests. |
| Risks & Mitigations | OK | Good coverage; minor gap regarding schema file equivalence (see Minor #2). |

```
###  Path: `/docs/agents/plans/2026-05-05-test-db-seed-data/dependency-analysis.md`

```md
# Dependency & Sequencing Analysis

## Dependency Graph

| WP | Dependencies |
|----|-------------|
| WP-1 | none |
| WP-2 | none |
| WP-3 | none |
| WP-4 | WP-1, WP-2, WP-3 |
| WP-5 | WP-4 |

## Execution Phases

### Phase 1 (Parallel)
- WP-1: Implement `TestSuiteBootstrap::truncateAllTables()`
- WP-2: Implement `TestSuiteBootstrap::seedLocales()`
- WP-3: Implement `TestSuiteBootstrap::seedCountries()`

### Phase 2
- WP-4: Wire `doSeedTests()` orchestration (depends on WP-1, WP-2, WP-3)

### Phase 3
- WP-5: Remove `testsuite.sql` and update references (depends on WP-4)

## Parallelization Notes

- WP-1, WP-2, and WP-3 all modify the same file (`src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php`). They add independent methods and constants to non-overlapping sections of the class, so they are logically parallelizable. However, concurrent edits to the same file risk textual merge conflicts — sequential execution within Phase 1 is safer for automated agents.
- WP-4 modifies a different file (`src/classes/Application/Composer/ComposerScripts.php`) and has no file overlap with WP-5, but WP-4 calls methods that WP-1/2/3 produce, creating a hard artifact dependency.
- WP-5 modifies three files (`tests/sql/testsuite.sql`, `tools/setup-local.php`, `AGENTS.md`) that no other WP touches. Its dependency on WP-4 is a logical ordering constraint: the legacy SQL file should only be removed after the programmatic seeding is fully wired and operational.

## Critical Path

```
WP-1 ─┐
WP-2 ─┼─→ WP-4 → WP-5
WP-3 ─┘
```

Longest sequential chain: **3 phases** (any of WP-1/2/3 → WP-4 → WP-5).

Since WP-1, WP-2, and WP-3 share a file, the practical critical path when executing sequentially within Phase 1 is: WP-1 → WP-2 → WP-3 → WP-4 → WP-5 (5 sequential steps). With true parallelism in Phase 1: 3 phases minimum.

```
###  Path: `/docs/agents/plans/2026-05-05-test-db-seed-data/pipeline-configuration.md`

```md
# Pipeline Configuration

## Per-WP Stage Configuration

| WP | active_pipeline_stages | Rationale |
|----|------------------------|-----------|
| WP-1 | `["implementation", "qa", "code-review", "documentation"]` | Standard code change — internal dev tooling, no security surface, no release artifacts |
| WP-2 | `["implementation", "qa", "code-review", "documentation"]` | Standard code change — internal dev tooling, no security surface, no release artifacts |
| WP-3 | `["implementation", "qa", "code-review", "documentation"]` | Standard code change — internal dev tooling, no security surface, no release artifacts |
| WP-4 | `["implementation", "qa", "code-review", "documentation"]` | Standard code change — wires existing methods together, no security surface, no release artifacts |
| WP-5 | `["implementation", "qa", "code-review", "documentation"]` | Involves file deletion and setup script modification — not documentation-only; QA needed to verify no broken references |

## Guardrail Notes

- **No deviations from standard chain.** All 5 WPs receive the default 4-stage pipeline.
- **Security-audit excluded:** All WPs modify internal developer tooling (test database seeding). No production-facing code, no user input handling, no external API calls, no authentication/authorization logic.
- **Release-engineering excluded:** This is internal infrastructure with no publishable artifacts, no version bumps, and no breaking API changes.
- **WP-5 is not documentation-only:** Although described as "cleanup/reference updates," it deletes a SQL file and modifies a setup script (`tools/setup-local.php`), which constitutes code/infrastructure changes requiring implementation and QA validation.

```
###  Path: `/docs/agents/plans/2026-05-05-test-db-seed-data/plan.md`

```md
# Plan: Test Database Seed Infrastructure (Framework)

## Summary

Extend the framework's `composer seed-tests` infrastructure with three new capabilities: a truncate-all-tables reset step, locale seeding, and country seeding. These are framework-level concerns reusable by any application built on the framework. The HCP Editor will build on this foundation with its own application-specific seeding (separate plan).

## Architectural Context

### Existing Infrastructure

- **`composer seed-tests`** is defined in `composer.json` and calls `Application\Composer\ComposerScripts::seedTests()`.
- **`ComposerScripts::seedTests()`** (`src/classes/Application/Composer/ComposerScripts.php`) defines `APP_SEED_MODE`, calls `self::init()` (boots the application in seed mode), then calls `self::doSeedTests()`.
- **`ComposerScripts::doSeedTests()`** currently only calls `TestSuiteBootstrap::seedSystemUsers()`.
- **`TestSuiteBootstrap::seedSystemUsers()`** (`src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php`) wraps the `InitSystemUsers` installer task in a manual transaction: `startTransaction()` → task → `commitTransaction()`, with `rollbackConditional()` in the catch block.
- **`APP_SEED_MODE`:** When defined, `TestSuiteBootstrap::_boot()` skips `configureUsers()` (the check that system users exist), allowing the seeder to boot before users are inserted.
- **`DBHelper::truncate(string $tableName)`** (`src/classes/DBHelper/DBHelper.php`) executes `TRUNCATE TABLE` on the given table.
- **FK checks:** No standalone helper exists. The pattern used in `getDropTablesQuery()` is: `DBHelper::execute(DBHelper_OperationTypes::TYPE_UPDATE, "SET FOREIGN_KEY_CHECKS=0")`.
- **Locale tables** (`locales_application`, `locales_content`) are single-column tables (`locale_name varchar(5)`) with no ORM — data must be inserted via `DBHelper::insertDynamic()`.
- **Country creation** uses `Application_Countries::createNewCountry(string $iso, string $label)` and `createInvariantCountry()` for the ZZ invariant country. Note: only `createInvariantCountry()` is idempotent (checks existence first). `createNewCountry()` throws `CountryException` (`ERROR_ISO_ALREADY_EXISTS`) if the ISO already exists — therefore `seedCountries()` must only run after `truncateAllTables()` has cleared the table, or must guard each insert with an `isoExists()` check.
- The framework's own test SQL (`tests/sql/testsuite.sql`) seeds locales and countries via raw SQL, confirming these are framework-level concerns. This file will be removed because its responsibilities are now fully covered by importing the pristine schema SQL followed by the programmatic seeder.
- **`createTestLocale()`** in `ApplicationTestCase` fetches a random `locale_name` from `locales_application` — requires at least one row to exist.

### Entry Points for Seeding

Multiple CLI pathways invoke `composer seed-tests` — none require code changes, but implementers should be aware of them:

- **`composer seed-tests`** — standalone command, runs the seeder directly.
- **`composer setup`** (`tools/setup-local.php`) — interactive local environment setup; imports the schema SQL and calls `composer seed-tests` as its final step.
- **Developer menu** (`tools/menu.php`, option 5) — calls `composer seed-tests`.

### Key Files

| File | Purpose |
|---|---|
| `src/classes/Application/Composer/ComposerScripts.php` | Composer script entry points, `doSeedTests()` |
| `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php` | Test boot, `seedSystemUsers()` |
| `src/classes/Application/Installer/Task/InitSystemUsers.php` | Only existing installer task |
| `src/classes/Application/Installer/Task.php` | Installer task base class |
| `src/classes/Application/Countries/Countries.php` | `createNewCountry()`, `createInvariantCountry()` |
| `src/classes/Application/Countries/Country.php` | `COUNTRY_INDEPENDENT_ISO` constant |
| `src/classes/DBHelper/DBHelper.php` | `truncate()`, `execute()`, `insertDynamic()`, transaction methods |
| `tests/AppFrameworkTestClasses/ApplicationTestCase.php` | `createTestLocale()`, `createTestCountry()` |

## Approach / Architecture

Add three new public static methods to `TestSuiteBootstrap`, following the established `seedSystemUsers()` pattern, then wire them into `ComposerScripts::doSeedTests()`:

1. **`truncateAllTables()`** — Disables FK checks, truncates every table in the database via `SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'`, re-enables FK checks in a `try/finally` block to guarantee re-enablement even on failure. Runs outside a transaction (MySQL `TRUNCATE` is DDL and auto-commits).

2. **`seedLocales()`** — Inserts `de_DE` and `en_UK` into both `locales_application` and `locales_content` via `DBHelper::insertDynamic()`. Wrapped in a transaction.

3. **`seedCountries()`** — Creates the invariant country (ZZ) via `createInvariantCountry()` and 8 test countries (DE, CA, FR, IT, ES, GB, US, MX) via `createNewCountry()`. Wrapped in a transaction. The country ISOs and labels are defined as a constant array on `TestSuiteBootstrap`.

The updated `doSeedTests()` execution order:
```
doSeedTests()
  ├─ truncateAllTables()      ← FK off, TRUNCATE *, FK on
  ├─ seedSystemUsers()        ← known_users (IDs 1, 2)
  ├─ seedLocales()            ← locales_application + locales_content
  └─ seedCountries()          ← 8 countries + ZZ invariant
```

The new local environment setup flow becomes:
1. Create the database (if needed).
2. Import `docs/sql/pristine.sql` (schema only, no test data).
3. Run `composer seed-tests` (populates test data programmatically).

## Rationale

- **Locales and countries are framework concepts.** The framework's own test infrastructure (`createTestLocale()`, `createTestCountry()`) depends on them. Seeding them in the framework benefits all applications.
- **Truncate-and-reseed** is simpler than per-row idempotency checks. Disabling FK checks during truncation makes table ordering irrelevant.
- **Static methods on `TestSuiteBootstrap`** follow the existing pattern established by `seedSystemUsers()`. No new class hierarchy needed.
- **The `doSeedTests()` public helper** allows application-level ComposerScripts to call the framework seeding first, then add their own steps — the established delegation pattern.

## Detailed Steps

### Step 1: Add `TestSuiteBootstrap::truncateAllTables()`

**File:** `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php`

Add a new public static method that:
1. Executes `SET FOREIGN_KEY_CHECKS=0` via `DBHelper::execute()`.
2. In a `try` block:
   a. Fetches all base table names via `SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'`.
   b. Calls `DBHelper::truncate()` for each table.
3. In a `finally` block: executes `SET FOREIGN_KEY_CHECKS=1`.

The `try/finally` guarantees FK checks are re-enabled even if a truncation fails mid-loop (following the safety pattern used in `DBHelper::getDropTablesQuery()`).

This method does not use a transaction (MySQL `TRUNCATE` is DDL and auto-commits).

### Step 2: Add `TestSuiteBootstrap::seedLocales()`

**File:** `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php`

Add a new public static method following the `seedSystemUsers()` transaction pattern:
1. Start transaction.
2. Insert `de_DE` and `en_UK` into `locales_application` via `DBHelper::insertDynamic('locales_application', array('locale_name' => $name))`.
3. Insert `de_DE` and `en_UK` into `locales_content` via `DBHelper::insertDynamic('locales_content', array('locale_name' => $name))`.
4. Commit transaction (rollback on failure).

The locale names should be defined as a class constant for clarity:
```php
public const array SEED_LOCALES = array('de_DE', 'en_UK');
```

### Step 3: Add `TestSuiteBootstrap::seedCountries()`

**File:** `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php`

Add a new public static method:
1. Start transaction.
2. Call `AppFactory::createCountries()->createInvariantCountry()` for ZZ.
3. For each entry in the constant array: check `isoExists($iso)` first — if the country already exists, skip it; otherwise call `createNewCountry($iso, $label)`. This makes `seedCountries()` safe to call even without a preceding truncation (defensive against partial re-runs).
4. Commit transaction (rollback on failure).

Define the country data as a class constant:
```php
// These 8 countries cover the framework's test needs (country tests, locale
// tests, filter criteria) without over-seeding. Downstream applications add
// their own countries via application-level seeders. The previous testsuite.sql
// included pl, ro, at — these are not required by any framework test and are
// omitted to keep the seed minimal.
//
// Note: The old testsuite.sql used 'uk' for United Kingdom. We use 'gb' here
// because it is the correct ISO 3166-1 alpha-2 code. The framework's
// CountryCollection::filterCode() normalizes 'uk' → 'gb', so lookups via
// either code continue to work. No framework test uses raw SQL with
// WHERE iso = 'uk'.
public const array SEED_COUNTRIES = array(
    'de' => 'Germany',
    'ca' => 'Canada',
    'fr' => 'France',
    'it' => 'Italy',
    'es' => 'Spain',
    'gb' => 'United Kingdom',
    'us' => 'United States',
    'mx' => 'Mexico',
);
```

### Step 4: Remove `tests/sql/testsuite.sql`

Delete the file `tests/sql/testsuite.sql`. Its responsibilities (schema + test data) are now split between:
- **Schema:** `docs/sql/pristine.sql` (imported during `composer setup`).
- **Test data:** `composer seed-tests` (programmatic seeding).

Update `tools/setup-local.php` to import `docs/sql/pristine.sql` instead of `tests/sql/testsuite.sql` when creating the test database.

Update the `AGENTS.md` "Local Environment Setup" section to reference `docs/sql/pristine.sql` instead of `tests/sql/testsuite.sql`.

### Step 5: Update `ComposerScripts::doSeedTests()`

**File:** `src/classes/Application/Composer/ComposerScripts.php`

Update the method to call all seed steps in order, with `echo` progress messages matching the existing style:

```php
public static function doSeedTests() : void
{
    echo '- Seeding test database...'.PHP_EOL;

    TestSuiteBootstrap::truncateAllTables();
    TestSuiteBootstrap::seedSystemUsers();
    TestSuiteBootstrap::seedLocales();
    TestSuiteBootstrap::seedCountries();

    echo '  DONE.'.PHP_EOL;
}
```

## Dependencies

- No external dependencies. All required classes and methods already exist in the framework.
- Steps 1–3 (new methods on `TestSuiteBootstrap`) are independent of each other and can be implemented in any order.
- Step 4 (file removal) is independent but should be done after Step 5 is ready.
- Step 5 (wiring into `doSeedTests()`) depends on Steps 1–3 being complete.

## Required Components

### Modified Files

| File | Changes |
|---|---|
| `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php` | Add `truncateAllTables()`, `seedLocales()`, `seedCountries()`, and related constants |
| `src/classes/Application/Composer/ComposerScripts.php` | Update `doSeedTests()` to call new seed methods |
| `tools/setup-local.php` | Update DB import path from `tests/sql/testsuite.sql` to `docs/sql/pristine.sql` |
| `AGENTS.md` | Update "Local Environment Setup" section to reference `docs/sql/pristine.sql` |

### Deleted Files

| File | Reason |
|---|---|
| `tests/sql/testsuite.sql` | Replaced by pristine schema SQL + programmatic seeding |

### New Files

None.

## Assumptions

- The test database has the full schema applied via `docs/sql/pristine.sql`. The seeder populates data, not structure.
- `APP_SEED_MODE` correctly bypasses the user existence check in `TestSuiteBootstrap::_boot()`.
- After `ComposerScripts::init()`, `AppFactory` is fully initialized and all factory methods are available, including `createCountries()`. The seed mode only skips `configureUsers()` — it does not affect factory registration.
- MySQL's `SET FOREIGN_KEY_CHECKS=0` allows `TRUNCATE TABLE` on tables with FK references.
- The locale names `de_DE` and `en_UK` match the values used in the framework's own test infrastructure and the `APP_UI_LOCALES` / `APP_CONTENT_LOCALES` constants configured in test environments.
- `docs/sql/pristine.sql` (50 tables) is a superset of `tests/sql/testsuite.sql` (41 tables) — all tables required by the seeder exist in the pristine schema.

## Constraints

- **Array syntax:** All code must use `array()`, not `[]`.
- **`declare(strict_types=1)`:** Already present in both files; maintain it.
- **Typed constants:** New class constants must have explicit type declarations.
- **No constructor promotion.**

## Out of Scope

- Application-specific seeding (business areas, tenants, templates) — handled by the HCP Editor plan.
- Refactoring existing test helpers (`createTestLocale()`, `createTestCountry()`).
- Adding new installer task classes (the framework seeding uses direct API calls, not the installer task infrastructure).

## Acceptance Criteria

1. Running `composer seed-tests` from a framework-based application completes without errors.
2. After seeding, the test database contains:
   - `known_users`: 2 rows (system, dummy). Note: the previous `testsuite.sql` contained a third user (ID 3) which was not a system user; no test depends on it and it is intentionally not seeded.
   - `locales_application`: 2 rows (de_DE, en_UK).
   - `locales_content`: 2 rows (de_DE, en_UK).
   - `countries`: 9 rows (8 test countries + ZZ invariant).
3. Running `composer seed-tests` a second time produces the same result (truncate-and-reseed is idempotent).
4. All tables not in the seed set are empty after seeding (truncate clears everything).
5. The file `tests/sql/testsuite.sql` no longer exists.
6. `tools/setup-local.php` imports `docs/sql/pristine.sql` for schema creation.
7. The framework's own test suite continues to pass after these changes (existing behavior preserved).

## Testing Strategy

1. **Manual seeder verification:** Run `composer seed-tests`, then query the database to verify row counts for `known_users`, `locales_application`, `locales_content`, and `countries`.
2. **Idempotency check:** Run `composer seed-tests` twice in succession; verify no errors and identical row counts.
3. **Framework test smoke test:** Run `composer test-filter -- Countries` and `composer test-filter -- Locale` to verify country and locale test infrastructure works with seeded data.

## Risks & Mitigations

| Risk | Mitigation |
|---|---|
| **`TRUNCATE TABLE` fails despite `FOREIGN_KEY_CHECKS=0`** | MySQL respects `FOREIGN_KEY_CHECKS=0` for `TRUNCATE`. If issues arise on specific MySQL versions, fall back to `DELETE FROM` + `ALTER TABLE AUTO_INCREMENT = 1` per table. |
| **~~`SHOW TABLES` returns views or temporary tables~~** | Mitigated by design: `truncateAllTables()` uses `SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'` as the primary query, eliminating this risk proactively. |
| **Truncating all tables breaks a downstream application's seed assumptions** | The `doSeedTests()` method is a helper called by application scripts. Applications that need additional data call `doSeedTests()` first, then add their own seeds — the truncate step resets everything to a known-empty state before any seeding begins. |
| **Country auto-increment IDs differ from old `testsuite.sql` values** | No framework test code relies on specific numeric country IDs (verified via grep; full confirmation requires running `composer test-filter -- Countries`). All lookups use ISO codes. |
| **`setup-local.php` references old SQL file** | Step 4 explicitly updates the import path to `docs/sql/pristine.sql`. |
| **`pristine.sql` missing tables from `testsuite.sql`** | Verify before deleting `testsuite.sql` that all 41 tables from `testsuite.sql` exist in `pristine.sql` (which has 50 tables — likely a superset, but confirm). |

```
###  Path: `/docs/agents/plans/2026-05-05-test-db-seed-data/synthesis.md`

```md
# Synthesis Report — Test Database Seed Infrastructure (Framework)

**Plan:** `2026-05-05-test-db-seed-data`
**Date:** 2026-05-06
**Status:** COMPLETE

---

## Executive Summary

This session delivered a complete, production-ready seed infrastructure for the Application Framework's `composer seed-tests` command. Five work packages extended `TestSuiteBootstrap` and `ComposerScripts` with three new seed methods (`truncateAllTables`, `seedLocales`, `seedCountries`) and wired them into an orchestrating `doSeedTests()` flow that is **fully idempotent** and can seed a schema-only empty database from scratch.

The original `tests/sql/testsuite.sql` raw-SQL seed file was simultaneously decommissioned — its responsibilities are now entirely covered by programmatic seeding on top of `docs/sql/pristine.sql`, eliminating SQL file duplication and keeping seed data under version control as PHP code.

One significant integration bug was discovered and resolved during QA: ORM collection caches were not invalidated after `truncateAllTables()`, causing `seedSystemUsers()` to silently skip re-inserting user 1 on every run. A companion bootstrap bug (authentication chain querying user 1 before seeding on an empty DB) was also identified and fixed. Both fixes have been verified by live idempotent double-run execution and the full regression suite passes cleanly.

---

## Metrics

| Metric | Value |
|---|---|
| Work Packages | 5 / 5 COMPLETE |
| Pipeline Stages | 20 / 20 PASS |
| QA Rework Cycles | 1 (WP-004) |
| PHPStan Errors | 0 (all WPs) |
| Tests Passing (QA verification runs) | 322 across all passing QA stages |
| Tests Failing | 0 |
| Files Modified | 7 |
| Files Deleted | 1 (`tests/sql/testsuite.sql`) |

---

## Work Package Summary

### WP-001 — `truncateAllTables()`

**Purpose:** Reset the entire test database before re-seeding.

**Implementation:** `TestSuiteBootstrap::truncateAllTables()` disables FK checks, fetches all base tables via `SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'`, truncates each via `DBHelper::truncate()`, and re-enables FK checks in a `finally` block — guaranteeing re-enablement even if truncation fails mid-loop. Intentionally runs outside a transaction (MySQL `TRUNCATE` is DDL and auto-commits).

**Code Review Fix-Forward:** SQL string quoting changed from escaped single-quotes to double-quoted outer string — the established PHP idiom for SQL with embedded single quotes. Non-behavioral.

**Note confirmed correct:** Developer's concern about `DBHelper::$tablesList` cache staleness after truncation was confirmed a false positive by code review — `TRUNCATE` empties rows but does not drop tables; the table-names cache remains accurate.

---

### WP-002 — `seedCountries()`

**Purpose:** Seed 8 test countries plus the ZZ invariant country into the `application_countries` table.

**Implementation:** `SEED_COUNTRIES` constant defines 8 ISO=>label pairs (DE, CA, FR, IT, ES, GB, US, MX). `seedCountries()` calls `createInvariantCountry()` unconditionally (it is internally idempotent), then inserts each country in `SEED_COUNTRIES` guarded by an `isoExists()` check — making the method **safe to call without a preceding truncation** (idempotent re-runs skip already-present countries). All operations wrapped in a transaction with `rollbackConditional()` on failure.

**Inline documentation note:** The `gb` vs `uk` normalization rationale is documented as an inline comment, cited as a model of contextual documentation by the Reviewer.

---

### WP-003 — `seedLocales()`

**Purpose:** Seed `de_DE` and `en_UK` into both `locales_application` and `locales_content` tables.

**Implementation:** `SEED_LOCALES` constant defines the two locale strings. `seedLocales()` iterates both locales and inserts into both tables via `DBHelper::insertDynamic()`, wrapped in a transaction. Unlike `seedCountries()`, **this method is NOT idempotent** — it has no duplicate guard. A duplicate-key exception will be thrown if called without a preceding `truncateAllTables()`.

**Documentation-Forward Resolved:** Code review flagged missing non-idempotency note in the PHPDoc. The documentation stage updated the docblock in `TestSuiteBootstrap.php` and added an explicit warning to `testing.md`.

---

### WP-004 — `doSeedTests()` Orchestration *(one rework cycle)*

**Purpose:** Wire all four seed methods into a single `doSeedTests()` call sequence and verify end-to-end idempotency.

**Bugs Discovered (QA Bounce):**

| Bug | Severity | Root Cause | Fix Applied |
|---|---|---|---|
| ORM cache not invalidated after truncation | **CRITICAL** | After bootstrap authenticated user 1, `BaseCollection::$idLookup[1]` stayed populated. After `truncateAllTables()` wiped the row, `seedSystemUsers()` saw user 1 as "existing" via stale cache, took the UPDATE branch, and never re-inserted the row. | `doSeedTests()` now calls `AppFactory::createUsers()->resetCollection()` and `AppFactory::createCountries()->resetCollection()` immediately after `truncateAllTables()`. |
| Bootstrap fails on empty DB | **SECONDARY** | The authentication chain (`createEnvironment()` → session auth → `getSystemUser()`) ran before `doSeedTests()`, querying user 1 from an empty table. | `TestSuiteBootstrap::_boot()` now guards `createEnvironment()`, `configurePaths()`, AND `configureUsers()` with `!defined('APP_SEED_MODE')`. In seed mode only `configureDatabase()` and `registerTransactionCleanupHandler()` run. |

**Post-fix QA:** Both bugs fixed and verified by live double-run of `composer seed-tests`. Regression tests: DBHelper 83/83, Countries 22/22, Locale 9/9 — all pass.

**Reviewer Observations (non-blocking):**
- Future seed method additions must include a matching `resetCollection()` call in `doSeedTests()`. A maintenance note was added to `doSeedTests()` PHPDoc and to `testing.md`.
- If `APP_SEED_MODE` guarding grows to more bootstrap methods, a private `isInSeedMode(): bool` helper would improve discoverability.

---

### WP-005 — Decommission `tests/sql/testsuite.sql`

**Purpose:** Remove the now-redundant raw-SQL seed file and update all references to point to `docs/sql/pristine.sql`.

**Implementation:**
- Deleted `tests/sql/testsuite.sql`
- Updated `SETUP_SQL_SCHEMA` constant in `tools/setup-local.php`
- Updated `AGENTS.md` Local Environment Setup section

**Code Review Fix-Forward:** Updated 4 stale references to the deleted file in `README.md` (manual setup instructions, automated setup description, `composer setup` section, seed-tests section).

**Documentation:** `composer build` regenerated `.context/framework-core-system-overview.md`, resolving 3 stale operational references to the deleted file. The now-empty `tests/sql/` directory will disappear from VCS on the next commit (git does not track empty directories).

---

## Modified Files

| File | Change |
|---|---|
| `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php` | Added `truncateAllTables()`, `SEED_COUNTRIES`, `seedCountries()`, `SEED_LOCALES`, `seedLocales()`; updated `_boot()` APP_SEED_MODE guard |
| `src/classes/Application/Composer/ComposerScripts.php` | Updated `doSeedTests()` with full call sequence and ORM cache resets |
| `tools/setup-local.php` | Updated `SETUP_SQL_SCHEMA` constant to `docs/sql/pristine.sql` |
| `AGENTS.md` | Updated SQL file reference |
| `README.md` | Fixed 4 stale `tests/sql/testsuite.sql` references (Fix-Forward) |
| `docs/agents/project-manifest/testing.md` | Added subsections: Resetting the Test Database, Seeding Countries, Seeding Locales; updated Seeding the Test Database section |
| `.context/framework-core-system-overview.md` | Regenerated via `composer build` |
| ~~`tests/sql/testsuite.sql`~~ | Deleted |

---

## Strategic Recommendations

### 1. ORM Cache Invalidation Is a Systemic Risk

The critical bug in WP-004 reveals a framework-level pattern gap: any workflow that calls `truncateAllTables()` must manually invalidate all ORM collection caches that may have been populated during bootstrap. Currently this is handled by explicit `resetCollection()` calls in `doSeedTests()`, but the obligation falls on developers to remember this for each future seed method.

**Recommendation:** Consider adding a `resetAllCollections()` helper to `TestSuiteBootstrap` that calls `resetCollection()` on all framework-level ORM singletons in one shot. This reduces the per-developer maintenance burden and provides a single authoritative reset point. Document it as the required post-truncation call in `constraints.md`.

### 2. Dual-Idempotency Contract for Seed Methods

Two different idempotency contracts now coexist:
- `seedCountries()` — fully idempotent via `isoExists()` guard (safe without prior truncation)
- `seedLocales()` and `seedSystemUsers()` — NOT idempotent (require prior truncation)

This inconsistency is documented but is a latent maintenance trap. **Recommendation:** For the HCP Editor's application-specific seed plan, establish and document a single convention — either all seed methods are idempotent (preferred) or all require truncation-first. Mixing patterns in a growing seed infrastructure will lead to subtle ordering bugs.

### 3. Test Isolation Gap: Countries Truncates Locales

`seedCountries()` calls `truncateAllTables()` internally (via `doSeedTests()`). QA confirmed a pre-existing issue where running Countries and Locales test suites in the same process (combined filter) produces false Locales failures because Countries tests invoke truncation that wipes the Locales table.

**Recommendation:** Address in a future test infrastructure pass. Each test suite that requires seed data should call `truncateAllTables()` + the relevant seed methods in its own `setUpBeforeClass()`, or use a test trait that manages test-database state isolation.

### 4. Pre-existing Technical Debt: `$port` Undefined

`tools/setup-local.php` line 228 has a "possible undefined variable `$port`" flagged by static analysis. Pre-existing and out of scope for this plan, but should be cleaned up.

### 5. HCP Editor Application-Specific Seeding (Next Plan)

This plan explicitly positions itself as the framework-level foundation. The HCP Editor's own `composer seed-tests` extension for application-specific entities (tenants, users, etc.) should be planned and implemented next, building on the now-stable framework seed infrastructure.

---

## Next Steps for Planner

1. **HCP Editor seed plan** — Application-specific seeding on top of this foundation.
2. **Idempotency convention decision** — Align `seedLocales()` (and future methods) with the `seedCountries()` idempotent pattern, or document the truncate-first contract as the single official approach.
3. **`resetAllCollections()` helper** — Evaluate extracting the multi-collection reset into a named helper on `TestSuiteBootstrap`.
4. **Test isolation** — Address Countries-truncates-Locales isolation issue in the test suite.
5. **`$port` undefined** in `tools/setup-local.php` — Minor cleanup ticket.

```
###  Path: `/docs/agents/plans/2026-05-05-test-db-seed-data/work-packages-draft.md`

```md
# Work Packages — Test Database Seed Infrastructure (Framework)

---

## WP-1 — Add `truncateAllTables()` to `TestSuiteBootstrap`

**Description:** Implement a static method that disables FK checks, truncates every base table in the database, and re-enables FK checks in a `try/finally` block.

**Scope:**
- `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php`

**Deliverables:**
- New public static method `truncateAllTables()` on `TestSuiteBootstrap`
- Method disables FK checks, queries `SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'`, calls `DBHelper::truncate()` for each table, re-enables FK checks in `finally`

**Acceptance Criteria:**
1. Calling `TestSuiteBootstrap::truncateAllTables()` on a seeded database results in all tables being empty.
2. FK checks are re-enabled even if a truncation fails mid-loop (verified by `finally` block structure).
3. The method does not use a transaction (TRUNCATE is DDL).
4. Code uses `array()` syntax and follows existing coding conventions.

**Estimated Complexity:** Medium

**Notes:** No dependencies on other WPs. Uses the FK check pattern from `DBHelper::getDropTablesQuery()`. Must use `DBHelper::execute(DBHelper_OperationTypes::TYPE_UPDATE, ...)` for SET statements.

---

## WP-2 — Add `seedLocales()` to `TestSuiteBootstrap`

**Description:** Implement a static method that inserts the two test locales (`de_DE`, `en_UK`) into both `locales_application` and `locales_content`, wrapped in a transaction.

**Scope:**
- `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php`

**Deliverables:**
- New typed class constant `SEED_LOCALES` (array of locale strings)
- New public static method `seedLocales()` on `TestSuiteBootstrap`

**Acceptance Criteria:**
1. After calling `seedLocales()`, `locales_application` contains exactly 2 rows (`de_DE`, `en_UK`).
2. After calling `seedLocales()`, `locales_content` contains exactly 2 rows (`de_DE`, `en_UK`).
3. The method uses the same transaction pattern as `seedSystemUsers()` (start → commit, rollback in catch).
4. `SEED_LOCALES` is declared with explicit type: `public const array SEED_LOCALES = array(...)`.
5. Inserts use `DBHelper::insertDynamic()`.

**Estimated Complexity:** Low

**Notes:** No dependencies on other WPs. The constant ensures locale names are defined in one place for both tables.

---

## WP-3 — Add `seedCountries()` to `TestSuiteBootstrap`

**Description:** Implement a static method that creates the ZZ invariant country and 8 test countries, wrapped in a transaction with idempotency guards.

**Scope:**
- `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php`

**Deliverables:**
- New typed class constant `SEED_COUNTRIES` (associative array of ISO → label)
- New public static method `seedCountries()` on `TestSuiteBootstrap`

**Acceptance Criteria:**
1. After calling `seedCountries()`, `countries` contains 9 rows (8 test + ZZ invariant).
2. ZZ is created via `createInvariantCountry()`; other countries via `createNewCountry()`.
3. Each country insert is guarded with `isoExists()` check before calling `createNewCountry()` (defensive against partial re-runs).
4. The method uses the same transaction pattern as `seedSystemUsers()`.
5. `SEED_COUNTRIES` is declared with explicit type: `public const array SEED_COUNTRIES = array(...)`.
6. Country ISOs are lowercase in the constant (matching the plan's specification).

**Estimated Complexity:** Medium

**Notes:** No dependencies on other WPs. Uses `AppFactory::createCountries()` to access the Countries collection. The `isoExists()` guard makes this safe even without a prior truncation.

---

## WP-4 — Wire `doSeedTests()` to call all seed methods

**Description:** Update `ComposerScripts::doSeedTests()` to invoke the new seed methods in the correct order with progress output.

**Scope:**
- `src/classes/Application/Composer/ComposerScripts.php`

**Deliverables:**
- Updated `doSeedTests()` method calling: `truncateAllTables()` → `seedSystemUsers()` → `seedLocales()` → `seedCountries()`
- Echo progress messages matching existing style

**Acceptance Criteria:**
1. Running `composer seed-tests` executes all four seed steps in order without errors.
2. After seeding: `known_users` has 2 rows, `locales_application` has 2 rows, `locales_content` has 2 rows, `countries` has 9 rows.
3. Running `composer seed-tests` twice in succession is idempotent (truncate clears everything before re-seeding).
4. All tables not in the seed set are empty after seeding.

**Estimated Complexity:** Low

**Notes:** Depends on WP-1, WP-2, and WP-3 being complete. The method body is straightforward — just sequencing calls with echo statements.

---

## WP-5 — Remove `testsuite.sql` and update references

**Description:** Delete the legacy `tests/sql/testsuite.sql` file and update `tools/setup-local.php` and `AGENTS.md` to reference `docs/sql/pristine.sql` instead.

**Scope:**
- `tests/sql/testsuite.sql` (deletion)
- `tools/setup-local.php`
- `AGENTS.md`

**Deliverables:**
- `tests/sql/testsuite.sql` deleted
- `tools/setup-local.php` imports `docs/sql/pristine.sql` instead of `tests/sql/testsuite.sql`
- `AGENTS.md` "Local Environment Setup" section references `docs/sql/pristine.sql`

**Acceptance Criteria:**
1. The file `tests/sql/testsuite.sql` no longer exists.
2. `tools/setup-local.php` references `docs/sql/pristine.sql` for schema import.
3. `AGENTS.md` mentions `docs/sql/pristine.sql` in the setup documentation (no stale references to `testsuite.sql`).
4. Running `composer setup` with a fresh database successfully imports the schema and seeds via the new pathway.

**Estimated Complexity:** Low

**Notes:** Depends on WP-4 being complete (the new seeding pipeline must be functional before removing the old SQL file). Involves a file deletion — implementer should confirm before proceeding.

```
###  Path: `/docs/agents/plans/2026-05-05-test-db-seed-data/work.md`

```md
# Work Packages — Test Database Seed Infrastructure (Framework)

| WP | Title | Status | Dependencies | Pipeline Stages |
|----|-------|--------|--------------|------------------|
| WP-001 | Implement `TestSuiteBootstrap::truncateAllTables()` | READY | — | implementation → qa → code-review → documentation |
| WP-002 | Implement `TestSuiteBootstrap::seedCountries()` | READY | — | implementation → qa → code-review → documentation |
| WP-003 | Implement `TestSuiteBootstrap::seedLocales()` | READY | — | implementation → qa → code-review → documentation |
| WP-004 | Wire `doSeedTests()` orchestration | BLOCKED | WP-001, WP-002, WP-003 | implementation → qa → code-review → documentation |
| WP-005 | Remove `testsuite.sql` and update references | BLOCKED | WP-004 | implementation → qa → code-review → documentation |

## Dependency Chain

```
WP-001 (truncateAllTables) ──┐
                              │
WP-002 (seedCountries)  ─────┼──► WP-004 (doSeedTests orchestration) ──► WP-005 (remove testsuite.sql)
                              │
WP-003 (seedLocales) ────────┘
```

```
###  Path: `/docs/agents/plans/2026-05-05-test-db-seed-data/work/WP-001.md`

```md
# WP-001: Implement `TestSuiteBootstrap::truncateAllTables()`

## Description

Add a public static method to `TestSuiteBootstrap` that truncates all base tables in the test database, with proper FK-check disabling/re-enabling guards.

## Scope

- `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php`

## Dependencies

- None

## Acceptance Criteria

1. Public static method `truncateAllTables()` exists on `TestSuiteBootstrap`
2. Method disables FK checks before truncation and re-enables in a `finally` block
3. Method fetches table list via `SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'`
4. Method calls `DBHelper::truncate()` for each table
5. Method does NOT use a transaction (TRUNCATE is DDL)
6. FK checks are guaranteed re-enabled even if truncation fails mid-loop

## Active Pipeline Stages

`implementation` → `qa` → `code-review` → `documentation`

```
###  Path: `/docs/agents/plans/2026-05-05-test-db-seed-data/work/WP-002.md`

```md
# WP-002: Implement `TestSuiteBootstrap::seedCountries()`

## Description

Add a public static method to `TestSuiteBootstrap` that seeds the required countries into the test database, including the ZZ invariant country.

## Scope

- `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php`

## Dependencies

- None

## Acceptance Criteria

1. Class constant `SEED_COUNTRIES` is defined with type `array` containing 8 country ISO=>label pairs
2. Public static method `seedCountries()` exists on `TestSuiteBootstrap`
3. Method creates the ZZ invariant country via `createInvariantCountry()`
4. Method guards each country insert with `isoExists()` check before calling `createNewCountry()`
5. Method wraps operations in a transaction with rollback on failure

## Active Pipeline Stages

`implementation` → `qa` → `code-review` → `documentation`

```
###  Path: `/docs/agents/plans/2026-05-05-test-db-seed-data/work/WP-003.md`

```md
# WP-003: Implement `TestSuiteBootstrap::seedLocales()`

## Description

Add a public static method to `TestSuiteBootstrap` that seeds the required application and content locales into the test database.

## Scope

- `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php`

## Dependencies

- None

## Acceptance Criteria

1. Class constant `SEED_LOCALES` is defined with type `array` containing `de_DE` and `en_UK`
2. Public static method `seedLocales()` exists on `TestSuiteBootstrap`
3. Method inserts locales into both `locales_application` and `locales_content` tables
4. Method uses `DBHelper::insertDynamic()` for insertions
5. Method wraps operations in a transaction with rollback on failure

## Active Pipeline Stages

`implementation` → `qa` → `code-review` → `documentation`

```
###  Path: `/docs/agents/plans/2026-05-05-test-db-seed-data/work/WP-004.md`

```md
# WP-004: Wire `doSeedTests()` orchestration

## Description

Update the `doSeedTests()` method to call the new truncate, locale-seeding, and country-seeding methods in the correct sequence, with appropriate progress messages.

## Scope

- `src/classes/Application/Composer/ComposerScripts.php`

## Dependencies

- WP-001
- WP-002
- WP-003

## Acceptance Criteria

1. `doSeedTests()` calls `truncateAllTables()`, `seedSystemUsers()`, `seedLocales()`, `seedCountries()` in that order
2. Progress echo messages are present matching the existing output style
3. Running `composer seed-tests` completes without errors
4. Running `composer seed-tests` twice in succession is idempotent (same results, no errors)

## Active Pipeline Stages

`implementation` → `qa` → `code-review` → `documentation`

```
###  Path: `/docs/agents/plans/2026-05-05-test-db-seed-data/work/WP-005.md`

```md
# WP-005: Remove `testsuite.sql` and update references

## Description

Delete the now-obsolete `testsuite.sql` file and update all references in setup scripts and documentation to point to `docs/sql/pristine.sql` instead.

## Scope

- `tests/sql/testsuite.sql` (delete)
- `tools/setup-local.php`
- `AGENTS.md`

## Dependencies

- WP-004

## Acceptance Criteria

1. File `tests/sql/testsuite.sql` no longer exists
2. `tools/setup-local.php` imports `docs/sql/pristine.sql` instead of `tests/sql/testsuite.sql`
3. `AGENTS.md` references `docs/sql/pristine.sql` instead of `tests/sql/testsuite.sql`
4. Running `composer setup` (or the relevant setup path) still works with the new SQL file

## Active Pipeline Stages

`implementation` → `qa` → `code-review` → `documentation`

```
###  Path: `/docs/agents/plans/2026-05-06-composer-setup-command/plan.md`

```md
# Plan: Interactive Developer Menu & `composer setup` Command

## Summary

Add an interactive CLI developer menu to the Application Framework, accessible via `menu.sh` (Unix) and `menu.cmd` (Windows) from the project root. The menu presents numbered options for common development tasks: Setup local environment, Build, Run tests, Clear caches, Seed tests, and PHPStan analysis. The menu shell scripts are thin wrappers that invoke a PHP menu script (`tools/menu.php`), keeping all logic in one language and avoiding duplication across platforms.

The **Setup** option (also available directly via `composer setup`) interactively configures the local development environment: it queries the user for database and UI settings, generates `test-db-config.php` and `test-ui-config.php` from their `.dist.php` templates, and runs `composer seed-tests` to initialize the database. The CAS config is deliberately skipped (it is loaded as optional and must be edited manually if needed). The setup is idempotent: re-running uses existing values as defaults.

## Architectural Context

### Config File Loading Chain

1. `tests/bootstrap.php` → `Application_Bootstrap::init()` → loads `tests/application/config/app-config.php`
2. `app-config.php` → `new EnvironmentsConfig(FolderInfo)->detect()`
3. `EnvironmentsConfig` registers `LocalEnvironment` which queues config file includes
4. `LocalEnvironment::setUpEnvironment()` includes:
   - `test-db-config.php` (required)
   - `test-ui-config.php` (required)
   - `test-cas-config.php` (optional — second parameter `true`)
5. After includes are loaded, `IncludesLoaded` event fires → `configureDefaultSettings()` reads the constants

### Template Files (`.dist.php`)

| Template | Target | Constants |
|---|---|---|
| `tests/application/config/test-db-config.dist.php` | `test-db-config.php` | `TESTSUITE_DB_HOST`, `TESTSUITE_DB_NAME`, `TESTSUITE_DB_USER`, `TESTSUITE_DB_PASSWORD`, `TESTSUITE_DB_PORT` |
| `tests/application/config/test-ui-config.dist.php` | `test-ui-config.php` | `TESTS_BASE_URL`, `TESTS_SESSION_TYPE`, `TESTS_SYSTEM_EMAIL_RECIPIENTS` |
| `tests/application/config/test-cas-config.dist.php` | `test-cas-config.php` | CAS/LDAP constants (skipped by this script) |

### Existing Convention (HCP Editor)

The HCP Editor has `tools/setup-local.php` — a standalone CLI script (no bootstrap dependency) invoked via `composer setup`. This plan follows the same pattern.

### Relevant Files

- `tests/application/config/test-db-config.dist.php` — DB config template
- `tests/application/config/test-ui-config.dist.php` — UI config template
- `tests/application/config/test-cas-config.dist.php` — CAS config template (not touched)
- `composer.json` — Composer scripts section (new `setup` script to add)
- `.gitignore` — currently ignores `/tools` (must be un-ignored)
- `tests/sql/testsuite.sql` — DB schema (imported by the setup script before seeding)

### File Structure (New)

```
(project root)
├── menu.sh                         ← Unix launcher (new)
├── menu.cmd                        ← Windows launcher (new)
└── tools/
    ├── menu.php                    ← Interactive menu (new)
    ├── setup-local.php             ← Setup script (new)
    └── include/
        └── cli-utilities.php       ← Shared CLI utilities (new)
```

## Approach / Architecture

The solution has three layers:

1. **Root launcher scripts** (`menu.sh`, `menu.cmd`) — thin wrappers that invoke `php tools/menu.php`
2. **PHP menu script** (`tools/menu.php`) — presents a numbered menu, dispatches to Composer commands or the setup script
3. **Setup script** (`tools/setup-local.php`) — standalone interactive setup (also callable directly via `composer setup`)

```
menu.sh / menu.cmd
       │
       ▼
tools/menu.php  (interactive numbered menu)
       │
       ├─→ [pre-flight] Check for vendor/ → run `composer install` if missing
       │
       ├─→ [1] Setup  → php tools/setup-local.php
       ├─→ [2] Build  → composer build
       ├─→ [3] Tests  → composer test (with scope prompt)
       ├─→ [4] Clear caches → composer clear-caches
       ├─→ [5] Seed tests → composer seed-tests
       ├─→ [6] PHPStan → composer analyze
       └─→ [0] Exit
```

Before showing the menu, the script checks whether the `vendor/` directory exists. If it does not, it runs `composer install` automatically. This allows a developer to go from a fresh clone to a fully operational local copy using only the menu.

The menu loops after each action completes, allowing the developer to run multiple tasks in one session. Exit with `0` or Ctrl+C.

## Rationale

- **PHP-based menu** avoids duplicating logic in bash and batch. Only the 2-line launcher wrappers differ per OS.
- **Standalone scripts** (no bootstrap) avoid the chicken-and-egg problem — config files don't exist yet on first run.
- **Same pattern as HCP Editor** reduces cognitive load for developers working on both projects.
- **Vendor pre-flight** means a fresh clone only needs `./menu.sh` to become fully operational — no prior manual steps.
- **Looping menu** lets developers run multiple tasks (e.g., setup → build → test) in one session.
- **Idempotent setup** via existing-value parsing: safe to re-run when changing a single setting.
- **Delegating DB seeding to `composer seed-tests`** keeps the seed logic in one place (`ComposerScripts::seedTests`) and avoids duplicating it.
- **CAS skipped** because it's rarely needed and marked optional in the environment config.

## Detailed Steps

### Step 1: Un-ignore `/tools` in `.gitignore`

Remove the line `/tools` from `.gitignore` so the new scripts can be version-controlled.

**File:** `.gitignore` (line 19)

### Step 2: Create `menu.sh` (project root)

A thin wrapper for Unix (macOS/Linux):

```bash
#!/usr/bin/env bash
# Interactive developer menu
cd "$(dirname "$0")" || exit 1
php tools/menu.php
```

Mark executable (`chmod +x`).

### Step 3: Create `menu.cmd` (project root)

A thin wrapper for Windows:

```batch
@echo off
cd /d "%~dp0"
php tools/menu.php
```

### Step 4: Create `tools/menu.php`

The interactive numbered menu script. Structure:

#### 4a. Pre-flight: Vendor Check

As the very first action (before including shared utilities or showing the menu), check for the `vendor/` directory relative to the project root:

```php
$vendorDir = __DIR__ . '/../vendor';

if (!is_dir($vendorDir)) {
    echo 'vendor/ directory not found. Running composer install...' . PHP_EOL;
    passthru('composer install', $exitCode);
    if ($exitCode !== 0) {
        echo 'composer install failed (exit code ' . $exitCode . '). Aborting.' . PHP_EOL;
        exit(1);
    }
    echo PHP_EOL;
}
```

This ensures all dependencies (including autoload) are available before any further logic executes.

#### 4b. Console I/O Utilities (shared)

Extract shared utilities into `tools/include/cli-utilities.php` so both `menu.php` and `setup-local.php` can reuse them:

- `writeln(string $text = ''): void`
- `color(string $text, string $color): string` — ANSI color support (green, red, yellow, cyan, bold)
- `prompt(string $label, string $default = ''): string` — readline with default

#### 4c. Menu Display & Loop

```php
function showMenu(): void
{
    writeln();
    writeln(color('=== Application Framework — Developer Menu ===', 'bold'));
    writeln();
    writeln('  ' . color('1', 'cyan') . '  Setup local environment');
    writeln('  ' . color('2', 'cyan') . '  Build');
    writeln('  ' . color('3', 'cyan') . '  Run tests');
    writeln('  ' . color('4', 'cyan') . '  Clear caches');
    writeln('  ' . color('5', 'cyan') . '  Seed test database');
    writeln('  ' . color('6', 'cyan') . '  PHPStan analysis');
    writeln();
    writeln('  ' . color('0', 'cyan') . '  Exit');
    writeln();
}
```

#### 4d. Menu Dispatch

Each option executes via `passthru()`:

| Choice | Action |
|--------|--------|
| `1` | `php tools/setup-local.php` (direct invocation, stays in same process context) |
| `2` | `composer build` |
| `3` | Sub-prompt: "Filter pattern (empty = full suite):" → `composer test-filter -- <pattern>` or `composer test` |
| `4` | `composer clear-caches` |
| `5` | `composer seed-tests` |
| `6` | `composer analyze` |
| `0` | Exit |

After each action completes, the menu redisplays (loop).

#### 4e. Main Entry

```php
// Pre-flight: ensure vendor/ exists
ensureVendorInstalled();

while (true) {
    showMenu();
    $choice = prompt('Select option');
    // dispatch...
}
```

### Step 5: Create `tools/include/cli-utilities.php`

Shared CLI utility functions used by both `menu.php` and `setup-local.php`:

- `writeln()`
- `color()`
- `prompt()`
- `promptPassword()` — hidden input via `stty -echo` (Unix) with graceful fallback (Windows: echo visible)

Guard against double-inclusion with `function_exists()` checks.

### Step 6: Create `tools/setup-local.php`

Create the standalone CLI setup script with the following sections:

#### 6a. Constants & Paths

```php
const SETUP_ROOT       = __DIR__ . '/..';
const SETUP_CONFIG_DIR = SETUP_ROOT . '/tests/application/config';

// Template files
const SETUP_TPL_DB  = SETUP_CONFIG_DIR . '/test-db-config.dist.php';
const SETUP_TPL_UI  = SETUP_CONFIG_DIR . '/test-ui-config.dist.php';

// Target files
const SETUP_DB_CONFIG = SETUP_CONFIG_DIR . '/test-db-config.php';
const SETUP_UI_CONFIG = SETUP_CONFIG_DIR . '/test-ui-config.php';
```

#### 6b. Include Shared Utilities

```php
require_once __DIR__ . '/include/cli-utilities.php';
```

#### 6c. Existing Config Parsing (for idempotency)

- `parseExistingDbConfig(): array` — parse `test-db-config.php` if it exists, extract constant values via regex
- `parseExistingUiConfig(): array` — parse `test-ui-config.php` if it exists, extract constant values via regex

Both use a shared `extractConstantValue(string $constant, string $content): string` helper.

#### 6d. Input Collection

- `collectDatabaseSettings(array $defaults): array` — prompt for: DB host, DB name, DB user, DB password, DB port
- `collectUiSettings(array $defaults): array` — prompt for: base URL, system email recipients
  - `TESTS_SESSION_TYPE` is not queried (always `'NoAuth'`; CAS mode requires manual config)

#### 6e. Database Connection Test

- `testDatabaseConnection(array $settings): PDO|false` — PDO connection attempt to the specified host/port/user/password (without selecting a database)
- Loop on failure, re-prompting only the DB settings

#### 6f. Config File Generation

- `replaceConfigConstant(string $content, string $constant, string $value, bool $isInt = false): string` — regex replacement of constant value in template content
- `generateDbConfig(array $settings): void` — read `.dist.php`, replace constant values, write `test-db-config.php`
- `generateUiConfig(array $settings): void` — read `.dist.php`, replace constant values, write `test-ui-config.php`

Special handling for `TESTSUITE_DB_PORT`: it is `null` in the template. When the user provides a port, write it as an integer. When the user leaves it empty or enters `null`, write `null`.

#### 6g. Database Creation & Schema Import

After the DB connection test succeeds and config files are written, ensure the database exists and its schema is loaded. This step runs before seeding because `composer seed-tests` requires the tables to already exist.

- `ensureDatabase(PDO $pdo, string $dbName, string $schemaFile): void`

Logic:
1. Attempt `SELECT 1` against the target database (`USE <dbName>`). If successful, the database exists — skip creation.
2. On failure, run `CREATE DATABASE \`<dbName>\``.
   - If the user lacks `CREATE DATABASE` privileges, report the error and abort.
3. Select the new database (`USE <dbName>`).
4. Read `tests/sql/testsuite.sql` via `file_get_contents()`.
5. Execute the SQL against the database via PDO with `PDO::MYSQL_ATTR_MULTI_STATEMENTS` (or `exec()` for single-connection drivers).
6. Report success or failure.

This follows the proven pattern from the HCP Editor's `tools/setup-local.php` (`ensureDatabase()` function, lines 358–420).

#### 6h. Database Seeding

After the database and schema are confirmed to exist, call `composer seed-tests` via `passthru()`:

```php
passthru('composer seed-tests', $exitCode);
```

This delegates to `ComposerScripts::seedTests()` which boots the framework and calls `TestSuiteBootstrap::seedSystemUsers()`. It requires the schema tables to already exist (handled by step 6g).

#### 6i. Main Entry Point

Orchestrate the flow:
1. Print banner
2. Parse existing configs as defaults
3. Collect DB settings
4. Collect UI settings
5. Test DB connection (loop on failure)
6. Generate `test-db-config.php`
7. Generate `test-ui-config.php`
8. Create database and import schema if needed
9. Run `composer seed-tests`
10. Print success / next-steps summary

#### 6j. Signal Handling

Register `SIGINT` handler (when `pcntl` available) to restore terminal echo if interrupted during password prompts.

### Step 7: Add `setup` script to `composer.json`

Add to the `"scripts"` section:

```json
"setup": "php tools/setup-local.php"
```

This allows `composer setup` as a direct alternative to using the menu.

### Step 8: Update `.gitignore` for generated config files

Verify that `tests/application/config/test-db-config.php` and `tests/application/config/test-ui-config.php` remain in `.gitignore` (they already are — lines 34 and 42). No change needed.

### Step 9: Update `AGENTS.md`

Add the developer menu and `composer setup` command to the framework's `AGENTS.md`:
- Document `menu.sh` / `menu.cmd` as the entry point
- Document `composer setup` as the direct setup command

## Dependencies

- PHP 8.4+ (already a project requirement)
- `ext-pdo` + MySQL driver (already a project requirement)
- `pcntl` extension (optional — for SIGINT handler during password prompts)
- `readline` extension (optional — falls back to `fgets(STDIN)`)
- `composer seed-tests` must work correctly after config files are in place

## Required Components

- **New file:** `menu.sh` (project root) — Unix launcher
- **New file:** `menu.cmd` (project root) — Windows launcher
- **New file:** `tools/menu.php` — interactive menu logic
- **New file:** `tools/include/cli-utilities.php` — shared CLI utilities
- **New file:** `tools/setup-local.php` — setup script
- **Modified file:** `composer.json` — add `"setup"` script
- **Modified file:** `.gitignore` — remove `/tools` exclusion

## Assumptions

- The developer has MySQL/MariaDB running locally and can provide valid credentials.
- The developer has Composer installed and can run `composer` commands.
- `composer seed-tests` handles seeding system users correctly once the database schema exists (this is the existing behavior per `ComposerScripts::seedTests()`). The setup script is responsible for creating the database and importing the schema beforehand.
- The DB user has `CREATE DATABASE` privileges (or the database already exists).

## Constraints

- The script must be standalone (no bootstrap dependency) — config files don't exist before it runs.
- Must use `array()` syntax, not `[]` (hard project rule).
- Must include `declare(strict_types=1)`.
- Must be idempotent: re-running with existing configs should use them as defaults and overwrite only with confirmed new values.
- `TESTS_SESSION_TYPE` defaults to `'NoAuth'` and is not queried.
- CAS config is not generated by this script.

## Out of Scope

- CAS/LDAP configuration (manual-only)
- Database schema migrations or upgrades
- Webserver virtual host configuration
- IDE configuration
- Git hook installation (framework doesn't have hooks)

## Acceptance Criteria

- Running `./menu.sh` (or `menu.cmd` on Windows) displays a numbered menu of developer tasks.
- Selecting option 1 (Setup) interactively generates both config files and seeds the database.
- `composer setup` achieves the same result as menu option 1.
- Running setup again reads existing values as defaults; pressing Enter preserves them.
- If the DB connection fails, the user is re-prompted for credentials until success.
- After successful setup, `composer test-file -- tests/AppFrameworkTests/` runs without config-related errors.
- The CAS config file is not generated.
- `TESTSUITE_DB_PORT` supports both integer and `null` values.
- Menu options 2–6 correctly dispatch to their respective Composer commands.
- The menu loops after each action; exit with `0`.

## Testing Strategy

This is a CLI tool, so testing is primarily manual:

1. **Menu display:** Run `./menu.sh`, verify all options are shown and the loop works correctly.
2. **Fresh setup:** Delete all generated config files, select option 1, verify files are generated correctly and tests can run.
3. **Direct composer setup:** Run `composer setup` directly, verify same behavior as menu option 1.
4. **Idempotent re-run:** Run setup again, press Enter through all prompts, verify config files are unchanged.
5. **Bad credentials:** Enter invalid DB credentials, verify re-prompt loop works and does not crash.
6. **Port handling:** Test with explicit port (e.g., `3307`) and with empty/null port.
7. **Other menu options:** Verify options 2–6 dispatch to correct Composer commands.
8. **Windows:** Verify `menu.cmd` launches the PHP menu correctly.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **`composer seed-tests` fails on fresh DB** | The setup script creates the database and imports `tests/sql/testsuite.sql` before calling `composer seed-tests`, ensuring all required tables exist. `APP_SEED_MODE` only skips the user-existence check during bootstrap — it does not handle missing schema. If the schema import itself fails, the script reports the PDO error and aborts before attempting to seed. |
| **`stty -echo` not available on Windows** | `promptPassword()` detects the OS and degrades gracefully (password visible on Windows). |
| **Regex fails on unusual constant values** | Use the same proven `replaceConfigConstant()` pattern from the HCP Editor that handles single-quoted, double-quoted, integer, and null values. |
| **`/tools` gitignore removal exposes unintended files** | The `/tools` directory didn't exist before and will only contain the menu/setup scripts. If other local-only tooling is added later, those specific files can be gitignored individually. |
| **ANSI colors break on Windows cmd** | Detect `PHP_OS_FAMILY === 'Windows'` and strip ANSI codes when not in a compatible terminal. Modern Windows Terminal supports ANSI, so this is a graceful fallback only for legacy cmd.exe. |

```
###  Path: `/docs/agents/plans/2026-05-06-composer-setup-command/work-packages-draft.md`

```md
# Work Packages — Interactive Developer Menu & `composer setup` Command

> **Decomposed by:** Ledger WP Decomposer v1.0.1
>
> **Plan source:** `docs/agents/plans/2026-05-06-composer-setup-command/plan.md`
>
> **Implementation note:** All files described in this plan already exist in the
> repository. The work packages below reflect the logical units of the completed
> implementation that must proceed through the pipeline stages (QA verification,
> code review, documentation sign-off).

---

## WP-001 — Project Configuration Prerequisites

**Description:** Modify `.gitignore` to un-ignore the `/tools` directory and add the `setup` Composer script to `composer.json`, enabling the new tool scripts to be version-controlled and invoked via `composer setup`.

**Scope:**
- `.gitignore` — remove the `/tools` exclusion line
- `composer.json` — add `"setup": "php tools/setup-local.php"` to the `"scripts"` section

**Deliverables:**
- `.gitignore` no longer contains a `/tools` exclusion line
- `composer.json` `"scripts"` section includes the `"setup"` entry
- Running `composer setup` invokes `tools/setup-local.php` (once the script exists)

**Acceptance Criteria:**
1. The `/tools` exclusion line is absent from `.gitignore`.
2. `composer.json` contains `"setup": "php tools/setup-local.php"` in the `"scripts"` section.
3. Files under `tools/` are trackable by Git (not ignored).
4. The existing `.gitignore` entries for generated config files (`tests/application/config/test-db-config.php`, `tests/application/config/test-ui-config.php`) remain intact.

**Estimated Complexity:** Low

**Notes:** This WP is a prerequisite for all other WPs that add files under `tools/`. The `.gitignore` change must be verified to not accidentally un-ignore other files. The `composer.json` change depends on `tools/setup-local.php` existing (WP-005) but can be added first — Composer will simply fail gracefully if the script is not yet present.

---

## WP-002 — Shared CLI Utilities Library

**Description:** Create the shared CLI utility functions library (`tools/include/cli-utilities.php`) that provides console I/O helpers used by both `menu.php` and `setup-local.php`.

**Scope:**
- `tools/include/cli-utilities.php` — new file

**Deliverables:**
- `writeln()` function — outputs a line of text to STDOUT
- `color()` function — wraps text in ANSI escape codes with Windows fallback (returns plain text on Windows)
- `prompt()` function — displays a label, reads a line from STDIN, supports default values
- `promptPassword()` function — reads password input with `stty -echo` on Unix, graceful fallback on Windows
- All functions guarded with `function_exists()` checks to allow safe re-inclusion

**Acceptance Criteria:**
1. The file declares `strict_types=1`.
2. `writeln()` outputs text followed by `PHP_EOL` and accepts an empty string for blank lines.
3. `color()` supports the five named colours: `green`, `red`, `yellow`, `cyan`, `bold`.
4. `color()` returns plain text (no ANSI codes) when `PHP_OS_FAMILY === 'Windows'` or when an unrecognised colour is passed.
5. `prompt()` displays the default value in square brackets and returns it when the user presses Enter without input.
6. `promptPassword()` uses `stty -echo` on Unix to hide input and restores echo afterwards.
7. `promptPassword()` falls back to visible input on Windows with a warning message.
8. All four functions are guarded by `function_exists()` checks.
9. The file uses `array()` syntax, not `[]` (project convention).

**Estimated Complexity:** Medium

**Notes:** This is a shared dependency of WP-004 (menu.php) and WP-005 (setup-local.php). Both scripts include this file via `require_once`. The `promptPassword()` function's `stty` detection must handle edge cases: `shell_exec` disabled in `php.ini`, non-Unix systems, etc.

---

## WP-003 — Root Launcher Scripts

**Description:** Create the thin shell wrapper scripts (`menu.sh` for Unix and `menu.cmd` for Windows) that invoke `php tools/menu.php` from the project root.

**Scope:**
- `menu.sh` — new file (project root), Unix/macOS launcher
- `menu.cmd` — new file (project root), Windows launcher

**Deliverables:**
- `menu.sh` that changes to the script's directory and invokes `php tools/menu.php`
- `menu.cmd` that changes to the script's directory and invokes `php tools/menu.php`
- `menu.sh` is marked executable (`chmod +x`)

**Acceptance Criteria:**
1. `menu.sh` starts with a `#!/usr/bin/env bash` shebang line.
2. `menu.sh` uses `cd "$(dirname "$0")"` to ensure the working directory is the project root regardless of where the script is invoked from.
3. `menu.sh` is executable (has the execute permission bit set).
4. `menu.cmd` uses `cd /d "%~dp0"` to set the working directory to the script's location.
5. Both scripts invoke `php tools/menu.php` with no additional arguments.
6. Running `./menu.sh` from the project root launches the interactive PHP menu (requires WP-004).

**Estimated Complexity:** Low

**Notes:** These scripts depend on WP-004 (`tools/menu.php`) to produce meaningful output. The scripts themselves are intentionally minimal — all logic lives in PHP. Verify `menu.sh` has the correct line endings (LF, not CRLF) and `menu.cmd` uses CRLF line endings per Windows convention.

---

## WP-004 — Interactive Developer Menu

**Description:** Create the interactive numbered menu script (`tools/menu.php`) that presents common developer tasks and dispatches to Composer commands or the setup script. Includes a vendor pre-flight check that runs `composer install` if the `vendor/` directory is missing.

**Scope:**
- `tools/menu.php` — new file

**Deliverables:**
- Vendor pre-flight check: detects missing `vendor/` directory and runs `composer install` automatically
- Menu display function showing options 1–6 and 0 (Exit)
- Dispatch function routing each option to its corresponding command
- Main loop that redisplays the menu after each action completes

**Acceptance Criteria:**
1. The file declares `strict_types=1`.
2. The file uses `array()` syntax, not `[]` (project convention).
3. If `vendor/` does not exist, `composer install` runs automatically before the menu is shown.
4. If `composer install` fails (non-zero exit code), the script aborts with exit code 1.
5. The menu displays options 1–6 and 0 (Exit) with ANSI colour formatting.
6. Option 1 dispatches to `php tools/setup-local.php` via `passthru()`.
7. Option 2 dispatches to `composer build`.
8. Option 3 prompts for a filter pattern; if provided, runs `composer test-filter -- <pattern>`; if empty, runs `composer test`.
9. Option 4 dispatches to `composer clear-caches`.
10. Option 5 dispatches to `composer seed-tests`.
11. Option 6 dispatches to `composer analyze`.
12. Option 0 prints a goodbye message and exits.
13. Unrecognised input displays an error message and re-shows the menu.
14. The menu loops after each completed action until the user selects 0.

**Estimated Complexity:** Medium

**Notes:** Depends on WP-002 (cli-utilities.php) for the shared I/O functions. The vendor pre-flight runs before including shared utilities (since those utilities are in the project tree, not in vendor, this ordering is a design choice for consistency). Option 1 depends on WP-005 (setup-local.php). Options 2–6 depend on existing Composer scripts already defined in the project.

---

## WP-005 — Local Environment Setup Script

**Description:** Create the standalone interactive setup script (`tools/setup-local.php`) that configures the local development environment by prompting for database and UI settings, generating config files from `.dist.php` templates, creating the database and importing the schema, and running `composer seed-tests`.

**Scope:**
- `tools/setup-local.php` — new file
- Reads template files: `tests/application/config/test-db-config.dist.php`, `tests/application/config/test-ui-config.dist.php`
- Generates target files: `tests/application/config/test-db-config.php`, `tests/application/config/test-ui-config.php`
- Reads schema file: `tests/sql/testsuite.sql`

**Deliverables:**
- Path constants for config directory, template files, target files, and schema file
- SIGINT handler (when `pcntl` is available) to restore terminal echo if interrupted during password prompts
- `extractConstantValue()` — regex-based extraction of constant values from existing config files (string, integer, null)
- `parseExistingDbConfig()` — reads existing `test-db-config.php` for idempotent defaults
- `parseExistingUiConfig()` — reads existing `test-ui-config.php` for idempotent defaults
- `collectDatabaseSettings()` — interactive prompts for DB host, name, user, password, port with validation
- `collectUiSettings()` — interactive prompts for base URL and system email recipients
- `testDatabaseConnection()` — PDO connection attempt (without selecting a database)
- `replaceConfigConstant()` — regex-based replacement of constant values in template content (string, integer, null)
- `generateDbConfig()` — reads `.dist.php` template, replaces constants, writes `test-db-config.php`
- `generateUiConfig()` — reads `.dist.php` template, replaces constants, writes `test-ui-config.php`
- `ensureDatabase()` — creates the database if it does not exist, imports `testsuite.sql` schema
- Main entry point orchestrating the complete setup flow with re-prompt loop on DB connection failure

**Acceptance Criteria:**
1. The file declares `strict_types=1`.
2. The file uses `array()` syntax, not `[]` (project convention).
3. The script is standalone — it does not require the framework bootstrap or autoloader.
4. Running the script interactively generates both `test-db-config.php` and `test-ui-config.php` from their `.dist.php` templates.
5. `TESTSUITE_DB_PORT` is written as a bare integer when a numeric value is provided, and as `null` when left empty or entered as "null".
6. `TESTS_SESSION_TYPE` is always set to `'NoAuth'` and is not prompted.
7. The CAS config file (`test-cas-config.php`) is not generated by this script.
8. Re-running the script reads existing config file values as defaults; pressing Enter through all prompts preserves existing values.
9. If the database connection fails, the user is re-prompted for DB credentials until a successful connection is established.
10. Database name input is validated to allow only letters, digits, and underscores.
11. Port input is validated to be either empty/null or a positive integer.
12. If the target database does not exist, the script creates it with `utf8mb4` charset and `utf8mb4_unicode_ci` collation.
13. The schema from `tests/sql/testsuite.sql` is imported into the database.
14. After config files and schema are ready, `composer seed-tests` is called via `passthru()`.
15. If `composer seed-tests` fails, the script reports a warning but does not abort (config files and schema remain intact).
16. The SIGINT handler (when `pcntl` is available) restores terminal echo before exiting.

**Estimated Complexity:** High

**Notes:** This is the most complex WP in the project. It is kept as a single WP because all functions are tightly coupled within one file and the interactive flow requires end-to-end testing as a unit. The complexity is justified: splitting by function within a single file would create artificial boundaries where each sub-WP cannot be independently verified. Depends on WP-002 (cli-utilities.php) for shared I/O functions. The database connection test, creation, and schema import require a running MySQL/MariaDB instance for full verification.

---

## WP-006 — AGENTS.md Documentation Update

**Description:** Update the project's `AGENTS.md` to document the interactive developer menu (`menu.sh` / `menu.cmd`) and the `composer setup` command in a new "Developer Tools Quick Reference" section.

**Scope:**
- `AGENTS.md` — add or update the Developer Tools section

**Deliverables:**
- Documentation of `menu.sh` / `menu.cmd` / `php tools/menu.php` as entry points
- Table of menu options (1–6 and 0) with descriptions
- Documentation of `composer setup` as the direct setup command
- Note about vendor pre-flight behaviour
- Note about generated config files being gitignored

**Acceptance Criteria:**
1. `AGENTS.md` contains a "Developer Tools Quick Reference" section (or equivalent heading).
2. The section documents all three ways to launch the menu: `./menu.sh`, `menu.cmd`, `php tools/menu.php`.
3. All six menu options plus the exit option are listed with their corresponding actions.
4. `composer setup` is documented as a direct alternative to menu option 1.
5. The vendor pre-flight behaviour (automatic `composer install` when `vendor/` is missing) is mentioned.
6. A note states that generated config files (`test-db-config.php`, `test-ui-config.php`) are in `.gitignore` and must not be committed.
7. The documentation accurately reflects the implemented behaviour of the menu and setup scripts.

**Estimated Complexity:** Low

**Notes:** This is a documentation-only WP. It depends on all implementation WPs (WP-001 through WP-005) being complete so the documentation accurately reflects the final implementation. The AGENTS.md already contains other quick-reference sections (Testing, Static Analysis, Build) — the new section should follow the same style and formatting conventions.

```
###  Path: `/docs/agents/plans/2026-05-06-composer-setup-command/work.md`

```md
# Work Packages — Interactive Developer Menu & `composer setup` Command

| WP | Title | Status | Dependencies | Spec File | Pipeline Stages |
|----|-------|--------|--------------|-----------|-----------------|
| WP-001 | Shared CLI Utilities Library | READY | — | work/WP-002.md | implementation → qa → code-review → documentation |
| WP-002 | Root Launcher Scripts | READY | — | work/WP-003.md | implementation → qa → code-review → documentation |
| WP-003 | Project Configuration Prerequisites | READY | — | work/WP-001.md | implementation → qa → code-review → documentation |
| WP-004 | Interactive Developer Menu | BLOCKED | WP-001 | work/WP-004.md | implementation → qa → code-review → documentation |
| WP-005 | Local Environment Setup Script | BLOCKED | WP-001 | work/WP-005.md | implementation → qa → security-audit → code-review → documentation |
| WP-006 | AGENTS.md Documentation Update | BLOCKED | WP-003, WP-004, WP-005 | work/WP-006.md | documentation |

> **Note:** The spec file names do not match the WP IDs for WP-001, WP-002, and WP-003
> due to bootstrapper ordering. Follow the `work_package_file` pointer in the ledger
> to find the correct spec file for each WP.

## Dependency Chain

```
WP-001 (Shared CLI Utilities) ──┐
                                 ├─> WP-004 (Developer Menu) ──┐
WP-002 (Launcher Scripts)       │                               │
                                 └─> WP-005 (Setup Script) ────┤
WP-003 (Project Config) ───────────────────────────────────────┼─> WP-006 (AGENTS.md Docs)
```

```
###  Path: `/docs/agents/plans/2026-05-06-composer-setup-command/work/WP-001.md`

```md
# WP-003: Project Configuration Prerequisites

> **Note:** This spec file is referenced by ledger WP-003 via `work_package_file: "work/WP-001.md"`.

## Description

Modify `.gitignore` to un-ignore the `/tools` directory and add the `setup` Composer script to `composer.json`, enabling the new tool scripts to be version-controlled and invoked via `composer setup`.

## Scope

- `.gitignore` — remove the `/tools` exclusion line
- `composer.json` — add `"setup": "php tools/setup-local.php"` to the `"scripts"` section

## Dependencies

- None

## Acceptance Criteria

1. The `/tools` exclusion line is absent from `.gitignore`.
2. `composer.json` contains `"setup": "php tools/setup-local.php"` in the `"scripts"` section.
3. Files under `tools/` are trackable by Git (not ignored).
4. The existing `.gitignore` entries for generated config files (`tests/application/config/test-db-config.php`, `tests/application/config/test-ui-config.php`) remain intact.

## Active Pipeline Stages

`implementation` -> `qa` -> `code-review` -> `documentation`

```
###  Path: `/docs/agents/plans/2026-05-06-composer-setup-command/work/WP-002.md`

```md
# WP-001: Shared CLI Utilities Library

> **Note:** This spec file is referenced by ledger WP-001 via `work_package_file: "work/WP-002.md"`.

## Description

Create the shared CLI utility functions library (`tools/include/cli-utilities.php`) that provides console I/O helpers used by both `menu.php` and `setup-local.php`.

## Scope

- `tools/include/cli-utilities.php` — new file

## Dependencies

- None

## Acceptance Criteria

1. The file declares `strict_types=1`.
2. `writeln()` outputs text followed by `PHP_EOL` and accepts an empty string for blank lines.
3. `color()` supports the five named colours: `green`, `red`, `yellow`, `cyan`, `bold`.
4. `color()` returns plain text (no ANSI codes) when `PHP_OS_FAMILY === 'Windows'` or when an unrecognised colour is passed.
5. `prompt()` displays the default value in square brackets and returns it when the user presses Enter without input.
6. `promptPassword()` uses `stty -echo` on Unix to hide input and restores echo afterwards.
7. `promptPassword()` falls back to visible input on Windows with a warning message.
8. All four functions are guarded by `function_exists()` checks.
9. The file uses `array()` syntax, not `[]` (project convention).

## Active Pipeline Stages

`implementation` -> `qa` -> `code-review` -> `documentation`

```
###  Path: `/docs/agents/plans/2026-05-06-composer-setup-command/work/WP-003.md`

```md
# WP-002: Root Launcher Scripts

> **Note:** This spec file is referenced by ledger WP-002 via `work_package_file: "work/WP-003.md"`.

## Description

Create the thin shell wrapper scripts (`menu.sh` for Unix and `menu.cmd` for Windows) that invoke `php tools/menu.php` from the project root.

## Scope

- `menu.sh` — new file (project root), Unix/macOS launcher
- `menu.cmd` — new file (project root), Windows launcher

## Dependencies

- None

## Acceptance Criteria

1. `menu.sh` starts with a `#!/usr/bin/env bash` shebang line.
2. `menu.sh` uses `cd "$(dirname "$0")"` to ensure the working directory is the project root regardless of where the script is invoked from.
3. `menu.sh` is executable (has the execute permission bit set).
4. `menu.cmd` uses `cd /d "%~dp0"` to set the working directory to the script's location.
5. Both scripts invoke `php tools/menu.php` with no additional arguments.
6. Running `./menu.sh` from the project root launches the interactive PHP menu (requires WP-004).

## Active Pipeline Stages

`implementation` -> `qa` -> `code-review` -> `documentation`

```
###  Path: `/docs/agents/plans/2026-05-06-composer-setup-command/work/WP-004.md`

```md
# WP-004: Interactive Developer Menu

## Description

Create the interactive numbered menu script (`tools/menu.php`) that presents common developer tasks and dispatches to Composer commands or the setup script. Includes a vendor pre-flight check that runs `composer install` if the `vendor/` directory is missing.

## Scope

- `tools/menu.php` — new file

## Dependencies

- WP-001 (Shared CLI Utilities Library)

## Acceptance Criteria

1. The file declares `strict_types=1`.
2. The file uses `array()` syntax, not `[]` (project convention).
3. If `vendor/` does not exist, `composer install` runs automatically before the menu is shown.
4. If `composer install` fails (non-zero exit code), the script aborts with exit code 1.
5. The menu displays options 1–6 and 0 (Exit) with ANSI colour formatting.
6. Option 1 dispatches to `php tools/setup-local.php` via `passthru()`.
7. Option 2 dispatches to `composer build`.
8. Option 3 prompts for a filter pattern; if provided, runs `composer test-filter -- <pattern>`; if empty, runs `composer test`.
9. Option 4 dispatches to `composer clear-caches`.
10. Option 5 dispatches to `composer seed-tests`.
11. Option 6 dispatches to `composer analyze`.
12. Option 0 prints a goodbye message and exits.
13. Unrecognised input displays an error message and re-shows the menu.
14. The menu loops after each completed action until the user selects 0.

## Active Pipeline Stages

`implementation` -> `qa` -> `code-review` -> `documentation`

```
###  Path: `/docs/agents/plans/2026-05-06-composer-setup-command/work/WP-005.md`

```md
# WP-005: Local Environment Setup Script

## Description

Create the standalone interactive setup script (`tools/setup-local.php`) that configures the local development environment by prompting for database and UI settings, generating config files from `.dist.php` templates, creating the database and importing the schema, and running `composer seed-tests`.

## Scope

- `tools/setup-local.php` — new file
- Reads template files: `tests/application/config/test-db-config.dist.php`, `tests/application/config/test-ui-config.dist.php`
- Generates target files: `tests/application/config/test-db-config.php`, `tests/application/config/test-ui-config.php`
- Reads schema file: `tests/sql/testsuite.sql`

## Dependencies

- WP-001 (Shared CLI Utilities Library)

## Acceptance Criteria

1. The file declares `strict_types=1`.
2. The file uses `array()` syntax, not `[]` (project convention).
3. The script is standalone — it does not require the framework bootstrap or autoloader.
4. Running the script interactively generates both `test-db-config.php` and `test-ui-config.php` from their `.dist.php` templates.
5. `TESTSUITE_DB_PORT` is written as a bare integer when a numeric value is provided, and as `null` when left empty or entered as "null".
6. `TESTS_SESSION_TYPE` is always set to `'NoAuth'` and is not prompted.
7. The CAS config file (`test-cas-config.php`) is not generated by this script.
8. Re-running the script reads existing config file values as defaults; pressing Enter through all prompts preserves existing values.
9. If the database connection fails, the user is re-prompted for DB credentials until a successful connection is established.
10. Database name input is validated to allow only letters, digits, and underscores.
11. Port input is validated to be either empty/null or a positive integer.
12. If the target database does not exist, the script creates it with `utf8mb4` charset and `utf8mb4_unicode_ci` collation.
13. The schema from `tests/sql/testsuite.sql` is imported into the database.
14. After config files and schema are ready, `composer seed-tests` is called via `passthru()`.
15. If `composer seed-tests` fails, the script reports a warning but does not abort (config files and schema remain intact).
16. The SIGINT handler (when `pcntl` is available) restores terminal echo before exiting.

## Active Pipeline Stages

`implementation` -> `qa` -> `security-audit` -> `code-review` -> `documentation`

```
###  Path: `/docs/agents/plans/2026-05-06-composer-setup-command/work/WP-006.md`

```md
# WP-006: AGENTS.md Documentation Update

## Description

Update the project's `AGENTS.md` to document the interactive developer menu (`menu.sh` / `menu.cmd`) and the `composer setup` command in a new "Developer Tools Quick Reference" section.

## Scope

- `AGENTS.md` — add or update the Developer Tools section

## Dependencies

- WP-003 (Project Configuration Prerequisites)
- WP-004 (Interactive Developer Menu)
- WP-005 (Local Environment Setup Script)

## Acceptance Criteria

1. `AGENTS.md` contains a "Developer Tools Quick Reference" section (or equivalent heading).
2. The section documents all three ways to launch the menu: `./menu.sh`, `menu.cmd`, `php tools/menu.php`.
3. All six menu options plus the exit option are listed with their corresponding actions.
4. `composer setup` is documented as a direct alternative to menu option 1.
5. The vendor pre-flight behaviour (automatic `composer install` when `vendor/` is missing) is mentioned.
6. A note states that generated config files (`test-db-config.php`, `test-ui-config.php`) are in `.gitignore` and must not be committed.
7. The documentation accurately reflects the implemented behaviour of the menu and setup scripts.

## Active Pipeline Stages

`documentation`

```
###  Path: `/docs/agents/plans/2026-05-07-seed-rework-followup/plan.md`

```md
# Plan

## Summary

Follow-up cleanup plan addressing the four actionable items identified in the `2026-05-05-test-db-seed-data-rework-1` synthesis. The items are: (1) replace or pin the floating `shark/simple_html_dom` `dev-master` dependency, (2) remove the now-dead `ComposerScripts::doSeedTests()` method, (3) add bidirectional `@see` cross-references to `seedSystemUsers()` and `seedCountries()`, and (4) add a PHPStan stub for `tools/include/cli-utilities.php` so that `tools/` scripts can optionally be analyzed without 91 false-positive `function.notFound` errors.

## Architectural Context

### Package: `shark/simple_html_dom`

- **Declared in:** `composer.json` line 96 as `"shark/simple_html_dom": "dev-master"`.
- **Installed version:** 1.5 (from GitHub `samacs/simple_html_dom`, commit `d0a7686`).
- **Framework usage:** NONE — the framework source (`src/`) does not reference this package at all.
- **Downstream usage:** The HCP Editor uses `str_get_html()` and the `simple_html_dom_node` class in `assets/classes/Maileditor/Mails/Mail/FrozenTextParser.php` (lines 210, 216).
- **PHP 8.4 deprecation:** `$http_response_header` usage on lines 99, 102, 113 of `simple_html_dom.php` emits deprecation notices at runtime.
- **Risk:** Floating `dev-master` pins bypass Composer's security advisory checks (OWASP A06).

### Method: `ComposerScripts::doSeedTests()`

- **Location:** `src/classes/Application/Composer/ComposerScripts.php` lines 129–147.
- **Current invocations:** Zero. `composer seed-tests` now invokes `php tools/seed-truncate.php` and `php tools/seed-insert.php`. No HCP Editor code calls `doSeedTests()` (the planned HCP Editor seeding feature in `docs/agents/plans/2026-05-05-test-db-seed-data/plan.md` has not been implemented).
- **Contains:** `truncateAllTables()`, two `resetCollection()` calls, three seed method calls, and progress echoing — duplicating the logic now handled by the two CLI scripts.

### PHPDoc Cross-References

- **`seedLocales()` (line 214):** Has `@see self::SEED_LOCALES` and `@see self::seedCountries()`.
- **`seedSystemUsers()` (line 180):** Has `@see ComposerScripts::doSeedTests()` only — no cross-reference to sibling seed methods.
- **`seedCountries()` (line 284):** Has no `@see` cross-references to sibling seed methods at all.

### PHPStan and `tools/setup-local.php`

- `tools/` is NOT in PHPStan's `paths:` configuration, so the 91 `function.notFound` errors do NOT appear in standard `composer analyze` runs.
- The errors would appear only if `tools/` is explicitly added to analysis scope in the future.
- Root cause: `tools/setup-local.php` includes `tools/include/cli-utilities.php` via `require_once` at runtime; PHPStan cannot resolve these functions statically without a stub or bootstrap entry.

## Approach / Architecture

Four independent, low-risk changes:

1. **Replace `shark/simple_html_dom`** with `mistralys/simple_html_dom` — a drop-in replacement package that is PHP 8.4 compatible and offers stable tagged releases. Since the framework itself has zero usages, the safest approach is to **remove it from the framework's `composer.json`** and let the HCP Editor declare its own dependency on `mistralys/simple_html_dom`. Alternatively, if the framework wants to keep providing it transitively, simply swap the require line.

2. **Remove `doSeedTests()`** — since it has zero callers and its logic is fully superseded by the process-isolated CLI scripts. Add a `@deprecated` notice first if a grace period is desired, or remove outright since no callers exist anywhere in the workspace.

3. **Add `@see` cross-references** to `seedSystemUsers()` and `seedCountries()` docblocks, following the pattern established in `seedLocales()`.

4. **Create a PHPStan stub file** at `tests/phpstan/cli-utilities-stubs.php` that declares the function signatures from `tools/include/cli-utilities.php`, and add it to `phpstan.neon`'s `bootstrapFiles` list. This allows `tools/` to be optionally analyzed without false positives.

## Rationale

- **Replace vs. remove `simple_html_dom`:** Although the framework itself doesn't use the package, it provides it transitively to the HCP Editor. Replacing with `mistralys/simple_html_dom` (a maintained, PHP 8.4-compatible, drop-in fork) eliminates the security/deprecation issues while keeping the transitive dependency chain intact — no downstream changes needed.
- **Remove vs. deprecate `doSeedTests()`:** There are zero callers across the entire workspace (framework + HCP Editor). The method's contract (single-process with `resetCollection()`) is incompatible with the new architecture. Deprecating it adds noise without serving anyone; removal is cleaner.
- **PHPDoc cross-references:** Navigability of the seeding surface — a developer reading any one seed method should discover the others immediately.
- **PHPStan stub:** Defensive improvement — prevents a flood of false positives if `tools/` is ever added to analysis scope (as recommended by the CTX documentation's completeness goals).

## Detailed Steps

### Step 1: Replace `shark/simple_html_dom` with `mistralys/simple_html_dom`

1. Replace the line `"shark/simple_html_dom": "dev-master"` with `"mistralys/simple_html_dom": "^2.0"` in `composer.json` `require` section.
2. Run `composer update mistralys/simple_html_dom --with-all-dependencies` to install the new package and remove the old one (the `replaces` metadata in `mistralys/simple_html_dom` handles the removal of `shark/simple_html_dom` automatically).
3. Run `composer install` to verify no framework code breaks.
4. Run `composer analyze` to verify no PHPStan regressions.

### Step 2: Remove `ComposerScripts::doSeedTests()`

1. Delete the `doSeedTests()` method (lines 129–147) from `src/classes/Application/Composer/ComposerScripts.php`.
2. Remove the `@see ComposerScripts::doSeedTests()` tag from `seedSystemUsers()` docblock in `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php` (line 172).
3. Run `composer dump-autoload`.
4. Run `composer test-filter -- Seed` to verify existing seed tests still pass.

### Step 3: Add PHPDoc `@see` cross-references

1. In `TestSuiteBootstrap::seedSystemUsers()` docblock, add:
   - `@see self::seedLocales()`
   - `@see self::seedCountries()`
2. In `TestSuiteBootstrap::seedCountries()` docblock, add:
   - `@see self::SEED_COUNTRIES`
   - `@see self::seedSystemUsers()`
   - `@see self::seedLocales()`
3. In `TestSuiteBootstrap::seedLocales()` docblock, add:
   - `@see self::seedSystemUsers()`

### Step 4: Create PHPStan stub for CLI utilities

1. Create `tests/phpstan/cli-utilities-stubs.php` containing function signatures (no bodies) for all functions defined in `tools/include/cli-utilities.php`.
2. Add `- ./tests/phpstan/cli-utilities-stubs.php` to the `bootstrapFiles` array in `phpstan.neon`.
3. Optionally add `- ./tools` to the PHPStan `paths:` array and verify zero errors appear.
4. Run `composer analyze` to confirm no regressions.
5. Update the AGENTS.md description of `tests/phpstan/` from "contains only bootstrap constants" to "contains PHPStan bootstrap files (constants and function stubs)".

### Step 5: Regenerate documentation

1. Run `composer build` to regenerate all CTX context. The removal of `doSeedTests()` from source will be reflected automatically in `.context/modules/composer/architecture-core.md` (generated from the PHP source via `php-content-filter`).

> **Note:** Historical plan documents in `docs/agents/plans/` that reference `doSeedTests()` will continue to appear in the generated `framework-core-system-overview.md` — this is acceptable as historical context. Do NOT manually edit `.context/` generated files; they will be overwritten by `composer build`.

## Dependencies

- Steps 1–4 are independent and can be executed in parallel.
- Step 5 depends on Steps 2 and 1 being complete.

## Required Components

- `composer.json` (replace `shark/simple_html_dom` with `mistralys/simple_html_dom`)
- `src/classes/Application/Composer/ComposerScripts.php` (remove `doSeedTests()`)
- `src/classes/Application/Bootstrap/Screen/TestSuiteBootstrap.php` (PHPDoc updates)
- `tests/phpstan/cli-utilities-stubs.php` (new file)
- `phpstan.neon` (add stub to bootstrapFiles)
- `.context/modules/composer/architecture-core.md` (documentation update)
- `.context/framework-core-system-overview.md` (documentation update)

## Assumptions

- The HCP Editor's planned `seedTests()` feature (from `docs/agents/plans/2026-05-05-test-db-seed-data/plan.md`) has NOT been implemented yet and will not call `doSeedTests()`. If it is implemented in the future, it should use the CLI script pattern instead.
- `mistralys/simple_html_dom` is a drop-in replacement for `shark/simple_html_dom` and provides the same `str_get_html()` / `simple_html_dom` / `simple_html_dom_node` API. The HCP Editor's `FrozenTextParser.php` will continue to work without changes.
- The function signatures in `tools/include/cli-utilities.php` are stable and can be stubbed without frequent maintenance.

## Constraints

- Array syntax: `array()` only — never `[]`.
- No constructor promotion.
- `declare(strict_types=1)` in every new PHP file.
- Run `composer dump-autoload` after any class file modification.

## Out of Scope

- Replacing `str_get_html()` usage in the HCP Editor's `FrozenTextParser.php` — that belongs to an HCP Editor plan.
- The HCP Editor's own test database seeding feature (synthesis item #5 from the prior plan).
- Refactoring the CTX-generated documentation sections that reference older plan content (the plan references in `.context/framework-core-system-overview.md` are auto-generated and will be refreshed by `composer build`).
- Revising the HCP Editor plan `docs/agents/plans/2026-05-05-test-db-seed-data/plan.md` (which delegates to `doSeedTests()`) — that plan must be updated to use the CLI script pattern before implementation, but that revision belongs to an HCP Editor task.

## Acceptance Criteria

1. `composer.json` requires `mistralys/simple_html_dom` (stable `^2.0` constraint) instead of `shark/simple_html_dom`.
2. `composer install` completes without errors in the framework project.
3. `ComposerScripts::doSeedTests()` no longer exists in the source.
4. `composer seed-tests` still works correctly (uses CLI scripts, unaffected by removal).
5. `seedSystemUsers()`, `seedLocales()`, and `seedCountries()` all have bidirectional `@see` cross-references to each other.
6. `tests/phpstan/cli-utilities-stubs.php` exists and is referenced in `phpstan.neon`.
7. `composer analyze` passes with no new errors.
8. All modified documentation is consistent with the code changes.

## Testing Strategy

- **Step 1:** Run `composer install` and `composer analyze` after swapping the dependency. Run `composer test-filter -- Html` to verify no framework tests regress with the new package.
- **Step 2:** Run `composer test-filter -- Seed` and `composer seed-tests` to verify seeding still works.
- **Step 3:** PHPDoc-only change — verify with `composer analyze`.
- **Step 4:** Run `composer analyze` to verify no new errors from the stub.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **HCP Editor breaks after `simple_html_dom` swap** | `mistralys/simple_html_dom` is a drop-in replacement providing the same `str_get_html()` API. The HCP Editor inherits the framework's dependency transitively — no changes needed there. |
| **Undiscovered caller of `doSeedTests()` in a deployment script** | Full workspace search confirmed zero callers. The method echoes to stdout, making it easy to detect if something was using it. |
| **PHPStan stub drifts from actual CLI utilities** | The utilities file is small (233 lines) and rarely changes. Add a comment in the stub pointing to the source file. |
| **`composer build` overwrites documentation changes** | Run `composer build` AFTER making documentation edits to `.context/` source configs, not the generated files themselves. Manual edits to `.context/` generated files will be overwritten. |

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
> Generated: 2026-05-07T07:26:58Z

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

> Auto-generated on 2026-05-07 09:26:58. Do not edit manually.

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
| `composer seed-tests` | Truncate and fully re-seed the test database (users, locales, countries) | `composer seed-tests` |

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

### Transaction Shutdown Handler

During `_boot()`, `TestSuiteBootstrap` calls `registerTransactionCleanupHandler()`, which registers a PHP shutdown function that invokes `DBHelper::rollbackConditional()`. This handler fires whenever the PHP process exits — including after a crash, fatal error, or `Ctrl-C` during a test run.

**Why this matters:** If a test process dies mid-transaction (e.g., due to an out-of-memory error or an unhandled signal), InnoDB keeps the row locks held by that transaction until it times out. Subsequent test runs that try to acquire the same locks stall for the full `innodb_lock_wait_timeout` duration (default: 50 seconds). The shutdown handler ensures those locks are released immediately by rolling back any open transaction when the process terminates.

`DBHelper::rollbackConditional()` is guarded by `isTransactionStarted()` — it is a no-op when no transaction is active, so there is no risk of interfering with a test run that completed normally.

---

## Framework Test Application

The framework includes a working test application in `tests/application/` that provides concrete implementations of framework abstractions. This application is used by the test suite to exercise framework features against real database tables and screens.

---

## Local Test Environment Setup

The test suite requires local configuration files that are **not committed** to the repository. Before running tests for the first time, copy the distribution templates and configure them for the local environment:

1. Copy `tests/application/config/test-db-config.dist.php` → `tests/application/config/test-db-config.php`
2. Copy `tests/application/config/test-ui-config.dist.php` → `tests/application/config/test-ui-config.php`
3. Copy `tests/application/config/test-cas-config.dist.php` → `tests/application/config/test-cas-config.php`

Edit `test-db-config.php` and set the correct database host, name, user, and password. The database name defaults to `app_framework_testsuite`; import `docs/sql/pristine.sql` to initialise it.

Edit `test-ui-config.php` and adjust `TESTS_BASE_URL` to the URL at which `tests/application` is reachable on the local webserver.

`test-cas-config.php` is only required when `TESTS_SESSION_TYPE` is set to `CAS` in `test-ui-config.php`. For most local runs the default `NoAuth` session type is sufficient and the CAS config can be left as-is.

### Seeding the Test Database

After importing `docs/sql/pristine.sql`, run `composer seed-tests` to populate the test database before running the test suite:

```
composer seed-tests
```

The command runs two process-isolated steps in sequence:

1. **`php tools/seed-truncate.php`** — empties all base tables via `TestSuiteBootstrap::truncateAllTables()`, then exits. The process terminates, destroying all in-memory ORM caches.
2. **`php tools/seed-insert.php`** — boots in a fresh process (no ORM cache state), then calls `TestSuiteBootstrap::seedSystemUsers()`, `TestSuiteBootstrap::seedLocales()`, and `TestSuiteBootstrap::seedCountries()` in that order.

Because the insertion step starts in a new process, there are no stale ORM caches to invalidate.

The command is **idempotent**: running `composer seed-tests` twice in succession produces the same result with no errors. It is safe to run on an already-seeded database at any time.

**If system users are missing when the test suite boots**, `TestSuiteBootstrap::configureUsers()` throws a `BootException` (error code `175001`) with a message listing the missing user IDs and directing the developer to run `composer seed-tests`. You will see an error similar to:

```
BootException: System users [2, 5] are missing from the test database. Run: composer seed-tests
```

Re-run `composer seed-tests` to resolve it.

### Seeding Locales

`TestSuiteBootstrap::seedLocales()` inserts the two standard test locales (`de_DE`, `en_UK`) into both the `locales_application` and `locales_content` tables.

```php
TestSuiteBootstrap::seedLocales();
```

The method is **idempotent**: it uses `DBHelper::recordExists()` to check for each locale in both `locales_application` and `locales_content` before inserting, so it can be called on a non-empty database without throwing a duplicate-key exception.

All operations are wrapped in a transaction; any failure triggers a `DBHelper::rollbackConditional()` and re-throws the original exception.

### Seeding Countries

`TestSuiteBootstrap::seedCountries()` populates the test database with a standard set of countries. Call it after `seedSystemUsers()` and `seedLocales()` when building a fully seeded test environment.

```php
TestSuiteBootstrap::seedCountries();
```

The method creates the ZZ invariant country (via `Application_Countries::createInvariantCountry()`, which is internally idempotent), then inserts each country defined in the `SEED_COUNTRIES` constant. Each insert is guarded by an `isoExists()` check, making the method safe to call even without a preceding `truncateAllTables()`.

The eight countries seeded are:

| ISO | Country |
|---|---|
| `de` | Germany |
| `ca` | Canada |
| `fr` | France |
| `it` | Italy |
| `es` | Spain |
| `gb` | United Kingdom |
| `us` | United States |
| `mx` | Mexico |

> **Note on `gb` vs `uk`:** `gb` is the correct ISO 3166-1 alpha-2 code for the United Kingdom. `CountryCollection::filterCode()` normalizes legacy `uk` lookups to `gb` automatically.

All operations are wrapped in a transaction; any failure triggers a `DBHelper::rollbackConditional()` and re-throws the original exception.

### Resetting the Test Database

`TestSuiteBootstrap::truncateAllTables()` empties all base tables in the test database. Use it when you need to wipe and re-seed an already-populated database, for example when automating a fresh-seed flow programmatically.

```php
TestSuiteBootstrap::truncateAllTables();
```

**How it works:**

1. Disables FK checks via `SET FOREIGN_KEY_CHECKS=0`.
2. Fetches all base tables with `SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'` — this intentionally excludes views and temporary tables.
3. Calls `DBHelper::truncate()` for each table.
4. Re-enables FK checks in a `finally` block, guaranteeing restoration even if truncation fails mid-loop.

The method does **not** use a database transaction. MySQL `TRUNCATE` is a DDL statement that auto-commits and cannot be rolled back, so wrapping it in `DBHelper::startTransaction()` would have no effect.

> **Note:** `TRUNCATE` removes rows but leaves the table schema intact — `DBHelper::getTablesList()` remains accurate after truncation and does not return stale data.

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
- **Size**: 211.62 KB
- **Lines**: 4642
File: `framework-core-system-overview.md`
