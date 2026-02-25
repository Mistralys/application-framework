# Themes

Provides a **PHP-based template and theming engine**. Templates are PHP classes (not a templating language), which keeps the toolchain lean and allows full IDE support. Framework templates can be transparently overridden by application-level themes.

## Integration

- Entry point: `UI_Themes` (held on the `UI` singleton); accessed via `$ui->getThemes()`.
- The active theme is resolved from `APP_ROOT/themes` first, then `APP_INSTALL_FOLDER/themes`.
- The default theme ID is `default`.
- See also: [Themes and Templates overview](../Docs/themes-and-templates.md).

## Folder Overview

| Folder / File | Contents |
|---|---|
| `BaseTemplates/` | Abstract base classes for framework-defined templates |
| `Exception/` | Typed theme exceptions |
| `Theme/` | Per-theme class and template discovery logic |
| `Exception.php` | Top-level theme exception |
| `Theme.php` | `UI_Themes_Theme` — represents a resolved theme and provides template lookup |

## How Template Inheritance Works

1. The framework ships templates in `APP_INSTALL_FOLDER/themes/<theme-id>/`.
2. An application can create `APP_ROOT/themes/<theme-id>/` and place override classes there.
3. `UI_Themes` checks the application path first; if no override exists it falls back to the framework path.

This means an application can replace any single template without copying the entire theme.

## Key `UI_Themes` API

| Method | Purpose |
|---|---|
| `getThemeID()` | Returns the active theme identifier |
| `selectTheme(string)` | Switch to a different theme at runtime |
| `getTheme()` | Returns the active `UI_Themes_Theme` instance |

> Related: [UI module overview](../README.md) · [Themes and Templates deep-dive](../Docs/themes-and-templates.md)
