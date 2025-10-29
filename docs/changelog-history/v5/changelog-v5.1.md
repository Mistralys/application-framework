## v5.1.1 - DataGrid fix
- DataGrid: Fixed a PHP error when using a string value that corresponds to a PHP function ([#72](https://github.com/Mistralys/application-framework/issues/72))
- DataGrid: Callback cell values are now filtered like all other values.

## v5.1.0 - DataGrid Enhancements
- Clientside: Modernized the renderable classes, converted to ES6.
- Clientside: Converted `ApplicationException` to ES6.
- DataGrid: Modernized the JS class, converted to ES6.
- DataGrid: Added the grid configuration UI.
- DataGrid: Fixed the column settings storage not being applied.
- DataGrid: Columns can now be sorted and hidden on a per-user basis.
- DataGrid: Converted the main JS classes to ES6 classes.
- DataGrid: It is now possible to reset individual grid settings.
- DataGrid: Cell values can now use callables to generate the value on demand.
- Examples: Added a detailed DataGrid column controls example.
- Users: Added the `$prefix` parameter to the `resetSettings()` method to limit the reset.

### Deprecations

- JS: `Application_BaseRenderable` => `UI_Renderable_Base`
- JS: `Application_RenderableHTML` => `UI_Renderable_HTML`
