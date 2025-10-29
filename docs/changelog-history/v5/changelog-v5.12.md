## v5.12.11 - UI Stringable fix
- UI: Fixed `toString()` recognizing `UI_Renderable_Interface` instances as not stringable.
- UI: `toString()` now returns `true` and `false` as strings for boolean values.

## v5.12.10 - API version selection
- API: Added `selectVersion()` to manually select the version to work with.

## v5.12.9 - Locale API improvements
- API: Added  `selectLocale()` to the locale API trait to select a locale manually.
- API: When running a method, it automatically sets `$_REQUEST['method']` to its own name.

## v5.12.8 - API Versioning
- API: Added trait and classes to handle API versioning with transformer classes.
- API: Added `KeyPath` and `KeyReplacement` classes to reference key paths and replacements.
- API: `getRequestTime()` can now return null when an exception occurs and no time is available.
- API: Added API changelog as a Markdown string with `getChangelog()`.
- API: Added the Changelog section to the documentation.
- API: Method indexing now also indexes API version classes in the class cache.
- API: Parameter sets now auto-register params in the method when not registered yet.
- API: Added possibility to auto-link API methods in descriptions with the syntax `#MethodName`.
- API: Moved response key docs to a dedicated section.
- API: Implementing JSON response key docs is now mandatory.
- API: Added API utility class `KeyDescription`.
- API: Tests: Added the assertion method `assertMethodCallIsSuccessful()`.
- MarkdownRenderer: Added `renderInline()` to render text without a paragraph tag.
- Countries: Fixed country API params not having been registered.

## v5.12.7 - Country API
- Countries: Added country ID / country ISO OR rule to the API parameter rules.
- API: Parameter sets now do a failsafe check if any parameters are defined.

## v5.12.7 - Fixes
- Locales: Fixed trait method not being considered callable.
- Languages: Fixed trait method not being considered callable.

## v5.12.6 - Small improvements and fixes
- API: Fixed missing label argument for some parameter rules.
- Countries: Updated currency interface return type to make methods visible.
- Dependencies: Bumped up AppLocalize to [v2.1.2](https://github.com/Mistralys/application-localization/releases/tag/2.1.2).

## v5.12.5 - Minor improvements
- Icons: Added the `getPrefix()` method to the icon class.

## v5.12.4 - API improvements
- API: Added a trait and interface for API method tests.
- API: Methods can now specify a custom response class to use for success responses.
- API: Added an application locale selection API trait.
- API: Now listing all validation messages in the `validationMessages` response property.
- API: Fixed enum parameters attempting to validate empty values.
- API: Made the alias validation argument for capital letters mandatory to make it more visible.

## v5.12.3 - API fixes and improvements
- Bootstrap: Added an exception when trying to boot a second time with a different class.
- API: Fixed the OR rule invalidating parameters prematurely when using multiple OR groups.
- API: Using `processReturn()` now returns distinct response and error response objects.
- API: Now passing the parameter instance to validation classes for better error messages.
- API: Added `getValidationResults()` to get results when processing in return mode.
- API: Added `setRequestBody()` to simulate the body in test scenarios.
- Composer: Added the `build` Composer command.

## v5.12.1 - Page help fix
- UI: Fixed the page help being expanded all the time, and buttons not being responsive.
- API: Can now select boolean values in a select element when trying out an API.

## v5.12.0 - API Update (Breaking-L)
- API: Complete overhaul of the API handling.
- API: Added an API method index to look up method names and classes.
- API: Registered the method index in the main cache control.
- API: Added an API method collection to access all methods.
- API: Added `APIMethodInterface`.
- API: Overhauled the base API method class, streamlined the request and response processes.
- API: Added traits for the request and response types, e.g. `JSONResponseTrait`.
- API: Implemented an exception-based flow break to access response data instead of sending responses.
- API: Renamed and namespaced the API bootstrapper.
- API: The method documentation URL is now included in the response API info.
- API: The request and response mime types are now included in the response API info.
- Connectors: Added a connector for framework APIs.
- Themes: The clean frame template now uses the page title if set.
- Validation: Added the `ValidationResults` utility class.
- Validation: Added the `ValidationResultInterface` for objects that contain validation results.
- Validation: Added the `ValidationLoggableTrait` for classes that combine logging and validation.
- Tests: Added the `OperationResultTestTrait` for useful assertions.
- Dependencies: Bumped up AppUtils to [v3.1.10](https://github.com/Mistralys/application-utils/releases/tag/3.1.10) for a bugfix.

### Breaking changes

- Renamed all API-related classes.
- New API method structure and flow.
- Renamed and namespaced the API bootstrapper.
