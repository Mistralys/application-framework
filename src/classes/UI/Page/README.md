# Page

Orchestrates **full-page composition**. A `UI_Page` aggregates all structural regions — header, sidebar, footer, breadcrumb trail, and named navigations — and delegates their rendering to the active theme's `frame` template.

## Integration

- Entry point: `UI_Page`, held on the `UI` singleton and created by the application driver.
- Rendered via the theme template identified by the `frame` property (default: `frame`).
- Consumed by: the application driver and admin screens that need to populate page regions.
- See [Themes and Templates](../Docs/themes-and-templates.md) for the rendering pipeline.

## Folder Overview

| Folder / File | Contents |
|---|---|
| `Breadcrumb/` | Breadcrumb trail builder |
| `Help/` | Contextual help panel |
| `Navigation/` | Named navigation menus |
| `Section/` | Page content sections |
| `Sidebar/` | Sidebar region and its widgets |
| `StepsNavigator/` | Step-by-step wizard navigator |
| `Template/` | Page-level template helpers |
| `Breadcrumb.php` | Breadcrumb entry point |
| `Footer.php` | Footer region |
| `Header.php` | Header region |
| `Navigation.php` | Base navigation class |
| `RevisionableTitle.php` | Title helper for revisioned records |
| `Sidebar.php` | Sidebar region |
| `StepsNavigator.php` | Wizard step navigator |
| `Subtitle.php` | Page subtitle |
| `Title.php` | Page title |

## Key `UI_Page` API Surface

| Method | Purpose |
|---|---|
| `setTitle(string)` | Sets the page `<title>` and visible heading |
| `getHeader()` | Returns the `UI_Page_Header` instance |
| `getSidebar()` | Returns the `UI_Page_Sidebar` instance |
| `getFooter()` | Returns the `UI_Page_Footer` instance |
| `getBreadcrumb()` | Returns the active `UI_Page_Breadcrumb` |
| `addNavigation(string)` | Registers a named `UI_Page_Navigation` |
| `setContent(string)` | Injects the main content area HTML |
| `render()` | Triggers the theme frame template and returns complete HTML |

> Related: [UI module overview](../README.md) · [Themes and Templates](../Docs/themes-and-templates.md)
