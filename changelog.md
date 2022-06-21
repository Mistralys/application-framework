### v2.2.0 - UI update release
- UI: Revamped the main navigation generation.
- UI: The main navigation now supports class-based custom generation.
- UI: Restyled the main navigation and footer.
- UI: Added the quick navigation for often-used sub screens of areas.
- UI: The `Iconizable::setIcon()` method now supports setting a `NULL` value.
- UI: Added `UI::tooltip()` to create a `TooltipInfo` instance.
- UI: Added the interface `TooltipableInterface` and matching trait.
- UI: Some navigation items now support tooltips.
- Users: Implemented the users list in the devel users management screen.
- Class loading: Added the static utility class `ClassFinder`.
- Exceptions: Added `UnexpectedInstanceException`.
- DBCollections: Filter criteria and settings classes now support namespaces.
- Screens: Screen classes now support using namespaces (BETA).
- Screens: URL names may now differ from the file name (BETA).
- Logging: Added support for log message categories.
- Moved the Deepl methods to the new `DeeplHelper` class.
- Unit Tests: The test application has been fleshed out further.
- Code Quality: PHPStan analysis now clean at level 5. 

#### Deprecated methods and classes

- `Application_Exception_UnexpectedInstanceType` - use `UnexpectedInstanceException` instead.
- `ensureType()` - use `ClassFinder` instead.
- `Application::requireClass()` - use `ClassFinder` instead.
- `Application::requireClassExists()` - use `ClassFinder` instead.
- `Application::requireClassExtends()` - use `ClassFinder` instead.
- `Application::requireInstanceOf()` - use `ClassFinder` instead.
- `Application_Driver::getCookieNamespace()` has been removed.

#### Breaking changes

- `Application_Driver->getMaintenance()` renamed to static `Application_Driver::createMaintenance()`.
- `Application_Driver::`

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

### v2.0.0 - PHP7.4 Update
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

### v1.23.0 - Minor feature release
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

**Breaking changes:**

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

### v1.22.0 - Maintenance release 
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

**Breaking changes**

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

### v1.21.0 - Code quality and minor feature release
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

**Breaking changes**

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

### v1.20.1 - Bugfix release
- DBHelper: Added the class `DBHelper_CaseStatement` to create `CASE` statements.
- DBHelper: Fixed order by and group by statements not using the custom column's value statement.
- DBHelper: Fixed order by being overwritten by other custom columns.
- FilterCriteria: Removed the no constructor limitation.
- FilterCriteria: Now automatically passing initial constructor arguments in `createPristine()`.

**Breaking changes**

Filter criteria classes with custom constructors **must** always
call the parent constructor.

### v1.20.0 - Feature release
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
- DBHelper: Custom columns now support selecting user names by user ID.
- DBHelper: Added registering `JOIN` statements, so they are only added if needed.
- DBHelper: Join statements can now require other joins.
- Feedback: Added the missing `AddFeedback` AJAX method.
- Feedback: Added some of the missing collection classes.
- DataGrids: Moved the sorting icons out of the header cells for better readability.
- DataGrids: Aligned header cells to the top.

**Breaking changes**

The database filter criteria `addJoin()` method now returns the join instance
instead of the filter criteria instance. Please verify if any of these calls
have been used for chaining in your application.

---
Older [changelog](https://github.com/Mistralys/application-framework/releases) 
entries can be found on the homepage.
