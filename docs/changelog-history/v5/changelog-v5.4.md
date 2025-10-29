## v5.4.5-hotfix1 - Country button bar fix
- Country ButtonBar: Fixed the button bar not correctly storing the selected country.
- Country ButtonBar: Fixed the `load()` method being called repeatedly.
- Country ButtonBar: Added `setStorageEnabled()` to disable storing the selected country.
- Country ButtonBar: A country can now be selected manually via `selectCountry()`.
- Country ButtonBar: Saving the selected country is now done at render time.
- Country ButtonBar: Added tests.

## v5.4.5 - Client-side logging improvements
- JS: Fixed a data key mismatch in the AJAX error logger for the source page URL.
- JS: Moved the code to handle JS error logging to a dedicated class.
- JS: Improved the logging of JS errors to include the application log ([#15](https://github.com/Mistralys/application-framework/issues/15)).
- JS: Exceptions now include a stack trace.
- JS: The full clientside log is now available for JS errors, including a stack trace.
- Testing: Added a screen in the test application to test the client-side error logging.
- Core: Added utility class `AppDevelAdminURLs` for Devel admin URLs.
- Media: Added `getImageFormat()` to images.
- Media: Added `supportsThumbnails()` to images.
- Media: Thumbnails will no longer be generated for animated GIF images.
- Dependencies: AppUtils updated to get access to ImageHelper enhancements for image formats.
- Dependencies: Updated AppUtils to [v3.1.0](https://github.com/Mistralys/application-utils/releases/tag/3.1.0).

## v5.4.4 - Sections and context buttons
- UI: Fixed context button size in subsections.
- UI: Added `makeContentIndented()` in sections.
- UI: Added size related methods (e.g. `isSmall()`) to buttons.
- App Interface: Added/improved some section examples.

## v5.4.3 - Query summary
- DBHelper: Added the query summary via the request param `query_summary` as a developer.

## v5.4.2 - Query tracking improvements
- DBHelper: `getQueries()` now returns an array of `TrackedQuery` objects.
- DBHelper: The results of `getAll()` are now cached to avoid duplicate queries.
- DBHelper: Added the primary name parameter to the collection's `setIDTable()` method.
- Countries: Now preferring the ISO to identify the invariant country instead of the ID.
- Countries: Fixed `getSupportedISOs()` not correctly handling the invariant country.

## v5.4.1 - Filter Criteria fix
- FilterCriteria: Fixed double-encoded query placeholders.
- FilterCriteria: Added some basic tests.

## v5.4.0 - Class loading, AJAX and more (SQL, Breaking-L)
- Deployments: Added a callback to write the localization files to disk.
- Deployments: Added logging in the deployment process for debugging.
- Revisionables: Improved the record destruction message to use `getIdentification()`.
- Database: Temporarily removed the index on the `known_users::email` column (see [#61](https://github.com/Mistralys/application-framework/issues/61)).
- AppFactory: Added `findClassesInFolder()`.
- AppFactory: Added the static `ClassCacheHandler` to handle dynamic class caching.
- AJAX: Added `getMethodName()` to all AJAX methods.
- AJAX: Using the AppFactory to load method classes.
- AJAX: Method class names now support namespaces and can be freely named.
- AJAX: Added some tests against the application's own AJAX methods.
- AJAX: Now correctly sending the `returnFormat` flag from clientside.
- AJAX: Now correctly recognizing the expected return format.
- UI: Added the jQuery extension `$(*).onClassChange()` to observe element class changes.
- UI: Fixed badge dropdown caret position.
- UI: Dropdowns: Added the AJAX loading feature for asynchronous menu loading.
- UI: Dropdowns: Added `renderMenuItems()` to the menu class.
- UI: Body padding now dynamically adjusted after the main navigation.
- RequestLog: Now sorting log entries from most recent to oldest.
- Icons: Added the `cache` icon.
- CacheControl: Added the `CacheManager` class to handle cache locations.
- CacheControl: Added a dedicated screen in the Developer area.
- DataGrids: Removed padding of checkbox labels in cells.
- Admin: Added a base class for the "Devel > Application configuration" screen.
- TestApp: Added the "Application sets" screen.
- TestApp: Added the "Application configuration" screen.
- Quickstart: Added news entries and media files to the quickstart.
- Quickstart: Added `registerNews()` and `registerMedia()` in the user's recent items base class.
- Quickstart: Fixed loading entries in all requests ([#74](https://github.com/Mistralys/application-framework/issues/74)).
- Notepad: Fixed broken layout when adding new notes ([#57](https://github.com/Mistralys/application-framework/issues/57)).
- DeploymentRegistry: Added a task to clear the class cache.
- DeploymentRegistry: Improved task loading, converted to a collection.
- DeploymentRegistry: Added some tests.
- DBHelper: Results of `idExists()` are now cached to avoid duplicate queries.
- Dependencies: Updated docs to [v1.0.1](https://github.com/Mistralys/application-framework-docs/releases/tag/1.0.1).

### Upgrade guide

See the [upgrade guide](docs/upgrade-guides/upgrade-v5.4.0.md) for details.
