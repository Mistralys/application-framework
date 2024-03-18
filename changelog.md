## v4.0.0 - Tagging and Revisionables update (DB-update L)
- Tags: Added the tagging management.
- Media: Documents are now taggable.
- Tests: The Test DB collection is now taggable.
- DBHelper: Added `getRelationsForField()` to fetch all field relations.
- JS: Added the `Logger` utility class.
- ButtonGroup: Added `addButtons()`.
- TreeRenderer: Added the `TreeRenderer` UI helper class.
- UI: Added `createTreeRenderer()`.
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
- Revisionables: Added a test revisionable collection to the test application.
- Revisionables: Added first unit tests.
- Revisionables: Modernized classes and strict typing.
- Revisionables: Automated the saving of custom revision table keys.
- Revisionables: Added an interface for the revisionable with state.
- Revisionables: Added a trait to implement the typical state setup.
- Revisionables: Added `RevisionableDependentInterface`.
- Revisionables: Added `requireRevisionableMatch()` and `requireRevisionMatch()`. 
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


---
Older changelog entries can be found in the `docs/changelog-history` folder.
