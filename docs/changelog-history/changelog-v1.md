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
- Environments: Added `Application\Application_Environments::getDetected()`.
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
