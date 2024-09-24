## v5.2.0 - Developer changelog handling
- Driver: Added missing implementation for the `areaExists()`.
- Driver: Made the use of the `dev-changelog.md` file official.
- Driver: Added the helper class `DevChangelog` to parse the developer changelog file.
- Driver: The `version` file can now optionally be automatically generated.

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
