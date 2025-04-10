## v5.8.0 - Time tracker
- TimeTracker: Added the time tracker management.
- DBHelper: Added an abstract list builder for DBHelper collections.
- ListBuilder: Added a trait for list screens via a list builder.
- StringBuilder: Modified the `reference()` method for a nicer output.
- StringBuilder: `codeCopy()` now handles empty values better.
- StringBuilder: Added `hr()`.
- StringBuilder: Added `heading()` and `h1()` through `h3()`.
- Interface Refs: Improved the text style references.
- Tests: Added the test application to the PHPStan analysis to fix unused trait messages and
- Dependencies: Updated AppUtils Core to [v2.3.8](https://github.com/Mistralys/application-utils-core/releases/tag/2.3.8).
- Dependencies: Updated AppUtils Core to [v2.3.9](https://github.com/Mistralys/application-utils-core/releases/tag/2.3.9).
- Dependencies: Updated AppUtils Core to [v2.3.10](https://github.com/Mistralys/application-utils-core/releases/tag/2.3.10).
  
## v5.7.9 - News update
- News: Improved styling of articles somewhat for readability.
- Markdown: Added the `class` attribute to `{media}` tags.
- Markdown: Added the boolean `thumbnail` attribute to `{media}` tags to turn off thumbnail generation.
- Markdown: Updated text styling somewhat for readability.

## v5.7.8 - Connector update
- Connectors: Added `201` as accepted status code for POST requests ([#76](https://github.com/Mistralys/application-framework/pull/76)) - thanks @danielioinos.

## v5.7.7 - DataGrid form target change
- DataGrids: Removed setting the form target for the whole grid.

Background for this change: Setting the form target for the whole
grid caused regular grid functions like sorting to also open in a 
new tab. This was not the intended behavior and has been removed
in favor of setting it only for specific list actions.

## v5.7.6 - DataGrid enhancement (Deprecation-XS)
- DataGrids: Added `enableSubmitInNewTab()` to make the grid's form be submitted in a new tab.
- DataGrids: Added `setFormTarget()` and `getFormTarget()`.
- DataGrids: Added the `makeAutoWidth()` method so the grid uses only the width its columns need.
- DataGrids: Added `clientCommands()` to generate client-side statements.
- DataGrids: Added `clientCommands()` to grid entries as well.
- DataGrids: Now marking rows as active when the checkbox is checked.
- DataGrids: Improved layout of sorted cells with hover and active rows.
- Tests: Added the utility method `saveTestFile()`.
- BigSelection: Added the possibility to add meta-controls to items with `addMetaControl()`.
- Application: Added URL methods for the storage and temp folders, e.g. `getTempFolderURL()`.

### Deprecations

- `DataGrid::getClientSubmitStatement()` => use `clientCommands()` instead.
- `DataGrid::getClientToggleSelectionStatement()` => use `clientCommands()` instead.

## v5.7.5 - Screen Tie-In improvement
- Screen Tie-Ins: Added the handling of hidden vars with the optional `_getHiddenVars()` method. 
- Screen Tie-Ins: Added the `injectHiddenVars()` method.
- AdminURL: Fixed the return type for `AdminURL::create()` to make PHPStan happy.

## v5.7.4 - Class cache update
- AppFactory: Now setting the `ClassHelper` cache during bootstrap to enable this for all use-cases.
- Dependencies: Bumped up AppUtils core to [v2.3.7](https://github.com/Mistralys/application-utils-core/releases/tag/2.3.7).

## v5.7.3 - AdminURL update
- AdminURL: Fixed the `create()` method not returning the correct instance.
- Dependencies: Bumped up AppUtils to [v3.1.4](https://github.com/Mistralys/application-utils/releases/tag/3.1.4).

## v5.7.2 - AdminURL update
- AdminURL: Now extending AppUtil's `URLBuilder` class.
- Dependencies: Bumped up AppUtils to [v3.1.3](https://github.com/Mistralys/application-utils/releases/tag/3.1.3).

## v5.7.1 - Formable type update
- Formable: Changed methods requiring element instances to accept nodes instead.

## v5.7.0 - Deployment task prioritization (Breaking-XS)
- DeploymentRegistry: Added a prioritization system for deployment tasks.
- DeploymentRegistry: The version update task is now always run first.
- DeploymentRegistry: Fixed the wrong version being stored in the history.
- AppFactory: Replaced the class cache with AppUtil's native class caching.
- Dependencies: Bumped up AppUtils core to [v2.3.6](https://github.com/Mistralys/application-utils-core/releases/tag/2.3.6).

### Breaking changes

- Deployment tasks must now implement the `getPriority()` method. 
  If you have custom deployment tasks, make sure to add this method.

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

## v5.5.5 - Country ButtonBar fix
- Country ButtonBar: Merged hotfix from [v5.4.5-hotfix1](https://github.com/Mistralys/application-framework/releases/tag/5.4.5-hotfix1).
- Country ButtonBar: Added constructor parameter to limit the available countries.
- Country ButtonBar: Fixed duplicate country parameter in links.
- Formable: Added overridable `_handleFormableInitialized()`.

## v5.5.4 - Small enhancements 
- Wizards: Added `_onRecordCreated()` to the DB creation step.
- UI: Linked labels and badges now clearly show that they are clickable on hover.

## v5.5.3 - Session handling
- Session: Added namespaces for disabled authentication and session simulation.

## v5.5.2 - Fixes
- UI: Fixed the request log link in the footer.
- UI: Fixed the broken deployment callback link in the footer.
- Session: Sessions are now namespaced to the auth type to avoid NoAuth / CAS conflicts.

## v5.5.1 - Fixes
- Driver: Moved the `version` file to the application's cache folder.
- Session: Added more logging to debug authentication issues.

## v5.5.0 - Quality of Life and Tagging (Breaking-L)
- Markdown Renderer: Fixed image tags missing the `width` attribute.
- Media: Tags are now shown in the image gallery.
- Media: Tags can be edited in the image gallery.
- Media: Image names are now linked to the media document pages in the image gallery.
- Media: Fixed documents being loaded every time `getByID()` is called.
- Media: Tags can now be edited in the status screen directly.
- Driver: The version handling system now officially uses the `dev-changelog.md` file.
- Driver: The version info has been moved from the `DevChangelog` to `VersionInfo`.
- Driver: Added `AppFactory::createVersionInfo()`.
- Deployments: The version file is now created with a deployment task.
- OfflineEvents: Now using the class cache to load listeners.
- OfflineEvents: The listener folders are now named after the event name.
- OfflineEvents: Listeners now only need to implement the `handleEvent()` method.
- Tags: Added the `TagCollectionRegistry` that collects all taggable record collections.
- Tags: Added `getByUniqueID()` and `uniqueIDExists()`.
- Tags: Added the `TaggableUniqueID` utility class to work with unique IDs.
- AppFactory: Added `createVersionInfo()`.
- UI: Added an ES6 dialog implementation.
- UI: Added the `UI.HideTooltip()` clientside method.
- UI: Added the utility class `ElementIds` to work with element IDs and getting elements.
- FilterSettings: Added `configureFiterSettings()` to make adjustments possible.
- AJAX: Added the base class `BaseHTMLAjaxMethod` for HTML-based requests.
- AJAX: Added the base class `BaseJSONAjaxMethod` for JSON-based requests.
- Session: Fixed session not being destroyed when the user logs out.

### Upgrade guide

See the [upgrade guide](docs/upgrade-guides/upgrade-v5.5.0.md) for details.

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

## v5.3.4 - Upgraded localization library
- Countries: Updated return types to avoid using deprecated AppLocalization types.
- Dependencies: Updated AppLocalization to [v1.5.0](https://github.com/Mistralys/application-localization/releases/tag/1.5.0).

## v5.3.3 - AppSets fix
- AppSets: Fixed not properly recognizing areas, now using the Driver's `areaExists()`.

## v5.3.2 - AJAX exception fix.
- AJAX: Fixed a type issue in the AJAX error logger.

## v5.3.1 - Record Setting Properties
- Record Settings: Added possibility to set runtime properties on record settings.

## v5.3.0 - Admin Screen event handling (Deprecation)
- Admin Screens: Added events and listener methods for screen initialization and rendering.
- Admin Screens: Added possibility to replace a screen's content via events.
- Admin Screens: Added the possibility to disable the action handling via events.
- Testing: Added some test screens for the screen event handling.

### Deprecations

- `Application_Admin_ScreenInterface` has been replaced by `AdminScreenInterface`.

## v5.2.0 - Developer changelog handling
- Driver: Added missing implementation for the `areaExists()`.
- Driver: Made the use of the `dev-changelog.md` file official.
- Driver: Added the helper class `DevChangelog` to parse the developer changelog file.
- Driver: The `version` file can now optionally be automatically generated.
- Tagging: Fixed a hardcoded media collection reference in the tag collection trait. 
- Tagging: Added `_handleHiddenFormVars()` in the record's tagging screen trait.

### Update guide

To make use of the new version file generation mechanism, use the following code
for the driver's `getExtendedVersion()` method:

```php
public function getExtendedVersion() : string
{
    return AppFactory::createDevChangelog()->getCurrentVersion()->getTagVersion();
}
```

## v5.1.1 - DataGrid fix
- DataGrid: Fixed a PHP error when using a string value that corresponds to a PHP function ([#72](https://github.com/Mistralys/application-framework/issues/72))
- DataGrid: Callback cell values are now filtered like all other values.

## v5.1.0 - DataGrid Enhancements
- Clientside: Modernized the renderable classes, converted to ES6.
- Clientside: Converted `ApplicationException` to ES6. 
- DataGrid: Modernized the JS class, converted to ES6.
- DataGrid: Added the grid configuration UI.
- DataGrid: Fixed the column settings storage not being applied.
- DataGrid: Columns can now be sorted and hidden on a per-user basis.
- DataGrid: Converted the main JS classes to ES6 classes.
- DataGrid: It is now possible to reset individual grid settings.
- DataGrid: Cell values can now use callables to generate the value on demand.
- Examples: Added a detailed DataGrid column controls example.
- Users: Added the `$prefix` parameter to the `resetSettings()` method to limit the reset.

### Deprecations

- JS: `Application_BaseRenderable` => `UI_Renderable_Base`
- JS: `Application_RenderableHTML` => `UI_Renderable_HTML`

## v5.0.4 - Minor fixes
- API: Fixed wrongly documented return value of `getParam()`.
- Locales: Added some return type docs to avoid confusion with locale codes.

## v5.0.3 - Auth redirect loop fix
-  Session: Fixed the infinite redirect loop in simulated session mode.

## v5.0.2 - Message log screen fix
- MessageLog: Fixed missing request variables in the message log grid and filters.
- MessageLog: Added the possibility to filter by user.
- MessageLog: Added log message generation for testing for developers.
- Users: Added `getUserInstance()` in the user collection record class. 

### Deprecations:

- `Application_Admin_Area_Mode_Messagelog` => `BaseMessageLogScreen`

## v5.0.1 - Disposable fix
- Disposables: Fixed `dispose()` not checking if currently in the process of disposing.
- Core: The `VERSION` file is now automatically generated and updated.
- Core: Added the `mistralys/changelog-parser` Composer dependency.

## v5.0.0 - Revisionable update (Breaking-S)
- Revisionables: Added `getRevisionAuthorXXX()` methods for more consistent naming.
- Revisionables: Added some missing methods in the revisionable interface.
- Revisionables: Storage now automatically disposes of keys that contain revision-dependent instances.
- Revisionables: Tweaked the abstract disposable method setup to handle common internal disposal.
- Revisionables: Added private key handling in the revision storage with `setPrivateKey()`.
- Revisionables: Added disposed checks in all relevant public methods.
- Revisionables: Added `setStateXXX()` methods to set the state within a transaction.
- Revisionables: Fixed the changelog queue not being cleared after a transaction.
- Disposables: `_dispose()` is now called after the child revisionables have been disposed.
- Disposables: `getIdentification()` now handles the disposed state.
- Disposables: Added the "disposing" state with `isDisposing()`.
- Logging: The `APP_LOGGING` configuration is not used anymore.
- Logging: Added `setLoggingEnabled()` to change this at runtime.
- Logging: Added `setMemoryStorageEnabled()` to turn log message storage in memory on or off.
- Logging: The memory storage option allows limiting memory usage in long-running tasks.
- Logging: Added `reset()` to reset to defaults.
- UI: Added `isActive()` to regular buttons.
- UI: Added static `buttonDropdown()` method to create dropdown buttons.
- UI: Added selecting the active button to the button groups.
- UI: Added the interface `ButtonSizesInterface` and matching trait.
- UI: Added the interface `ActivatableInterface` and matching trait.
- UI: Disabled the Keep-Alive AJAX calls in the logout and request log screens. 
- UI: `UI::tooltip()` now accepts an existing `TooltipInfo` instance.
- UI: Pretty booleans now support tooltips.
- UI: Added the interface `MissingRecordInterface`.
- App Interface: Added button group references.
- Changelogs: Added the `onQueueCommitted()` event handling method.
- RequestLog: Now ensuring that the session uses a different storage from the main app.
- Sessions: Trimmed the authentication process, fixed right presets not being applied ([#67](https://github.com/Mistralys/application-framework/issues/67)).
- Sessions: Removed simulating users, too error-prone and risky.
- Sessions: Added `Application::isUserReady()` as `isSessionReady()` does not include authentication.
- Users: Added new role preset handling via autoloader classes in the `{DriverName}/User/Role` folder.
- Devel Mode: Fixed right presets not being applied.
- Devel Mode: Enabling devel mode can only be turned off when the selected preset has devel rights.
- Devel Mode: Storing the enabled flag in the session instead of a user setting.
- Devel Mode: Removed possibility to simulate users, as the main use case is simulating roles.
- Validatable: Added validation code support to the `Validatable` trait.
- SystemMailer: Added classes to create and send system emails to admins ([#69](https://github.com/Mistralys/application-framework/issues/69)).
- SystemMailer: Setting the system email recipients is now required in the configuration.
- DeployCallback: Now sending an email on success or failure ([#68](https://github.com/Mistralys/application-framework/issues/68))
- Dependencies: Updated AppUtils Core to [v1.2.0](https://github.com/Mistralys/application-utils-core/releases/tag/1.2.0).

### Breaking changes

- The environment configuration must now implement `getSystemEmailRecipients()`.
- Revisionables must rename their `_dispose()` method to `_disposeRevisionable()`.
- Revisionables must rename their `getChildDisposable()` method to `_getChildDisposable()`.
- Revision-dependent classes must now implement the `getIdentification()` method.
- Existing overrides of the native session method `getPrefix()` must be renamed to `_getPrefix()`, and set to `protected`.
- The updated user role handling requires existing role arrays to be moved to separate classes.
- UI: Made the `getRecordMissingURL()` method public.

### Other changes

- Disposables should not implement `getLogIdentifier()` anymore.
- Disposables should replace `getIdentification()` with the protected `_getIdentification()` method.

### Deprecations

- `StandardStateSetup`: deprecated the `makeXXX()` methods in favor of the `setupXXX()` methods.
- `RevisionableStatelessInterface::getOwnerID()` => `getRevisionAuthorID()`
- `RevisionableStatelessInterface::getOwnerName()` => `getRevisionAuthorName()`
- `Application_Exception_DisposableDisposed` => `DisposableDisposedException`

---
Older changelog entries can be found in the `docs/changelog-history` folder.
