# Application Framework Changelog

## v6.2.0 - API improvements
- API: Added support for optional rules in API method parameters.
- API: Added `selectValue()` to parameter containers.
- API: Handlers now send an error response if required parameters are missing.
- API: Methods can now be linked in documentation with the `{api: MethodName}` tag.
- API: Added the `GetAppLocales` method.
- API: Now loading internal framework methods from thematic source folders.
- AdminScreens: `getRequiredRight()` can now return `null` when no right is required.
- FilterCriteria: Fixed a legacy callable causing a PHP error.
- DBHelper: Fixed `getByRequest()` causing a type error with null values.
- DBHelper: Added `addWhereColumnNOT_LIKE()` to the filter criteria.
- DBHelper: Added `getRecordUserKey()` and `requireRecordUserKey()`.
- UI: Lots of small fixes and improvements.
- UI: The footer now always sticks to the bottom in short content pages.
- Core: Started adding an agent guide for common practices.

## v6.1.1 - Renamer Performance
- Renamer: Improved memory usage when processing large amounts of data.
- Renamer: Clearing the index when clearing the configuration.
- Renamer: Fixed results not being correctly grouped by hash.
- Renamer: Columns can now provide custom `WHERE` conditions.

## v6.1.0 - DB Renamer dev tool (DB-Update-XS)
- Renamer: Added a tool to search for and rename text in database columns.
- Renamer: Place `DataColumnInterface` column defs into the application's `RenamerColumns` folder.
- DBHelper: Added `getDriverName()` to get the PDO driver's name (e.g. `mariadb`).
- DBHelper: Added `buildLIKEStatement()` with driver-aware case sensitivity handling.
- DBHelper: Optimized `UPDATE` statements to remove unnecessary assignments.
- Core: Updated the `pristine.sql` file.
- Core: Added a `pristine-data.sql` file with default data for new installations.

### Database Update

The new renamer tool requires importing the update script 
[2025-11-26-renamer-tool.sql](/docs/sql/2025-11-26-renamer-tool.sql) 
to add the necessary tables. This is entirely optional if you do not plan 
to use the renamer tool.

## v6.0.0 - DBHelper, Revisionables and APIs (Breaking-XL)
- DBHelper: Added a base record status screen trait.
- DBHelper: Added `getRecordMicrotimeKey()` to base records.
- DBHelper: Added validations to registered collection keys like `setRegexValidation()`.
- DBHelper: Deprecated and refactored DBHelper base screens.
- DBHelper: Added a dedicated `BaseChildCollection` class to handle parent relations.
- DBHelper: Added an interface for the DBHelper collection.
- DBHelper: Moved parent record handling to a separate child collection class.
- DBHelper: Added the request type base class `BaseDBRecordRequestType`.
- DBHelper: Added more interfaces for DBHelper collections and records.
- DBHelper: Added a formalized DB record decorator system.
- DBHelper: Added a minimal collection interface for filter criteria collection instances to facilitate decorators.
- Revisionables: Now fully interchangeable with DBHelper collections.
- Revisionables: Added more interfaces for revisionable collections and records.
- Revisionables: Retired the old plain revisionable class. Now all revisionables use the DB system.
- Revisionables: Improved the base revisionable admin screen classes with interfaces.
- Disposables: Added the attribute `DisposedAware` to mark methods that check disposed state.
- Revisionables: Retired the stateless revisionables, which were never used in practice.
- Revisionables: Removed the memory revisionables, which were also never used in practice.
- API: Added the API client collection classes.
- API: Added the API management screens.
- API: Added user rights to manage the API.
- API: Added API client test support classes.
- API: Added API grouping support, organized all APIs into groups.
- API: Added flat and grouped overviews with filtering in the documentation.
- API: Added links back to the application from the documentation.
- API: Parameters now support manually selecting a value via `selectValue()`.
- API: Using SourceFolders to load methods from external locations.
- API: Added API key parameter handling.
- API: Added header-based API parameters.
- AJAX: Using SourceFolders to load AJAX handlers from external locations.
- TimeTracker: Added autofill feature.
- TimeTracker: Added flavored entry creation methods.
- Admin: Added a base class and interface for request types.
- Admin: Allowing AdminURLInterface as return type in some URL methods.
- Admin: Screens are now aware of their own location on disk.
- Admin: Screens now use their location to detect subscreens.
- Admin: Starting to prepare for disconnecting screens from the fixed `Area` folder structure.
- SourceFolders: Added the possibility to add external class loading folders for dynamic class locations.
- SourceFolders: Added a dynamic class configuration method in the environment configuration.
- DataGrid: Heading rows now support an optional subline text.
- DataGrid: Better heading row styling.
- DataGrid: Added the method `attr()` to grid entries to set row attributes.
- UI: Added a text link style for navigations with `TextLinkNavigation`.
- Formable: Added `addRuleURL()`.
- Formable: Fixed the enabled status of the form registry not being used.
- Forms: Using SourceFolders to load Form elements from external locations.
- Ratings: Refactored and modernized, added filter classes.
- LockManager: Refactored and modernized, added filter classes.
- Messaging: Refactored and modernized, added filter classes.
- Feedback: Refactored and modernized, added filter classes.
- FilterCriteria: Added integer and string item classes that can be used for object results.
- AppSettings: Refactored and modernized, added filter classes.
- Core: Deprecated `Application_Exception` in favor of `ApplicationException`.
- Core: Removed PHPStan ignored type errors from the configuration.
- Core: Modernized a number of classes to improve static code analysis.
- Core: Added stub classes for unused classes and traits to improve static code analysis.
- Deployment: Using SourceFolders to load deployment tasks from external locations.
- Countries: Added an interface for country API parameters to declare the `getCountry()` method.
- Dependencies: Updated AppUtils Core to [v2.3.17](https://github.com/Mistralys/application-utils-core/releases/tag/2.3.17).
- Dependencies: Bumped up AppUtils Core to [v2.4.0](https://github.com/Mistralys/application-utils-core/releases/tag/2.4.0).

### Breaking Changes

- LooseRecords: Renamed the loose record trait and interface.
- DBHelper: Refactored a majority of classes.
- Revisionables: Completely revamped, modernized and namespaced the
  revisionables system. Migration is required.
- Environment configuration: The abstract method `_registerClassSourceFolders()`
  must now be implemented to register class source folders, if any.

### Database Changes

1. The update script [2025-11-05-api-management.sql](/docs/sql/2025-11-05-api-management.sql) must be run to
    add the necessary tables for API management.
2. The update script [2025-10-29-users-post-update.sql](/docs/sql/2025-10-29-users-post-update.sql) should be run
   to finish the user table migration (if the email hashes have been updated).

### Deprecations

- All DBHelper base admin screen classes have been deprecated. Replacement classes
  are documented for each to make migration straightforward.
- DBHelper collections with a parent collection must now extend `BaseChildCollection`.
- `Application_Exception` => use `ApplicationException` instead.


---
Older changelog entries can be found in the `docs/changelog-history` folder.
