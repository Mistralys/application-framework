# UI Module - Overview
_SOURCE: UI Module README_
# UI Module README
```
// Structure of documents
└── src/
    └── classes/
        └── UI/
            └── README.md

```
###  Path: `/src/classes/UI/README.md`

```md
# User Interface Module

The UI module is the **central rendering layer** of the framework. It owns the `UI` singleton, all HTML component abstractions, the page composition system, client-side resource management, and the PHP-based theming engine.

## Client-Side Stack

| Library | Version |
|---|---|
| **Bootstrap** | v2 |
| **jQuery** | – |
| **jQuery UI** | – |
| **FontAwesome** | v5 |

## Submodules

| Submodule | Summary |
|---|---|
| [AdminURLs](AdminURLs/README.md) | Type-safe URL builder for navigating admin screens |
| [Bootstrap](Bootstrap/README.md) | PHP abstractions for Bootstrap UI components |
| [ClientResource](ClientResource/README.md) | JS/CSS client-side resource loading with load-key deduplication |
| [DataGrid](DataGrid/README.md) | Tabular data display with sorting, pagination, and bulk actions |
| [Form](Form/README.md) | Form creation, rendering, and server-side validation |
| [MarkupEditor](MarkupEditor/README.md) | WYSIWYG rich-text editor integration (CKEditor5 / Redactor) |
| [Page](Page/README.md) | Page composition — header, sidebar, footer, breadcrumb, navigation |
| [PropertiesGrid](PropertiesGrid/README.md) | Key/value property display for detail views |
| [Themes](Themes/README.md) | PHP-based template and theming engine |
| [Tree](Tree/README.md) | Hierarchical tree widget with node actions |

## Cross-Cutting Topics

- [UI Helper Classes](Docs/ui-helper-classes.md) — overview of the helper-class pattern  
- [Themes and Templates](Docs/themes-and-templates.md) — PHP-based template inheritance

## Primary Entry Point

`UI` (`UI.php`) is the framework singleton. It owns the current `UI_Page`, the `UI_Themes` manager, and the `UI_ResourceManager`. All component creation flows through this class or through the active page.

```
_SOURCE: Cross-cutting documentation_
# Cross-cutting documentation
```
// Structure of documents
└── src/
    └── classes/
        └── UI/
            └── Docs/
                └── themes-and-templates.md
                └── ui-helper-classes.md

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
###  Path: `/src/classes/UI/Docs/ui-helper-classes.md`

```md
# User Interface - Helper Classes

## Admin URLs

## Data Grids

## Properties Grid
```
---
**File Statistics**
- **Size**: 2.97 KB
- **Lines**: 98
File: `modules/ui/overview.md`
