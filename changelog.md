## v4.1.0 - Revisionable update (Breaking-S)
- Revisionables: Added `getRevisionAuthorXXX()` methods for more consistent naming.
- Revisionables: Added some missing methods in the revisionable interface.
- Revisionables: Storage now automatically disposes of keys that contain revision-dependent instances.
- Revisionables: Tweaked the abstract disposable method setup to handle common internal disposal.
- Revisionables: Added private key handling in the revision storage with `setPrivateKey()`.
- Revisionables: Added disposed checks in all relevant public methods.
- Disposables: `_dispose()` is now called after the child revisionables have been disposed.
- Disposables: `getIdentification()` now handles the disposed state.
- Disposables: Added the "disposing" state with `isDisposing()`.
- Logging: The `APP_LOGGING` configuration is not used anymore.
- Logging: Added `setLoggingEnabled()` to change this at runtime.
- Logging: Added `setMemoryStorageEnabled()` to turn log message storage in memory on or off.
- Logging: The memory storage option allows limiting memory usage in long-running tasks.
- Logging: Added `reset()` to reset to defaults.
- Dependencies: Updated AppUtils Core to [v1.2.0](https://github.com/Mistralys/application-utils-core/releases/tag/1.2.0).

### Breaking changes

- Revisionables must rename their `_dispose()` method to `_disposeRevisionable()`.
- Revisionables must rename their `getChildDisposable()` method to `_getChildDisposable()`.
- Revision-dependent classes must now implement the `getIdentification()` method.

### Other changes

- Disposables should not implement `getLogIdentifier()` anymore.
- Disposables should replace `getIdentification()` with the protected `_getIdentification()` method.

### Deprecations

- `RevisionableStatelessInterface::getOwnerID()` => `getRevisionAuthorID()`
- `RevisionableStatelessInterface::getOwnerName()` => `getRevisionAuthorName()`
- `Application_Exception_DisposableDisposed` => `DisposableDisposedException`

## v4.0.7 - Layout tweak
- UI: Moved the subnav context menu to the left (icon only).
- Properties Grid: Fixed `0` value not being displayed when using `addAmount()`.

## v4.0.6 - Lookup Item improvements
- Item Lookup: Added getter methods in the result class.
- Item Lookup: Driver lookup items now support namespaces.
- Item Lookup: Added a base class for DBHelper collection items.
- Item Lookup: Added a base class for Revisionable collection items.
- Revisionables: Added a missing NULL check when no current revision is found.

### Deprecations

- Deprecated the `Application_LookupItems_Item` class.

## v4.0.5 - Filter criteria messages
- Filter Criteria: Upgraded the messages to use the `OperationResult` classes.
- Item Lookup: Improved layout to focus on the search results.

## v4.0.4 - DataGrid Bugfix
- DataGrid: Fixed navigating to another page throwing a system error.
- UI: Fixed header navbar overlapping contents ([#64](https://github.com/Mistralys/application-framework/issues/64)).
- Dev: Fixed the user rights overview showing an empty screen.

## v4.0.3 - QA Testing role
- User: Added the right `QATester` to formalize the role of application UI QA tester.
- User: Added `canLoginInMaintenanceMode()`.
- User: Both developers and QA testers can now access the UI during maintenance.
- News: Upgraded screens to use the screen rights handling layer.
- UI: Added docs for the screen rights method `getFeatureRights()`. 

## v4.0.2 - QOL improvements (Breaking-XS)
- UI: Fixed some timing issues with output buffering.
- UI: Added `requireRights()` to the conditionals.
- UI: Added the screen rights handling layer to formalize accessing this information.
- UI: Added the `AllowableMigrationTrait` to ease the migration of the screen rights handling.
- AdminURL: Added the `AdminURLInterface` interface.
- Core: The `Application_Exception` class can now hold page output.
- DataGrid: Filter settings now inherit hidden variables when using `configure()` ([#35](https://github.com/Mistralys/application-framework/issues/35)).
- DataGrid: Saving grid settings in the DB ([#27](https://github.com/Mistralys/application-framework/issues/27)).
- DataGrid: Redirect after saving grid settings ([#26](https://github.com/Mistralys/application-framework/issues/26)).
- Traits: Added `HiddenVariablesTrait` and matching interface.
- Traits: Added `AllowableMigrationTrait` and matching interface ([#62](https://github.com/Mistralys/application-framework/issues/62)).
- DataGrid: Filter settings inherit hidden variables from the grid ([#35](https://github.com/Mistralys/application-framework/issues/35)).
- Forms: Fixed `validateEmail()` not always returning a boolean value.
- Revisionables: Added the change type column in the changelog.
- Revisionables: Fixed `setRevisionKey()` ignoring the changelog data.
- DBHelper: Added the `KeyModified` event to listen to for records.
- DBHelper: Added `isStructureModified()` in records.
- DBHelper: Custom record fields are included in the key modified event.
- DBHelper: `setCustomModified()` now allows setting more arguments, like the structural flag.

### Breaking changes (XS)
- Users: Renamed `getRequestedRoles()` to `getRequestedRights()` for consistency.

## v4.0.1 - Session handling update (Breaking-XS)
- Session: Added event handling.
- Session: Added `onSessionStarted()`.
- Session: Added `onUserAuthenticated()`.
- Session: Added `onBeforeLogOut()`.
- Session: Fixed logging out not having any effect on the session.
- Users: Fixed user login dates not being correctly registered.
- OfflineEvents: Added the system event `SessionInstantiated`.
- OfflineEvents: More robust event and listener loading.
- OfflineEvents: Loading listeners and events from the framework and application.
- AppFactory: Added `createOfflineEvents()`.
- FilterSettings: Fixed error in `registerSearchSetting()` with default settings.
- Driver: Added support for Area class names in `getAdminAreas()`.
- UI: Fixed broken error page layout in some situations.
- Core: Fixed wrong version number in the `VERSION` file.

### Breaking changes (XS)
- Session: The `start()` method has been renamed to `_start()`. 
  Any custom session implementations must be updated accordingly.

## v4.0.0 - Tagging and Revisionables update (Breaking-XL, DB-update L)
- Tags: Added the tagging management.
- Media: Documents are now taggable.
- Tests: The Test DB collection is now taggable.
- DBHelper: Added `getRelationsForField()` to fetch all field relations.
- DBHelper: `insertDynamic` now supports and empty columns array.
- JS: Added the `Logger` utility class.
- ButtonGroup: Added `addButtons()`.
- TreeRenderer: Added the `TreeRenderer` UI helper class.
- UI: Added `createTreeRenderer()`.
- UI: Added the `AdminURL` helper class to create admin URLs.
- DataGrid: Added `BaseListBuilder` class to handle filtered lists.
- Formables: Unified form element creation methods to use `UI_Form` methods.
- Formables: Added the `$id` parameter to `addHiddenVar()`.
- Forms: Moved some element creation methods to `UI_Form`.
- Forms: Code modernization and quality improvements.
- Forms: Modernized the `UI_Form` class, now with strict typing.
- Forms: Fixed switch elements appearing in duplicate.
- Formables: Added some interface methods.
- RevisionCopy: `getParts()` can now return callables.
- FilterSettings: Settings are now class-based.
- FilterSettings: Preferred way to inject is via the setting's `setInjectCallback()`.
- Changelogable: Introduced the `ChangelogHandler` class structure.
- Changelogable: Added a trait to implement the changelog methods with a handler instance.
- DBHelper: Added the `AfterDeleteRecord` event in the base collection class.
- Countries: Added the `Languages` collection to fetch language information.
- Countries: Added the `Locales` collection to handle locale information.
- Countries: Added the possibility to ignore specific countries.
- Countries: Added the `IgnoredCountriesUpdated` event.
- Revisionables: Added a test revisionable collection to the test application.
- Revisionables: Added first unit tests.
- Revisionables: Modernized classes and strict typing.
- Revisionables: Automated the saving of custom revision table keys.
- Revisionables: Added an interface for the revisionable with state.
- Revisionables: Added a trait to implement the standard state setup.
- Revisionables: Added `RevisionableDependentInterface`.
- Revisionables: Added `requireRevisionableMatch()` and `requireRevisionMatch()`. 
- Revisionables: `requireTransaction()` now accepts a reason string.
- Revisionables: Added `isPartChanged()`.
- Revisionables: Fixed some discrepancies in the collection interface inheritances.
- Revisionables: Added `getPrimaryRequestName()`. Request now uses both primary and secondary names.
- Revisionables: Fully integrated the Eventable trait.
- Revisionables: Revision-specific events are now handled using event namespaces.
- Revisionables: Added an event class for the `BeforeSave` event.
- Revisionables: Added the `TransactionEnded` event.
- Revisionables: Fixed transaction without changes not removing the copied revision.
- Revisionables: Fixed transaction rollback committing the DB transaction even when not managed.
- Eventables: Added the event namespace concept.
- Eventables: Added the overridable `getEventNamespace()` method.
- Eventables: Added the possibility to ignore events.
- Connectors: `createConnector()` now accepts class names.
- Connectors: `createMethod()` now accepts class names.
- Connectors: Namespaced some classes.
- Users: Streamlined the way right groups are registered.
- Users: Fixed the developer right not being applied correctly.
- Dependencies: Updated AppUtils-Core to [v1.1.4](https://github.com/Mistralys/application-utils-core/releases/tag/1.1.4).

### Database changes (L)

The SQL update file must be imported: 

``` 
docs/sql/2024-02-15-revisionables-tagging.sql
```  

> Note: An existing application can use this framework release without
> the database update, as long as the tagging admin area is not enabled 
> in the application driver.

### Breaking changes (XL)
- Revisionables: Added abstract `initStorageParts()` to formalize the saving of parts.
- Revisionables: Renamed all `revdataXXX`-methods to make it easier to understand.
- Revisionables: Renamed and namespaced interfaces.
- Revisionables: Strict typing for all interface methods.
- Renderables: Added strict `string` return type for `_render()` methods.
- Eventables: Removed the `addEventHandler()` method (use `addEventListener()` instead).
- Eventables: Listener registration methods (for example `onBeforeSave()`) now return a listener instance.
- Users: Changed the way right groups are registered. User classes must now implement a new method to do so.

### Deprecations

- Connectors: Deprecated the `Connectors_Connector_Method`.
- FilterSettings: Deprecated `addAutoConfigure()`.
- FilterSettings: Deprecated `getArraySetting()`.
- Countries: Deprecated the `Application_Countries_Exception` class.
- Users: Deprecated the `Application_User_Extended` class.
- Users: Deprecated the `Application_User::roleExists()` method.

---
Older changelog entries can be found in the `docs/changelog-history` folder.
