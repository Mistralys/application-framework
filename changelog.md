### v2.4.3 - Filter Settings enhancements
- FilterSettings: Added `setSettingEnabled()` to turn individual settings on/off.
- FilterSettings: Added lazy loading of settings.
- FilterSettings: Added `setID()` to adjust the ID after instantiation.
- FilterSettings: Added logging.
- Formable: Added missing min and max arguments to `addRuleFloat()`.

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

### v1.23.2 - Bugfix release

- Fixed the error logs list missing hidden request variables.

### v1.23.1 - Maintenance release
- Sessions: Added error checks in the session initialization.

### v1.23.0 - Minor feature release (breaking)
- Forms: Improved the form TOC headers list styling.
- Forms: Added `addStatusIcon()` to the headers.
- Forms: Headers can now configure the final content section instances.
- DataGrid: Added `renderCheckboxLabel()` in entries.
- DataGrid: Added renderable support to `setEmptyMessage()`.
- DataGrid: Added `setDispatcher()` to change the grid form action attribute.
- Countries: Navigator: Added the `REQUEST_PARAM_COUNTRY_ID` constant.
- UI: Sections: Added status elements support.
- UI: Added interface and trait for elements that support status indicators.
- Logging: Added the `write()` method to the logger to write the current log to disk.
- Request: Added `getRequestID()`.
- Logging: Added request logging with the new `Application_RequestLog` class.
- Logging: Added `Application::createRequestLog()`.
- Logging: Added `APP_WRITE_LOG` constant to force writing logs to disk.
- Sessions: Added `getID()` to the session class interface.
- Events: Added a constant for the `SystemShutdown` event.
- Application: Added static `getTimeStarted()` and `getTimePassed()` methods.
- Application: Added the `APP_REQUEST_LOG_PASSWORD` config parameter.
- Bootstrap: Added `getBootClass()` to identify the boot screen used in the request.
- Driver: Added `setBoolSetting()`.
- FilterSettings: Added `addElementText()` and typed getSetting methods.
- DBHelper: Added query counts without enabling the full query tracking.

#### Breaking changes

The `APP_REQUEST_LOG_PASSWORD` setting must be added in the configuration. We suggest
adding in the `app-config.php` file, since it does not have to change regularly.

### v1.22.2 - Maintenance release
- Forms: Improved the form TOC headers list styling.
- Forms: Added `addStatusIcon()` to the headers.
- Forms: Headers can now configure the final content section instances.
- DataGrid: Added `renderCheckboxLabel()` in entries.
- DataGrid: Added renderable support to `setEmptyMessage()`.
- Countries: Navigator: Added the `REQUEST_PARAM_COUNTRY_ID` constant.
- UI: Sections: Added status elements support.
- UI: Added interface and trait for elements that support status indicators.

### v1.22.1 - Maintenance release
- Countries: Navigator: Added `setURLParamByRequest()` and admin screen flavors.
- Connectors: Fixed unique request IDs making unnecessary DB requests to generate the IDs.
- Forms: Improved styles for file upload elements.

### v1.22.0 - Maintenance release (breaking) 
- Icons: Added `on()` and `off()`.
- Icons: Improved internal color style handling.
- Icons: Added `makeRegular()` to restore the default color style.
- Icons: Added icon constants.
- Icons: Made the warning icon color more distinct from the dangerous color.
- Buttons: Improved size handling.
- Buttons: Added `makeActive()` to toggle the pressed state.
- Forms: Switch element: Replaced the custom HTML rendering with fitting UI classes.
- Forms: Switch element: Added `makeLarge()`, `makeMini()`, `makeEnabledDisabled()`, etc.
- UI:: Added `prettyBool()` method for the pretty boolean helper.
- Continued adding type hints wherever applicable.
- Formables: Using getUI() now works without initializing the formable.
- Connectors: Added `requireActiveResponse()`.

#### Breaking changes

Any classes extending the `Application_Formable` class must now implement
the `render()` method. This does not affect admin screens, which have been
updated already.

### v1.21.2 - Maintenance release
- Events: Added `Application::addRedirectListener()`.
- Events: Fixed PHP8 support by removing keys from associative argument arrays.

### v1.21.1 - Maintenance release
- Environments: `Application::isDevelEnvironment()` now uses the environment classes.
- Environments: Added `Application_Environments::getDetected()`.
- DBHelper: Fixed detection of complex queries in custom columns.
- Wizards: Fixed a bug when trying to use step data in the cancel URL.
- FilterSettings: Fixed the UI bug that made the add filter button not clickable.
- Sessions: Fixed auth type `None` throwing exceptions when used.
- UI: DataGrid: Added support for user-persisted hiding of columns.
- TestSuites: Added functional application UI under `tests/application`.

### v1.21.0 - Code quality and minor feature release (breaking)
- FilterCriteria: Improved the custom columns handling further.
- FilterCriteria: Custom columns now automate even more tasks.
- FilterCriteria: Custom columns detected and auto-enabled more reliably.
- FilterCriteria: Added protected `handleCriteriaChanged()` to handle the event.
- Traits: Added the Instanceable trait and matching interface.
- UnitTests: `Application::isUnitTestingRunning()` now also true in the framework's test suites.
- Feedback: Added `addBug()` and `addImprovement()` methods.
- User: Fixed `createDummyUser()` returning the system user.
- BigSelection: Added `prependXXX()` methods to prepend items instead of appending them.
- BigSelection: Added `getReferenceID()` and `getReferenceID()` to items to identify them.
- BigSelection: Added `getDescription()` and `getLabel()` to regular items.
- CodeQuality: Type hint extravaganza, part I: admin screen interfaces typified.
- TypeHinter: Added the automated type hint generator class.
- TypeHinter: Added the update class `TypeHinter_UpdateV1_21` to help with the migration.
- EventHandling: Added the offline event handling system.
- EventHandling: Added `Application_EventHandler::createOfflineEvents()`.
- Bootstrap: Added `getAutoLoader()` to access the composer autoloader instance.
- Bootstrap: Added `getVendorFolder()` to access the path to the vendor folder.
- DataTable: Added `setMaxKeyNameLength()` to handle long key names.
- DataTable: Added `setNameColumnName()` and `setValueColumnName()` to customize the names.
- Countries: Navigator: Added optional active country saving with `enableCountryStorage()`.
- AjaxMethod: Added `requireCountry()` to fetch a country from the request.
- Countries: Added `createNewCountry()`.
- Countries: Added failsafe for adding unhandled ISO codes like `gb`.

#### Breaking changes

A big number of type hints have been added, notably in the admin
screens handling. Especially affected is the `_handleAction()` method,
which now has a fixed boolean return value. It already allowed boolean
values, but would accept no return value as true. Going forward, this
must be returned explicitly.

To help with the type hint migration, use the class `TypeHinter_UpdateV1_21`,
which is pre-configured for most of the type hint changes and will adjust
PHP files automatically. It is recommended to commit all changes prior to
running this script. Simply call it in the `index.php` file of the application
to run it.

### v1.20.1 - Bugfix release (breaking)
- DBHelper: Added the class `DBHelper_CaseStatement` to create `CASE` statements.
- DBHelper: Fixed order by and group by statements not using the custom column's value statement.
- DBHelper: Fixed order by being overwritten by other custom columns.
- FilterCriteria: Removed the no constructor limitation.
- FilterCriteria: Now automatically passing initial constructor arguments in `createPristine()`.

#### Breaking changes

Filter criteria classes with custom constructors **must** always
call the parent constructor.

### v1.20.0 - Feature release (breaking)
- Wizards: Added a traits for selecting a country.
- Wizards: Added a traits for a generic summary/confirmation step.
- Wizards: Added registering completed steps to display the steps summary.
- UI: BigSelection: Top-aligned labels when description is multiline.
- UI: BigSelection: No margin on last paragraph in descriptions.
- UI: Icons: Added the `ContentTypes` icon.
- UI: Icons: Now implementing the `UI_Renderable_Interface`.
- UI: Updated to use FontAwesome `v5.15.4`.
- UI: Forms: Fixed block-styled multiselect inputs not shown as block elements.
- UI: Styles: Fixed external links right angle quote wrapping to the next line.
- Application: Added static `isUnitTestingRunning()` method.
- DBHelper: Added the statement builder's value container.
- DBHelper: Added the global `statementValues()` function.
- DBHelper: Updated the extended filter criteria to support the use of statement builders.
- DBHelper: Custom columns are now enabled automatically if used in the query.
- DBHelper: Custom columns now support selecting localized country labels by country ID.
- DBHelper: Custom columns now support selecting usernames by user ID.
- DBHelper: Added registering `JOIN` statements, so they are only added if needed.
- DBHelper: Join statements can now require other joins.
- Feedback: Added the missing `AddFeedback` AJAX method.
- Feedback: Added some missing collection classes.
- DataGrids: Moved the sorting icons out of the header cells for better readability.
- DataGrids: Aligned header cells to the top.

#### Breaking changes

The database filter criteria `addJoin()` method now returns the join instance
instead of the filter criteria instance. Please verify if any of these calls
have been used for chaining in your application.

---
Older [changelog](https://github.com/Mistralys/application-framework/releases) 
entries can be found on the homepage.
