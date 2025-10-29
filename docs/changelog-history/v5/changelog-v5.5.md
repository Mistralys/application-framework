## v5.5.5 - Country ButtonBar fix
- Country ButtonBar: Merged hotfix from [v5.4.5-hotfix1](https://github.com/Mistralys/application-framework/releases/tag/5.4.5-hotfix1).
- Country ButtonBar: Added constructor parameter to limit the available countries.
- Country ButtonBar: Fixed duplicate country parameter in links.
- Formable: Added overridable `_handleFormableInitialized()`.

## v5.5.4 - Small enhancements
- Wizards: Added `_onRecordCreated()` to the DB creation step.
- UI: Linked labels and badges now clearly show that they are clickable on hover.

## v5.5.3 - Session handling
- Session: Added namespaces for disabled authentication and session simulation.

## v5.5.2 - Fixes
- UI: Fixed the request log link in the footer.
- UI: Fixed the broken deployment callback link in the footer.
- Session: Sessions are now namespaced to the auth type to avoid NoAuth / CAS conflicts.

## v5.5.1 - Fixes
- Driver: Moved the `version` file to the application's cache folder.
- Session: Added more logging to debug authentication issues.

## v5.5.0 - Quality of Life and Tagging (Breaking-L)
- Markdown Renderer: Fixed image tags missing the `width` attribute.
- Media: Tags are now shown in the image gallery.
- Media: Tags can be edited in the image gallery.
- Media: Image names are now linked to the media document pages in the image gallery.
- Media: Fixed documents being loaded every time `getByID()` is called.
- Media: Tags can now be edited in the status screen directly.
- Driver: The version handling system now officially uses the `dev-changelog.md` file.
- Driver: The version info has been moved from the `DevChangelog` to `VersionInfo`.
- Driver: Added `AppFactory::createVersionInfo()`.
- Deployments: The version file is now created with a deployment task.
- OfflineEvents: Now using the class cache to load listeners.
- OfflineEvents: The listener folders are now named after the event name.
- OfflineEvents: Listeners now only need to implement the `handleEvent()` method.
- Tags: Added the `TagCollectionRegistry` that collects all taggable record collections.
- Tags: Added `getByUniqueID()` and `uniqueIDExists()`.
- Tags: Added the `TaggableUniqueID` utility class to work with unique IDs.
- AppFactory: Added `createVersionInfo()`.
- UI: Added an ES6 dialog implementation.
- UI: Added the `UI.HideTooltip()` clientside method.
- UI: Added the utility class `ElementIds` to work with element IDs and getting elements.
- FilterSettings: Added `configureFiterSettings()` to make adjustments possible.
- AJAX: Added the base class `BaseHTMLAjaxMethod` for HTML-based requests.
- AJAX: Added the base class `BaseJSONAjaxMethod` for JSON-based requests.
- Session: Fixed session not being destroyed when the user logs out.

### Upgrade guide

See the [upgrade guide](docs/upgrade-guides/upgrade-v5.5.0.md) for details.
