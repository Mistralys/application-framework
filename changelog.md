# Application Framework Changelog

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
- Revisionables: Now fully interchangeable with DBHelper collections.
- Revisionables: Added more interfaces for revisionable collections and records.
- Revisionables: Retired the old plain revisionable class. Now all revisionables use the DB system.
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
- TimeTracker: Added autofill feature.
- TimeTracker: Added flavored entry creation methods.
- Admin: Added a base class and interface for request types.
- Admin: Allowing AdminURLInterface as return type in some URL methods.
- DataGrid: Heading rows now support an optional subline text.
- DataGrid: Better heading row styling.
- DataGrid: Added the method `attr()` to grid entries to set row attributes.
- UI: Added a text link style for navigations with `TextLinkNavigation`.
- Core: Deprecated `Application_Exception` in favor of `ApplicationException`.
- Countries: Added an interface for country API parameters to declare the `getCountry()` method.
- Dependencies: Bumped up AppUtils Core to [v2.3.17](https://github.com/Mistralys/application-utils-core/releases/tag/2.3.17).

### Breaking Changes
- Revisionables: Completely revamped, modernized and namespaced the
  revisionables system. Migration is required.

### Deprecations

- All DBHelper base admin screen classes have been deprecated. Replacement classes
  are documented for each to make migration straightforward.
- DBHelper collections with a parent collection must now extend `BaseChildCollection`.
- `Application_Exception` => use `ApplicationException` instead.


---
Older changelog entries can be found in the `docs/changelog-history` folder.
