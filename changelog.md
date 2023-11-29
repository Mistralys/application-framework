### v3.0.2 - Maintenance update
- Code: Replaced all uses of the obsolete `Interface_Stringable` interface.
- Code: Replaced all uses of the obsolete `Interface_Classable` interface.
- Code: Replaced all uses of the obsolete `Interface_Optionable` interface.

### v3.0.1 - Media Gallery update
- MediaGallery: Added the missing pagination in the image gallery.
- UI: Added the `PaginationRenderer` helper class.
- FilterCriteria: Renamed inverted `$limit` and `$offset` parameters in `setLimit()`.
- Theme: Added `getEmptyImageURL()` and `getEmptyImagePath()`.
- Dependencies: Updated AppUtils to [v3.0.3](https://github.com/Mistralys/application-utils/releases/tag/3.0.3).

### v3.0.0 - News and forms release (breaking-m)
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

#### Breaking changes (M)

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

#### RecordSettings changes

When using `setDefaultsUseStorageNames()`, the setting of values in
the record has been streamlined. This expects `setStorageName()` to
be used for all keys whose value can be set directly. The filter methods
`(setImportFilter()` and `setStorageFilter()` can help with this. 

Given these prerequisites, the data handling methods mentioned above
only have to handle the internal values, as these can only be set
manually. All others are set automatically in the record instance.

The `updateRecord()` method is now called after the record has been
created, to avoid code duplication with the creation methods.

#### News Upgrade Guide

To use the experimental news feature, the database must be updated. 
The necessary changes to upgrade an existing installation are available 
in the SQL script `docs/sql/2023-10-16-news.sql`. The fresh installation 
file can be used to set up a new database as usual (`docs/sql/pristine.sql`).

NOTE: The news feature is still experimental, and not entirely finished.
It has been released as is pending further development.

---
Older changelog entries can be found in the `docs/changelog-history` folder.
