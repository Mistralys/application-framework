## v5.6.2 - Filter settings and changelog improvements
- FilterSettings: Added constants for the "Apply" and "Request" button request vars.
- Clientside: Added `UI.RequireElement()` to fetch elements by selector with exception fallback.
- Changelog: Added `limitByCustomField()` to the changelog filter criteria.
- Changelog: Added `getChangelogItemInsertColumns()` to the changelogable interface.
- Changelog Screen: Removed the obsolete "Switch revision" button.
- Changelog Screen: Now displaying the revision number in the grid.
- Changelog Screen: Added the overridable `applyCustomFilters()`.
- Changelog Screen: Added a button to reset the filters.
- Changelog Screen: Added filtering by revision.
- Changelog Screen: Added filtering by start and end date.
- Forms: Added support for `ArrayDataCollection` as default form data set.

### Update notes

Check if any of your revisionable classes override the method
`getChangelogItemPrimary()` provided by the base class.
If they do, make sure that the return value does not include
the item's revision.

## v5.6.1 - Error log fix
- ErrorLog: Fixed `.trace` JSON files causing an exception in AppUtils.
- Dependencies: Updated AppUtils Core for the file type registration feature.
- Dependencies: Updated AppUtils Core minimum version to [v2.3.4](https://github.com/Mistralys/application-utils-core/releases/tag/2.3.4).

## v5.6.0 - Offline listener priority (Breaking-S)
- OfflineEvents: Added the `getPriority()` method to listeners.
- OfflineEvents: Listeners can now optionally be prioritized to adjust their order.
- AdminURL: Added `importURL()` to import parameters and dispatcher from URL strings.
- AdminURL: Added `inheritParam()` to inherit a parameter from the current request.
- AdminURL: Added `getParam()`.
- Composer: Added utility scripts in the class `ComposerScripts`.
- Composer: Use `composer clear-class-cache` to clear the PHP class cache.
- Composer: Use `composer clear-caches` to clear all caches.
- Composer: When running `composer dumpautoload`, the class cache is now automatically cleared.
- Collections: Added the utility class `BaseRecordCollectionTieIn`.
- Collections: Added `IntegerCollectionInterface` and `StringCollectionInterface`.
- Collections: Added `IntegerCollectionItemInterface` and `StringCollectionItemInterface`.
- DBHelper: Added the utility class `BaseDBRecordSelectionTieIn`.
- Application: Added `isInstalledAsDependency()`.
- Application: Added `detectRootFolder()`.
- Session: Now clearing the `$_SESSION` array when destroying the session.
- Tests: Added a tie-in ancestry testing screen.
- Tests: Added a test collection of mythological figures to test the string-based collection interfaces.

### Breaking changes

- Renamed `Application_CollectionItemInterface` to `Application\Collection\CollectionItemInterface`.
- Renamed `Application_CollectionInterface` to `Application\Collection\BaseCollectionInterface`.
- The `BaseCollectionInterface` should not be used directly, but a type-specific like
  `IntegerCollectionInterface` instead.
