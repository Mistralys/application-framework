## v5.8.3 - Fixes and PHP8.4
- Session: Fixed the CLI session handling to avoid errors when running in CLI mode.
- PHP8.4: Fixed deprecation warnings for implicit nullable method arguments.
- Formable: Added `onClientFormRendered()` to react to the rendered HTML markup.
- Formable: Fixed JS head statements not being collected due to wrong call order.
- Sections: Fixed the collapse buttons not appearing in client forms.
- Events: Added a trait for HTML processing events.
- UI: Added `onPageRendered()` to add event listeners.
- UI: Added `selectDefaultInstance()`.
- UI: `selectInstance()` now accepts freeform instance names.
- UI: Removed the obsolete `selectDummyInstance()` method.
- Composer: Set PHP7.4 as the target platform, but allow installing on PHP8.
- Dependencies: Updated HTML QuickForm to [v2.3.6](https://github.com/Mistralys/HTML_QuickForm2/releases/tag/2.3.6).

### PHP8.4 update progress

Notices have mostly been fixed. One remaining issue is the PhpCAS package,
which has no PHP8.4 support yet. The session fix in this version makes it
at least possible to run the tests on PHP8.4.

## v5.8.2 - String builder and CSS classes
- UI: Added the `CSSClasses` enum class as a reference for available CSS class names.
- UI: Added the `right-developer` class.
- StringBuilder: Added `developer()` for dev-only text.
- StringBuilder: Using class constants where applicable.

## v5.8.1 - Small tweaks
- TimeTracker: Made times clickable to select the row.
- ListBuilder: `collectEntry()` can now return an entry instance.

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
  