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
it does not exist, import `tests/sql/testsuite.sql`, and seed the test data
automatically. Re-running the command is safe — existing values are shown as
defaults and pressing Enter preserves them.

<details>
<summary>Manual setup (alternative)</summary>

1. Import the SQL file `tests/sql/testsuite.sql` into a database.
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
if it does not exist, imports `tests/sql/testsuite.sql`, and runs
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

### Seed test database

Populates the test database with the system users required by the test
suite. Run this once after importing `tests/sql/testsuite.sql` (or
whenever the system-user records are missing from the test DB).

```bash
composer seed-tests
```

The command requires the test database to be accessible and configured
(see `tests/application/config/test-db-config.php`). On failure it
prints a human-readable error message and exits with code 1, making it
safe to use in CI pipelines.

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
