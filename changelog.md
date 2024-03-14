## v4.0.0 - Tagging management & Revisionables update (DB-update L)
- Tags: Added the tagging management.
- Media: Documents are now taggable.
- Tests: The Test DB collection is now taggable.
- DBHelper: Added `getRelationsForField()` to fetch all field relations.
- JS: Added the `Logger` utility class.
- ButtonGroup: Added `addButtons()`.
- TreeRenderer: Added the `TreeRenderer` UI helper class.
- UI: Added `createTreeRenderer()`.
- Formables: Unified form element creation methods to use `UI_Form` methods.
- Formables: Added the `$id` parameter to `addHiddenVar()`.
- Forms: Moved some element creation methods to `UI_Form`.
- Forms: Code modernization and quality improvements.
- Forms: Modernized the `UI_Form` class, now with strict typing.
- Formables: Added some interface methods.
- RevisionCopy: `getParts()` can now return callables.
- FilterSettings: Settings are now class-based.
- FilterSettings: Preferred way to inject is via the setting's `setInjectCallback()`.
- Revisionables: Added a test revisionable collection to the test application.
- Revisionables: Added first unit tests.
- Revisionables: Modernized classes and strict typing.
- Revisionables: Automated the saving of custom revision table keys.
- Revisionables: Added an interface for the revisionable with state.
- Revisionables: Added a trait to implement the typical state setup.
- Dependencies: Updated AppUtils-Core to [v1.1.2](https://github.com/Mistralys/application-utils-core/releases/tag/1.1.2).

### Database changes (L)

The SQL update file must be imported: 

``` 
docs/sql/2024-02-15-revisionables-tagging.sql
```  

> Note: An existing application can use this framework release without
> the database update, as long as the tagging admin area is not enabled 
> in the application driver.

### Breaking changes (M)
- Revisionables: Added abstract `initStorageParts()` to formalize the saving of parts.
- Revisionables: Renamed all `revdataXXX`-methods to make it easier to understand.
- Revisionables: Renamed and namespaced interfaces.
- Renderables: Added strict `string` return type for `_render()` methods.

### Deprecations

- FilterSettings: Deprecated `addAutoConfigure()`, replaced by `SettingDef::setConfigureCallback()`.
- FilterSettings: Deprecated `getArraySetting()`, replaced by `getSettingArray()`.

## v3.1.7 - Navigation update
- Navigation: Fixed the search being executed for empty search terms.
- Navigation: Fixed the search not being executed in some cases.
- Navigation: Added `setTemplateID()` to freely select the template.
- Navigation: Fixed the spacing before the split button caret.
- Navigation: Nav ID and template ID are now separate.
- Navigation: It is now possible to reuse the subnav template for other navs.
- Interface Refs: Improved the subnavigation example.
- Interface Refs: Added tabs example.
- Tabs: Fixed active tabs having a bottom border.
- Tabs: Added `setURLTarget()`.

## v3.1.6 - Filter settings update
- FilterSettings: Fixed setting values comparison for the active filters check.

## v3.1.5 - Lookup items improvements
- LookupItems: Improved the parsing of search terms.
- LookupItems: Added some search hints in the dialog.
- LookupItems: An error message is now displayed with a retry button if the search fails.
- AJAX: Fixed a property check syntax that could cause a JavaScript error.

## v3.1.4 - Maintenance improvements
- Maintenance: Fixed logo shown too big if the source image is larger.
- Maintenance: Message redirects are now functional.

## v3.1.3 - UI system update (DB-update XS)
- DBHelper: Added the overridable `getParentPrimaryName()` to collections.
- DBHelper: Added the static `clearCollections()` method.
- Data Grids: Criticality actions now use regular menu item classes.
- UI: Continued migrating colors to the new color stylesheets.
- UI: Added the `Capturable` trait and interface.
- UI: Dropdowns: Added `makeSuccess()` and `makeWarning()`.
- UI: Added the CSS generator tool to render CSS files from templates using CSS Crush.
- UI: Added the CSS generator screen in the developer tools.
- UI: Added the `PageRendered` event that allows modifying the rendered HTML code.
- UI: Messages: Added `makeWarningXL()`.
- BigSelection: `makeHeightLimited()` now accepts a string parameter for the height.
- BigSelection: Fixed the height limit not being applied.
- BigSelection: Converted the template to a class-based template.
- BigSelection: Items can now be made active with `makeActive()`.
- Sections: Added a helper class for the collapse controls.
- Sections: Added `makeDangerous()` to formalize the existing danger-styled section.
- Sections: Added the  `SectionRegistry` static helper class.
- Sections: Fixed subsections not being visually different from regular sections.
- Sections: Tweaked the font sizes and paddings of UI elements.
- Sections: Removed custom tabs implementation (prefer the actual tabs helper).
- Sections: Removed `disablePadding()`, as sections have no padding by default.
- Sections: Deprecated `makeLightBackground()`, replaced with `makeBodySolidFill()`.
- Sections: Fixed clientside sections collapse icons not being toggled.
- Forms: Now using the new section collapse controls.
- Icons: Added the `css` icon.
- StepsNavigator: Strict typing and modernized code.
- StepsNavigator: Upgraded CSS to use the color variables.
- StepsNavigator: Fixed non-numbered steps not having rounded corners.
- StepsNavigator: Added `makeEnabled()` to items keep in line with common method naming.
- StepsNavigator: Added `makeActive()` to items as alternative to `selectStep()`.
- Interface Refs: Added examples of menus.
- Interface Refs: Added example of data grid actions.
- Interface Refs: Added more section examples.
- Interface Refs: Added a form table of contents example.
- Interface Refs: Added sidebar examples.
- Uploads: Non-framework exceptions are now logged.
- Sidebar: Added `addFormableTOC()`.
- Sidebar: Added possibility to customize the element ID.
- Sidebar: Added `setID()` and `getID()`.
- SQL: Fixed the `pristine.sql` file not being importable without errors.

### Database changes

The SQL update file `docs/sql/2024-01-10-countries.sql` must be imported
to fix the index of the `iso` column in the `countries` table. This is
not a critical change, but recommended to avoid duplicate countries.

## v3.1.2 - Config loading update
- Environments: Added support for `app.php` + `environments.php` config files.
- Interface Refs: Added more examples.
- UI: Added the `Capturable` trait and interface.
- UI: Fixed an endless loop when using dropdown submenus.
- UI: Fixed the `style` attribute being ignored in dropdown menus.
- UI: Continued consolidating colors in `ui-colors.css`.
- UI: Added `makeWarning()` and `makeSuccess()` to dropdown link entries.
- Icons: Added the `css` icon.
- DataGrids: Added `addLIClass()` in grid actions.
- DataGrids: Actions now use the regular menu CSS classes.

## v3.1.1 - Interface Reference enhancements
- Interface Refs: Added more UI element examples.
- Interface Refs: Built a support class structure for categories and examples.
- Interface Refs: Improved the rendering of the reference pages.
- Interface Refs: Added example descriptions and settings.
- Interface Refs: Examples are now discovered automatically from the example folder.
- Interface Refs: Converted all examples to the new structure.
- Interface Refs: Added example navigation.
- Interface Refs: The active example sidebar section now automatically expands.
- Forms: Added `addSection()` to formables as a more logical variant of `addElementHeaderII()`.
- Forms: Added the `create()` factory method to the generic formable class.
- Forms: Added `addTab()` to formables.
- ScreenSkeleton: Added `createTemplate()`.
- Templates: Template ID parameters now accept template class names.
- Templates: Added support for namespaced template classes.
- Templates: Template classes must use the base namespace `Application\Themes\DefaultTemplate`.
- ThemeRenderer: Added `appendTemplateClass()`.
- Buttons: The label now accepts Stringable.
- Dependencies: Dropped the `erusev/parsedown` dependency.
- Dependencies: Updated AppUtils Core to [v1.0.4](https://github.com/Mistralys/application-utils-core/releases/tag/1.0.4).
- Dependencies: Updated collections to [v1.1.1](https://github.com/Mistralys/application-utils-collections/releases/tag/1.1.1).

## v3.1.0 - UI and News update
- News: Added article list navigation.
- News: Made permalinks less visible.
- Countries: Fixed unit tests adding duplicate countries.
- Countries: Made the `iso` column in the database `UNIQUE`.
- Countries: Added a check to see if an ISO code already exists.
- Interface Refs: Added code copy block example.
- Interface Refs: Added country flags example.
- Interface Refs: Added badge and label examples.
- Interface Refs: Added text color examples.
- UI: Added `addUnorderedList()` to the `UI_Help` class.
- UI: Fixed the size of headings in the page help.
- UI: Added dark mode (work in progress).
- UI: Added the system hint UI helper.
- StringBuilder: Added `secondary()`.
- StringBuilder: Added `success()`.
- Dependencies: Updated `zenorocha/clipboardjs`; simplified composer config.
- Dependencies: Updated `lipis/flag-icons` to recent version.
- Dependencies: Updated `ccampbell/mousetrap` to recent version.

### Dark mode

This requires a `diver-dark.css` to be added in the application's
`/htdocs/themes/default/css` folder. It is intended to contain any
application-specific dark mode color adjustments. It may be an
empty file.

Dark mode is still a work in progress, which means there are UI elements
left that are not dark mode capable yet.

### Database update

For existing installations, import the update SQL file:  

`docs/sql/2023-12-01-countries.sql`

This is not required, but recommended to avoid duplicate countries.

## v3.0.5 - News bugfix
- News: Fixed the media tag causing a PHP error.
- News: Added unit test for the media tag.

## v3.0.4 - News and Markdown update
- News: Fixed the jumbled order of articles in the news list.
- News: Added the creation date to the admin news list.
- News: Limited the width of the news entry detail view.
- MarkdownRenderer: Added the `{TOC}` tag.
- MarkdownRenderer: Added table support.
- MarkdownRenderer: Added heading anchors and permalinks.
- MarkdownRenderer: Added a dedicated style sheet.
- MarkdownRenderer: Streamlined layout of the TOC and headings.
- UserInterface: Fixed the UI font family not being applied.
- UserInterface: Improved typography with unified line heights and font sizes.

## v3.0.3 - Bugfix release
- MediaGallery: Fixed the image upload failing because of the missing file size.
- News: Added modified and created date fields in the settings.
- News: Enabled the scheduling feature.
- News: Tweaked the news layout for readability.
- News: Added a "Scheduling" badge when enabled.
- News: Added basic image styling.
- News: Adjusted heading sizes.

## v3.0.2 - Maintenance update
- Code: Replaced all uses of the obsolete `Interface_Stringable` interface.
- Code: Replaced all uses of the obsolete `Interface_Classable` interface.
- Code: Replaced all uses of the obsolete `Interface_Optionable` interface.
- Code: Modernized some classes, added type hints.

## v3.0.1 - Media Gallery update
- MediaGallery: Added the missing pagination in the image gallery.
- UI: Added the `PaginationRenderer` helper class.
- FilterCriteria: Renamed inverted `$limit` and `$offset` parameters in `setLimit()`.
- Theme: Added `getEmptyImageURL()` and `getEmptyImagePath()`.
- Dependencies: Updated AppUtils to [v3.0.3](https://github.com/Mistralys/application-utils/releases/tag/3.0.3).

## v3.0.0 - News and forms release (breaking-m)
- News: Added the experimental news feature for news articles and alerts.
- News: An example is available in the test application.
- Forms: Revamped the DateTimePicker element to work as intended.
- Forms: Extended RecordSettings now correctly handle key naming throughout.
- Forms: CollectionSettings now makes better use of the setting manager.
- Forms: Added `setImportFilter()` in the setting manager's settings.
- Forms: Fixed the Switch element's label not being clickable.
- Forms: Added possibility to specify a separate label ID.
- Forms: Fixed empty select values being ignored when a default value is present.
- Forms: Added `makeSubmitted()` to manually submit forms and formables.
- Forms: Added `$includeValue` to the Switch element's `makeYesNo()` method.
- RecordSettings: Record data and internal values are now separate.
- RecordSettings: `updateRecord()` is called after creation.
- RecordSettings: `getCreateData()` only has to handle internal values now.
- RecordSettings: Added create and edit screen variations using extended settings.
- RecordSettings: Fixed form value handling issues.
- RecordSettings: Added form handling test screens in the test application.
- Markdown: Added the `MarkdownRenderer` helper class.
- QuickStart: Removed the automatic refresh.
- Breadcrumb: Added `clearItems()` to remove all items.
- Changelog: Moved older versions to the `docs/changelog-history` folder.
- Dependencies: Updated QuickForm to [v2.3.2](https://github.com/Mistralys/HTML_QuickForm2/releases/tag/2.3.2).
- Dependencies: Updated AppUtils to [v3.0.0](https://github.com/Mistralys/application-utils/releases/tag/3.0.0).
- ErrorLog: Added display of the current PHP error settings.
- ErrorLog: Added possibility to trigger PHP errors.
- Media: Fixed thumbnails not being updated if the original image is changed.
- Media: Added a basic media management area.
- Media: Added a file size database column.
- Media: Added keywords and description database columns.
- Media: Unified uploads and documents, merged functionality for maintainability.
- Media: Improved document interfaces.
- Media: Added support for uploading PDF documents.
- CAS: Switched `renewAuthentication()` to `forceAuthentication()`.
- Code: Moved the PHPStan files to `tests/phpstan`.
- Code: PHPStan analysis clean @ level 5 with some leftover suppressed warnings.

### Breaking changes (M)

- The base test case class has been renamed and namespaced to
  `AppFrameworkTestClasses\ApplicationTestCase`.
- Arguments for the following methods have changed:
  - `Application_Formable_RecordSettings_Extended::getCreateData()`
  - `Application_Formable_RecordSettings_Extended::processPostCreateSettings()`
  - `Application_Formable_RecordSettings_Extended::createRecordFromValues()`
  - `Application_Formable_RecordSettings_Extended::updateRecord()`
- Review any wizard steps that use
  `Application_Traits_Admin_Wizard_CreateDBRecordStep`
  to ensure the data is processed correctly.
- Review any admin screens that use
  `Application_Traits_Admin_CollectionSettings`
  in combination with a settings manager, to ensure data
  is processed correctly.

### RecordSettings changes

When using `setDefaultsUseStorageNames()`, the setting of values in
the record has been streamlined. This expects `setStorageName()` to
be used for all keys whose value can be set directly. The filter methods
`(setImportFilter()` and `setStorageFilter()` can help with this.

Given these prerequisites, the data handling methods mentioned above
only have to handle the internal values, as these can only be set
manually. All others are set automatically in the record instance.

The `updateRecord()` method is now called after the record has been
created, to avoid code duplication with the creation methods.

### News Upgrade Guide

To use the experimental news feature, the database must be updated.
The necessary changes to upgrade an existing installation are available
in the SQL script `docs/sql/2023-10-16-news.sql`. The fresh installation
file can be used to set up a new database as usual (`docs/sql/pristine.sql`).

NOTE: The news feature is still experimental, and not entirely finished.
It has been released as is pending further development.

---
Older changelog entries can be found in the `docs/changelog-history` folder.
