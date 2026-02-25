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
