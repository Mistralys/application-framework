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
- SSO via CAS
- Interface Translations: English, German, French
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

1. Import the SQL file `tests/sql/testsuite.sql` into a database.
2. Open the folder `tests/application/config`.
3. Copy `test-db-config.dist.php` to `test-db-config.php`.
4. Copy `test-ui-config.dist.php` to `test-ui-config.php`.
5. Edit the settings in both files.
6. Access the `tests/application` folder via the webserver.

## Composer commands

These are custom Composer commands that are available 
when developing locally.

### Clear caches

Clears all caches used by the framework, including the dynamic
class cache.

```bash
composer clear-caches
```

## Documentation

The framework's documentation is available locally by pointing a browser to 
the `docs` folder, and online in the separate [Framework Documentation][]
package. 

> It is ideally viewed through the framework's documentation screen, as there
> are some features that are only available there (like code samples that are
> included dynamically). 

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
