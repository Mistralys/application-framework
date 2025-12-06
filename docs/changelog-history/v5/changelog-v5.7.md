## v5.7.9 - News update
- News: Improved styling of articles somewhat for readability.
- Markdown: Added the `class` attribute to `{media}` tags.
- Markdown: Added the boolean `thumbnail` attribute to `{media}` tags to turn off thumbnail generation.
- Markdown: Updated text styling somewhat for readability.

## v5.7.8 - Connector update
- Connectors: Added `201` as accepted status code for POST requests ([#76](https://github.com/Mistralys/application-framework/pull/76)) - thanks @danielioinos.

## v5.7.7 - DataGrid form target change
- DataGrids: Removed setting the form target for the whole grid.

Background for this change: Setting the form target for the whole
grid caused regular grid functions like sorting to also open in a
new tab. This was not the intended behavior and has been removed
in favor of setting it only for specific list actions.

## v5.7.6 - DataGrid enhancement (Deprecation-XS)
- DataGrids: Added `enableSubmitInNewTab()` to make the grid's form be submitted in a new tab.
- DataGrids: Added `setFormTarget()` and `getFormTarget()`.
- DataGrids: Added the `makeAutoWidth()` method so the grid uses only the width its columns need.
- DataGrids: Added `clientCommands()` to generate client-side statements.
- DataGrids: Added `clientCommands()` to grid entries as well.
- DataGrids: Now marking rows as active when the checkbox is checked.
- DataGrids: Improved layout of sorted cells with hover and active rows.
- Tests: Added the utility method `saveTestFile()`.
- BigSelection: Added the possibility to add meta-controls to items with `addMetaControl()`.
- Application: Added URL methods for the storage and temp folders, e.g. `getTempFolderURL()`.

### Deprecations

- `DataGrid::getClientSubmitStatement()` => use `clientCommands()` instead.
- `DataGrid::getClientToggleSelectionStatement()` => use `clientCommands()` instead.

## v5.7.5 - Screen Tie-In improvement
- Screen Tie-Ins: Added the handling of hidden vars with the optional `_getHiddenVars()` method.
- Screen Tie-Ins: Added the `injectHiddenVars()` method.
- AdminURL: Fixed the return type for `AdminURL::create()` to make PHPStan happy.

## v5.7.4 - Class cache update
- AppFactory: Now setting the `ClassHelper` cache during bootstrap to enable this for all use-cases.
- Dependencies: Bumped up AppUtils core to [v2.3.7](https://github.com/Mistralys/application-utils-core/releases/tag/2.3.7).

## v5.7.3 - AdminURL update
- AdminURL: Fixed the `create()` method not returning the correct instance.
- Dependencies: Bumped up AppUtils to [v3.1.4](https://github.com/Mistralys/application-utils/releases/tag/3.1.4).

## v5.7.2 - AdminURL update
- AdminURL: Now extending AppUtil's `URLBuilder` class.
- Dependencies: Bumped up AppUtils to [v3.1.3](https://github.com/Mistralys/application-utils/releases/tag/3.1.3).

## v5.7.1 - Formable type update
- Formable: Changed methods requiring element instances to accept nodes instead.

## v5.7.0 - Deployment task prioritization (Breaking-XS)
- DeploymentRegistry: Added a prioritization system for deployment tasks.
- DeploymentRegistry: The version update task is now always run first.
- DeploymentRegistry: Fixed the wrong version being stored in the history.
- AppFactory: Replaced the class cache with AppUtil's native class caching.
- Dependencies: Bumped up AppUtils core to [v2.3.6](https://github.com/Mistralys/application-utils-core/releases/tag/2.3.6).

### Breaking changes

- Deployment tasks must now implement the `getPriority()` method.
  If you have custom deployment tasks, make sure to add this method.
