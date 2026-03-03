# UI Themes - Overview
_SOURCE: Themes README_
# Themes README
```
// Structure of documents
└── src/
    └── classes/
        └── UI/
            └── Themes/
                └── README.md

```
###  Path: `/src/classes/UI/Themes/README.md`

```md
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

```
_SOURCE: Themes and templates deep-dive_
# Themes and templates deep-dive
```
// Structure of documents
└── src/
    └── classes/
        └── UI/
            └── Docs/
                └── themes-and-templates.md

```
###  Path: `/src/classes/UI/Docs/themes-and-templates.md`

```md
# Themes and Templates

## PHP-Based templates

The Themes do not use any known templating engine. Instead,
each template is a PHP class that renders HTML code. 
The philosophy behind this is that PHP is a template language
by design, so the system just facilitates using it this way.

## Template Inheritance and Overrides

Templates defined in the Framework can be overridden in the
application.
```
---
**File Statistics**
- **Size**: 2.76 KB
- **Lines**: 89
File: `modules/ui/themes/overview.md`
