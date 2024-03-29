### v2.10.0 - Dependency release
- Dependencies: Relaxed some version constraints for more flexibility.
- Forms: `addDatepicker()` now uses the HTMLDateTimePicker element.
- FilterCriteria: `setSearch()` now accepts null values.

### v2.9.2 - Minor enhancements
- KeepAlive: Avoid sending failed request error report.
- AJAX: Added `SetReportFailure()` to disable error reporting.

### v2.9.1 - Fixes and enhancements
- What's New: Fixed a JavaScript error when no dev changes are present.
- Themes: Added the request log link for developers in the footer.
- Bootstrap: Added some missing screen `DISPATCHER` constants.

### v2.9.0 - Sessions update (breaking-s)
- UI: Removed the `show_user_name` theme option.
- UI: The user menu now has the user's name as tooltip and header.
- UI: Added tooltips to dropdown menu links and buttons.
- Loggable: Fixed the `logUI()` method incorrectly passing on arguments.
- Sessions: Added a prefix for all session variables.
- Sessions: Fixed boot process to split session start and user authentication ([#55](https://github.com/Mistralys/application-framework/issues/55)).
- Sessions: Introduced the `authenticate()` method in the interface.
- Sessions: `storeUser()` now also unpacks the user instance.
- Sessions: Logging out now only clears the user, not the session.
- Sessions: Fixed the logout also clearing the request log authentication.
- Exceptions: Developer info is now automatically enabled in DEV environments.
- Application: `isUserDev()` now works even if no user is available yet.
- RequestLog: Added a button to delete all stored logs.
- RequestLog: Moved the status file from `storage` to `storage/logs`.
- RequestLog: Added a footer in the UI with version information.
- RequestLog: Added logging of session variables.
- RequestLog: Fixed no log being written before the session is started.
- RequestLog: Added an "Overview" breadcrumb item to go back to the overview.
- RequestLog: Added live info dump and possibility to destroy the current session.
- Bootstrap: Logging is now enabled by default until configuration settings are loaded.
- Bootstrap: Added `getKnownSettings()`.
- Driver: Added `isInitialized()` to check if the driver instance is set.
- Dependencies: Updated AppUtils to [v2.5.0](https://github.com/Mistralys/application-utils/releases/tag/2.5.0).

#### Breaking changes (S)

1. Session classes now require the `getPrefix()` method to be implemented.
   This is used to keep the application's session variables separate from
   any other processes running on the same server.
2. Any custom session classes must now implement the `authenticate()`
   method. This is called after the session has been started, specifically
   to check the user's authentication and handle the login process as
   needed.

### v2.8.3 - Bugfixes
- CAS: Fixed `setCASServerURI()` using the wrong setting constant name.

### v2.8.2 - Bugfixes
- Connectors: Fixed the connector exception printing debug information.
- Environments: Added missing phpDocs for chaining config setter methods.
- CAS: Fixed client setup method order causing an exception.

### v2.8.1 - Minor enhancements
- Bootstrap: Replaced a `die()` with an exception.
- Environments: Added specialized `Environments::displayException()` method to display errors.
- TestApplication: Converted to use the new environment handling.

### v2.8.0 - Environment handling update (breaking-xs)
- Environments: Added the `ConfigSettings` class that registers all available settings.
- Environments: Added the `AppSettings` class to access setting values.
- Environments: Added a full class structure to facilitate configuration.
- Environments: Every environment now has its own configuration class.
- Environments: Configuration settings are now set only on activation.
- Environments: The `config-local.php` is now optional (environments can be loaded in `app-config.php`).
- Bootstrap: Autoloading has been moved up to enable autoloading during environment detection.
- Documentation: Moved to the separate Git project [application-framework-docs](https://github.com/Mistralys/application-framework-docs).
- Documentation: The integrated UI is now handled via the doc's composer package.
- Global Functions: Added `getHomeFolder()` utility function.

#### Breaking changes (XS)

- Namespaced and renamed `Application_Environments_Environment`.
- Namespaced and renamed `Application_Environments`.

#### Upgrade guide

To use the new environment handling, follow these steps to upgrade an existing installation:

1. Create a `EnvironmentsConfig` class that extends `BaseEnvironmentsConfig`.
2. Create environment classes for all target environments, extending `BaseEnvironmentConfig`.
3. Return these classes in the config class' `getEnvironmentClasses()` method.
4. Move default and common settings to the config class' `configureDefaultSettings()` method.
5. Move environment-specific settings to the environment classes' `configureCustomSettings()` method.
6. In the `app-config.php`, use the new config class (see code sample below).
7. Remove the `config-local.php` file.
8. If needed, add a local developer config file, loaded via the local environment class.

```php
use AppUtils\FileHelper\FolderInfo;
use DriverClassName\EnvironmentsConfig;

try
{
    (new EnvironmentsConfig(FolderInfo::factory(__DIR__)))
        ->detect();
}
catch (Throwable $e)
{
    Environments::displayException($e);
}
```

### v2.7.4 - Authentication and KeepAlive
- Logger: Added the PSR-3 `PSRLogger` to redirect log messages to the application logger.
- Sessions: The return URL after authentication is now handled automatically via the session.
- CAS Authentication: Tweaked the authentication logic.
- CAS Authentication: CAS log messages are now included in the application log.
- CAS Authentication: Fixed the error log spamming by CAS graceful termination exceptions.
- CAS Authentication: Added `getClient()` to fetch the CAS client instance.
- UI: Fixed the spinner loading icon not being displayed ([#54](https://github.com/Mistralys/application-framework/issues/54)).
- UI: Added a full-fledged Keep Alive mechanism to check the user's session clientside.
- UI: The new authentication lost dialog informs the user when their session has expired.

### v2.7.3 - Logging Tweaks
- Loggable: Added category and label parameters to `logData()`.

### v2.7.2 - CKEditor extension
- MarkupEditor: Added possibility to turn off loading of the bundled CKEditor build.

### v2.7.1 - CKEditor update
- MarkupEditor: Updated the CKEditor implementation to the current v38.
- MarkupEditor: Moved the [CKEditor build](https://github.com/Mistralys/appframework-ckeditor5) to its own project.
- MarkupEditor: Added examples in the interface references.
- Core: Removed the obsolete C# toolset project.

### v2.7.0 - CAS Update
- Session: Updated the CAS package to [v1.6.1](https://github.com/apereo/phpCAS/blob/master/docs/ChangeLog).
- Session: Added logging to the CAS authentication process.
- Application: Added `setStorageFolder()` to change the folder globally.
- Bootstrap: Added `setSessionClass()` to the bootstrap screen to switch session classes.
- RequestLog: Removed logging toggle when not authenticated.
- RequestLog: Added password hint when running in the test application.
- RequestLog: Added enabling or disabling the global developer mode in the settings.
- Driver: Added a global developer mode override.
- TestApplication: Added possibility to enable CAS sessions to test the CAS auth.
- Themes: Added maximum logo size in the logged out screen.
- DBHelper: Added `getRecordData()` to DB records to fetch the raw data set.
- Core: Added a first draft of a logo for the framework.
- Countries: Updated country flag icons package, now Composerized \o/.
- Documentation: Added a country flags example page in the interface references.

#### Test application changes
- Renamed `tests/application/config/ui.dist.php` to `test-ui.dist.php`.
- Added CAS-specific test constants to `test-ui.dist.php`.
- The session auth type can now be selected with `TESTS_SESSION_TYPE`.

### v2.6.3 - CAS Fix
- Session: Fixed the CAS package not being included in the Composer config.
- Session: Locked the CAS package at version 1.3.9 until the method signature changes are implemented.

### v2.6.2 - PHP7 bug fix
- UI: Fixed the `UIButton` trait causing a fatal error on PHP7, because the QuickForm
  buttons now also have `makeSubmit()`. Moving the method from the trait to the `UIButton`
  class fixes the issue temporarily.

### v2.6.1 - Minor changes
- Exceptions: The exception page now converts newlines in messages to `br` tags in HTML view.
- Readme: Fixed the documentation link.
- Version: Fixed the version number in `VERSION`.

### v2.6.0 - PHP8 compatibility update
- Core: Made some PHP8 compatibility changes; removed deprecated notices.
- Core: Fixed `PackageInfo::getComposerID()` failing when no `composer.json` is present.
- Core: Added utility methods in `PackageInfo`.
- TestApplication: Added the documentation link in the footer.
- Documentation: Added compatibility for the test application paths.
- Documentation: Added categories in the file dropdown.
- Documentation: Split the main `documentation.md` into separate files.
- Forms: Fixed the Switch element not updating its value correctly.
- Dependencies: Updated Markdown Viewer to minimum [v1.3.1](https://github.com/Mistralys/markdown-viewer/releases/tag/1.3.1).
- Dependencies: Updated QuickForm2 to minimum [v2.2.1](https://github.com/Mistralys/HTML_QuickForm2/releases/tag/2.2.1).
- Dependencies: Updated AppUtils to minimum [v2.4.1](https://github.com/Mistralys/application-utils/releases/tag/2.4.1).
- Dependencies: Removed the explicit Bootstrap dependency.

> NOTE: Fully PHP7 backwards compatible.

### v2.5.7
- Logger: Made the `logSF()` parameter default values more lenient.
- Countries: Added `setURLDispatcher()` to the navigator class.
- Updaters: Added the `DISPATCHER_NAME` constant to the boot class.
- Updaters: Added the `REQUEST_VAR_UPDATER_ID` constant.
- Updaters: Improved the URL generation to use the request class.
- Forms: Added `UI_Form::addTextarea()`.
- Forms: Added code regions to the `UI_Form` class.
- Forms: `UI_Form::addHTML()` now accepts stringable values.
- Application: Removed `setTimeLimit()` exception.

### v2.5.6 - Minor enhancements
- DataGrids: Added `configureFilterSettings()` in the `CollectionList` trait.
- Forms: Added `UI_Form::FORM_PREFIX` to easily access it globally.
- DBHelper: Added `requireParentRecord()` in the collection class.

### v2.5.5 - UI update
- UI: `addJavascriptXXX()` methods are now chainable.
- UI: Added `addJavascriptHeadComment()` method and variants thereof.

### v2.5.4 - Dependency update
- Dependencies: Updated the minimum version parser release to [v2.0.0](https://github.com/Mistralys/version-parser/releases/tag/2.0.0).
- VisualSelect: Fixed missing image selection ([#53](https://github.com/Mistralys/application-framework/issues/53)).

### v2.5.3 - Sidebar dev panel fix
- Sidebar: Fixed the `getButton()` method ignoring the `$name` parameter.

### v2.5.2 - Minor tweaks
- Core: Fixed the framework reporting the wrong version
- Core: Updated the version string in `VERSION`.

### v2.5.1 - Minor features
- UI: Added `addUnorderedList()` to the `UI_Help` class.
- UI: Fixed the size of headings in the page help.
- DataGrids: Fixed grid actions ignoring exceptions.

### v2.5.0 - Deprecation cleanup
- VisualSelect: Added `injectJavascript()` to be able to call it separately.
- UI: Added the `ClientResourceCollection` helper class to access an object's client resources list.
- UI: Added `ScriptInjectableInterface` for all classes that inject scripts and styles.
- Forms: Updated the QuickForm library to [v2.1.8](https://github.com/Mistralys/HTML_QuickForm2/releases/tag/2.1.8).

> NOTE: Any custom quick form elements in an application must be reviewed
regarding type changes. For example, the `$attributes` property is now
declared as `array`.

#### Removed deprecated methods:

- `Application::requireClass()`
- `Application::logModeEcho()`
- `Application::logModeFile()`
- `Application::logModeNone()`
- `Application::requireClassExists()`
- `Application::requireClassExtends()`
- `Application::requireInstanceOf()`

### v2.4.6 - Visual select update
- Forms: Added `addVisualSelect()` to `UI_Form` (previously only found in the formable).
- UI: Added visual select examples in the interface references.
- Visual Select: The search field in the visual select no longer submits the form on pressing enter.
- Visual Select: Improvements overall, options are now `VisualSelectOption` instances.
- Visual Select: Added support for switching between image sets.
- Visual Select: Added a jump navigation for groups in the grouped view.
- Visual Select: Added tests for most of the basic functionality.
- Visual Select: Converted clientside pseudo classes to actual JavaScript classes.
- Visual Select: Now used a template class for the HTML rendering.

### v2.4.5
- DBHelper: Fixed a bug in `addWhereColumnISNULL()` when `$null` is set to `true`.
- Admin Screens: Added hidden vars to the collection list trait via `getPersistVars()`.

### v2.4.4 - Local file uploads, QoL improvements
- Uploads: Added the `LocalFileUpload` class, which creates uploads from local file paths.
- Media: Added `createImageFromFile()` to add an image document from a local file path.
- AppFactory: Added the new centralized collection factory helper class `AppFactory`.
- AppFactory: Replaced factory method calls for all framework-internal calls.
- WhatsNew: Fixed the sorting of the languages not putting the DEV language at the end.
- PropertiesGrid: Merged property grids now use the `ifEmpty()` text when available.
- PropertiesGrid: Added `addMarkdown()` to add markdown-styled multiline text.
- PropertiesGrid: Added message styling methods usable without `getMessage()`.
- Interfaces: Added an interface and trait for objects that implement UI message styling.
- Forms: Fixed issue with setting min/max element values ([#47](https://github.com/Mistralys/application-framework/issues/47)).
- DeployCallback: Added HTTP status codes for success and error ([#52](https://github.com/Mistralys/application-framework/issues/52)).
- AppSettings: Fixed the filters not searching in key names ([#51](https://github.com/Mistralys/application-framework/issues/51)).

#### Deprecated methods:

The following methods have all been replaced by equivalent methods in the
new `AppFactory` class:

- `Application_Driver::createCountries()`
- `Application_Driver::createMaintenance()`
- `Application_Driver::createUsers()`
- `Application_Driver::createWhatsnew()`
- `Application_Driver::getApplicationSets()`
- `Application::createDeeplHelper()`
- `Application::createDeploymentRegistry()`
- `Application::createMedia()`
- `Application::createMessageLog()`
- `Application::createLookupItems()`
- `Application::createRatings()`
- `Application::createErrorLog()`
- `Application::createRequestLog()`
- `Application::getLogger()`
- `Application::getSets()`

### v2.4.3 - Filter Settings enhancements
- FilterSettings: Added `setSettingEnabled()` to turn individual settings on/off.
- FilterSettings: Added lazy loading of settings.
- FilterSettings: Added `setID()` to adjust the ID after instantiation.
- FilterSettings: Added logging.
- Formable: Added missing min and max arguments to `addRuleFloat()`.
- FilterCriteria: `hasCriteriaValues()` can now be used with multiple types.
- PHPStan: Fixed a number of wrongly documented nullable types.
- Sessions: Property `$rightPresets` now requires the `array` type.
- Icons: Fixed a clientside error.

### v2.4.2 - Bugfix
- CLIScreens: Fixed a PHP error in the overridden `log()` method.

### v2.4.1 - Connector URL fix
- Connectors: Removed the exception when using a URL with GET parameters.
- Connectors: Added inheriting GET parameters from the endpoint URL.
- Connectors: Fixed URL handling discrepancies.
- Connectors: Added `Connectors_Request::getBaseURL()`.

### v2.4.0 - Connectors Cache fix redux
- Formable: Added a workaround for [#48](https://github.com/Mistralys/application-framework/issues/48).
- CollectionSettings: Added extensible `_handleHiddenVars()`.
- CollectionSettings: Turn off automatic page title by returning an empty string in `resolveTitle()`.
- Icons: Added support for custom icons via `htdocs/themes/custom-icons.json` [#49](https://github.com/Mistralys/application-framework/issues/49).
- Icons: Added the new `GetIconsReference` AJAX method which returns a list of all icons.
- Icons: Simplified the class structure.
- Icons: The clientside reference sheet dialog now uses the AJAX method.
- UI: Page title `addXXX()` methods now accept empty or null values for easier method chaining.
- UI: Links within "muted" texts now also appear muted until hovered.
- Connectors: Fixed the caching issues [#50](https://github.com/Mistralys/application-framework/issues/50)
- Connectors: Easier error handling in the response via `getError()`.

#### Breaking changes

- `Connectors_Response::unserialize()` can now return `NULL`.
- Connector response error handling modified: Endpoint errors are now handled via
  the `ResponseEndpointError` class, so the return value of `getError()` must be
  checked to get the error details.

#### Deprecated methods

- `Connectors_Response::getEndpointError()`
- `Connectors_Response::getEndpointException()`
- `Connectors_Response::getErrorCode()`
- `Connectors_Response::getErrorData()`
- `Connectors_Response::getErrorDetails()`
- `Connectors_Response::getErrorMessage()`

These have all been replaced by the new `Connectors_Response::getError()` method, which
contains all the necessary information.

#### Recommended updates

Formable setting managers: Review the [notes for issue #48](https://github.com/Mistralys/application-framework/issues/48).
Formable settings that have different setting names than the record's
data columns are now fully supported.


### v2.3.8 - Connectors Cache Fix
- Connectors: Fixed request using the cache even if cache is disabled.

### v2.3.7 - Translations
- Localization: Added missing UI translations.
- Localization: DE: Changed "Änderungslog" to "Änderungsprotokoll".
- Dialogs: Added `AddClass()` to all dialog types.
- Icons: Added the `snowflake` icon.
- Seasonals: Improved winter season dialog with custom theming.

### v2.3.6 - Seasonals and layout tweaks
- Seasonals: Added a winter greeting in the meta navigation.
- UI: Fixed the maximized width setting causing a horizontal scroll bar.
- UI: Reduced the spacing between navigation items to make it more compact.
- UI: Added the deployment date in the footer, when available.

### v2.3.5 - Bugfixes
- Connectors: Fixed empty response data for non-method-based requests.
- Application: `Application::setTimeLimit()` no longer throws an exception during unit tests.
- Notepad: Fixed notes overlapping the notepad are in some cases (#34).

### v2.3.4 - Connector tweaks and QoL changes
- Connectors: Added `getErrorCodes()` and `hasErrorCode()` to the response.
- Connectors: Added serialization to the request.
- Connectors: The response serialization now includes the request information.
- Loggable: Added the utility method `getIdentifierFromSelf()` to use the class type name.
- Bootstrap: Screens now have logging available.
- Bootstrap: Added the `DeploymentRegistry` manager to save deployment information.
- Bootstrap: Added the `deployment-callback.php` dispatcher.
- What's new: Fixed the dialog not showing any changes.
- What's new: Added possibility to define image size.
- What's new: Added syntax reference in the editor for the Markdown text field.
- What's new: Added a list of available images in the editor sidebar.
- Admin: Added the deployments history screen in the developer management.
- Test UI: Added the translation screen, which can also be used to do the translations.
- Test UI: Added the deployment history screen.
- QuickStart: Removed the BETA label.
- DataGrids: Added auto-adding of hidden screen parameters (#36).
- UI: Buttons: Added a parameter to `makeLink()` to omit the `btn-` classes on the tag.
- Templates: Added `startOutput()` and `endOutput()` helpers to capture content.
- Themes: Updated the footer to a class based template with a system to add column items.
- AppRatings: Fixed an SQL error when the screen has no path.

### v2.3.3 - Connector tweaks
- Connectors: Response: Added `requireData()`.
- Connectors: Response: Fixed some error codes never being triggered.
- Connectors: Response: Fixed the data set not bein initialized.

### v2.3.2 - Connectors bugfix
- Connectors: Response: Fixed a PHP error in some response configurations.

### v2.3.1 - Connectors update
- Connectors: Improved response error information handling.
- Connectors: Response: Added the `exception` response parameter (accepts a serialized `ThrowableInfo`).
- Connectors: Response: Added `getEndpointException()` to fetch the remote exception.
- Connectors: Response: Added `getEndpointError()` to fetch the remote error details.
- Connectors: Response: Added constants for relevant parameters and the JSON placeholders.
- Connectors: Exception: Added `getConnectorResponse()`, as this was missing.

#### Response error handling changes

Previously, a response with the `error` state would hide the remote error's
details. The `getErrorXXX()` methods stay unchanged, still returning the
response's error code and details. The new `getEndpointError` method allows
accessing the remote error details. Additionally, the `getEndpointException()`
gives access to any exception information sent along in the request
(independently of state).

### v2.3.0 - DBHelper update release (breaking)
- DBHelper: Added the collection's `AfterCreateRecord` event.
- DBHelper: Statement builder instances can now be passed to query methods.
- DBHelper: Added the DB filter criteria `isJoinRegistered()` method.
- UI: Added conditional interface to `UI_StringBuilder`.
- UI: The button interface now includes `disable()` and `isDisabled()`.
- Admin: Added the trait `RequestCountryTrait` for accessing a country instance.
- StringBuilder: Now implements the Conditional interface.
- DataGrid: Added `addHiddenScreenVars()` to add the current screen's params.

#### Breaking changes
- DBHelper: The class `DBHelper_BaseCollection_Event_BeforeCreateRecord` has been
  namespaced to `DBHelper\BaseCollection\Event\BeforeCreateRecordRecordEvent`.
- Admin: The class `Application_Admin_Area_Mode_Users_List` has been namespaced
  to `Application\Admin\Area\Mode\Users\UsersListSubmode`.

### v2.2.0 - UI update release (breaking)
- UI: Revamped the main navigation generation.
- UI: The main navigation now supports class-based custom generation.
- UI: Restyled the main navigation and footer.
- UI: Added the quick navigation for often-used sub screens of areas.
- UI: The `Iconizable::setIcon()` method now supports setting a `NULL` value.
- UI: Added `UI::tooltip()` to create a `TooltipInfo` instance.
- UI: Added the interface `TooltipableInterface` and matching trait.
- UI: Some navigation items now support tooltips.
- Users: Implemented the users list in the devel users management screen.
- DBCollections: Filter criteria and settings classes now support namespaces.
- Screens: Screen classes now support using namespaces (BETA).
- Screens: URL names may now differ from the file name (BETA).
- Logging: Added support for log message categories.
- Moved the Deepl methods to the new `DeeplHelper` class.
- Code Quality: PHPStan analysis now clean at level 5.
- Unit Tests: Added the method `string2html()` in the `ApplicationTestCase`.
- Wizards: Fixed wizards trying to load subscreens, and failing silently.
- Unit Tests: The test application has been fleshed out further.
- Unit Tests: Fixed the infinite loop when accessing the test wizard via the test application UI.

#### Deprecated methods and classes

- `Application_Driver::getCookieNamespace()` has been removed.
- `Application_Exception_UnexpectedInstanceType` - use `UnexpectedInstanceException` instead.
- `ensureType()` - use `AppUtils\ClassHelper` instead.
- `Application::requireClass()` - use `AppUtils\ClassHelper` instead.
- `Application::requireClassExists()` - use `AppUtils\ClassHelper` instead.
- `Application::requireClassExtends()` - use `AppUtils\ClassHelper` instead.
- `Application::requireInstanceOf()` - use `AppUtils\ClassHelper` instead.

#### Breaking changes

- `Application_Driver->getMaintenance()` renamed to static `Application_Driver::createMaintenance()`.
- Moved the Deepl methods to the new `DeeplHelper` class.

### v2.1.8 - Dependencies update
- Dependencies: Updated AppUtils to latest [stable release v2.2.6](https://github.com/Mistralys/application-utils/releases/tag/2.2.6).

### v2.1.7 - Maintenance release
- Framework: Added `AppFramework::getName()`.

### v2.1.6 - Minor feature release
- Framework: Added the utility class `AppFramework`.

### v2.1.5 - Bugfix release
- WhatsNew: Fixed the sorting of the languages, to have DEV at the end.
- BasicEnum: Added `requireValidValue()`.
- CriticalityEnum: Added namespace.
- UI: Badge dropdown: Fixed `makeLabel()` resetting the label of the badge.
- UI: Badge dropdown: `makeLabel()` is now chainable.
- UI: Badge dropdown: `setLabel()` now supports renderable values.

### v2.1.4 - Minor feature release
- Application: Added PHPStan-friendly `requireInstanceOf()`.
- Application: Added `requireClassExtends()`.
- Countries: Added the `$includeInvariant` parameter to `getSupportedISOs()`.
- UI: Icons: Added the "commandDeck" icon.
- DBHelper: Added the `val()` method in the values container.
- DataGrid: Added `setSortingDateTime()` to columns.

### v2.1.3 - Minor feature release
- Formable: Added `getFilters()` to the `Application_Formable_Selector` class to customize them.
- Countries: Added `useCustomCollection()` to the countries formable selector.
- Navigation: Fixed active items not being recognized in navigations.
- Themes: Added the possibility to display custom messages in the "Logged out" template #45.

### v2.1.2 - Bugfix release
- Forms: Fixed an issue causing hidden form element values to not be updated when submitted.

### v2.1.1 - Bugfix release
- DBHelper: Fixed a bug when saving a record that only has custom keys modified.
- DeepL: Updated `mistralys/deepl-xml-translator` to v2 branch.
- DeepL: Preparing for fix in the `scn/deepl-api-connector` package.

### v2.1.0 - What's new editor release
- WhatsNew: Added a UI to edit the changelog in the developer tools.
- WhatsNew: Added render classes for plain text and XML.
- WhatsNew: Added possibility to add new versions.
- Forms: Fixed link buttons in the footer not having any right margin.
- DBHelper: Fixed a DB error when saving single modified keys.
- DBHelper: Added the record method `saveChained()` for chaining support.
- Countries: Added the utility class `CountriesCollection`.
- Countries: Added the `getCollection()` method to fetch them as a collection.
- Forms: Added the `ExpandableSelect` custom element.
- DBHelper: Added get methods to the statement builder.
- Functions: Added namespace support to `getClassTypeName()`.
- UnitTests: Reaching a fully functional state of the test application.
- Driver: Added `getAdminURLChangelog()`.

### v2.0.1 - Bugfix release
- Forms: Fixed the loading of the UIButton element.
- Forms: Fixed submit buttons now being shown in the form footer.
- Forms: Fixed submit buttons not having any label.
- Documentation: Added button examples in the interface references.
- UI: Fixed buttons mixing submit and link attributes.
- UnitTests: Added button tests.
- UI: Added `isSubmittable()` to the `ClientConfirmable` interface.
- UI: Sections: Fixed the collapse check when containing forms.
- UI: Sections: The header text now supports renderables.

### v2.0.0 - PHP7.4 Update (breaking)
- Requirements: Now using PHP v7.4 as minimum requirement.
- UI: Fixed the dropdown menu's `setIcon()` method not doing anything (#25).
- UI: Fixed menu items' icons disappearing when they are active (#25).
- UI: External links now have the same color as other items in the dropdown menu
- UI: Fixed dropdown menu item not rendering correctly when set to "active"
- Admin: Added the static `Application_Admin_Skeleton::getPageParamNames()`.
- Formable: Marked `addElementHeader()` as deprecated.
- Request: Added `Application_Request::resolveParams()`.
- Application: Added the `DriverSettings` class to easily access global settings.
- Application: Added `Application_Driver::createSettings()`.
- Application: Added `requireClassExists()` for a useful exception.
- Dependencies: Bugfix for the documentation viewer (See [v1.1.0 release](https://github.com/Mistralys/markdown-viewer/releases/tag/1.1.0)).
- Themes: Added the `VariableMissingException` for variables missing in templates.
- Themes: Added an interface for templates, `PageTemplateInterface`.

#### Breaking changes

- Minimum requirement is now PHP 7.4.
- Wizard methods have been renamed:
    - `setSetting()` > `setWizardSetting()`
    - `getSetting()` > `getWizardSetting()`

#### Deprecated methods

- `Application_Driver::setSetting()`
- `Application_Driver::setBoolSetting()`
- `Application_Driver::setSettingExpiry()`
- `Application_Driver::getSetting()`
- `Application_Driver::getBoolSetting()`
- `Application_Driver::deleteSetting()`

These methods will be removed in a future release. Use the
`Application_Driver::createSettings()` API instead.
