# Modules Overview

> Auto-generated on 2026-03-04 15:52:14. Do not edit manually.

Total: 17 modules across 1 package.

## mistralys/application_framework

| ID | Label | Description | Source Path | Context Docs | Related Modules |
|----|-------|-------------|-------------|--------------|-----------------|
| `api-openapi` | API OpenAPI | End-to-end support for generating OpenAPI 3.1 specifications from the framework API system and serving them over HTTP. Covers parameter type mapping (TypeMapper), reusable component schemas and security schemes for the standard API response envelopes (OpenAPISchema), Apache .htaccess generation for RESTful URL rewriting (HtaccessGenerator), conversion of framework API parameters to OpenAPI parameter objects and request body schema properties (ParameterConverter), conversion of API method response metadata to OpenAPI response objects for 200/400/500 status codes (ResponseConverter), full spec assembly with error resilience and authentication/validation documentation (OpenAPIGenerator, MethodConverter), HTTP serving of the pre-generated spec as raw JSON (GetOpenAPISpec), and application-level convenience entry points (APIManager::generateOpenAPISpec, APIManager::generateHtaccess). The composer build pipeline calls both generation steps automatically via ComposerScripts. | `src/classes/Application/API/OpenAPI/` | `.context/modules/openapi/` | — |
| `application-sets` | Application Sets | Configuration-level system to control which administration areas are enabled per application instance, supporting multiple feature configurations. | `src/classes/Application/AppSets/` | `.context/modules/application-sets/` | db-helper |
| `composer` | Application Composer | Build-time utilities that generate Markdown documentation artefacts (Modules Overview and Keyword Glossary) from module-context.yaml files discovered throughout the codebase, generate the OpenAPI 3.1 specification JSON, and generate the API .htaccess for RESTful URL rewriting. Includes a shared BuildMessages registry for build-time notices. All steps are orchestrated by ComposerScripts::build(). | `src/classes/Application/Composer/` | `.context/modules/composer/` | event-handler |
| `connectors` | Connectors | Scaffold for building HTTP connector classes to access external APIs, supporting GET, POST, PUT, and DELETE methods. | `src/classes/Connectors/` | `.context/modules/connectors/` | — |
| `db-helper` | DBHelper | Provides database abstraction for manual SQL operations and an ORM-like record collection system with filtering, events, and CRUD operations. | `src/classes/DBHelper/` | `.context/modules/db-helper/` | event-handler, ui, ui-datagrid, application-sets |
| `event-handler` | Event Handling | Comprehensive event handling system supporting global events, instance-scoped Eventable objects, and offline just-in-time event listeners. | `src/classes/Application/EventHandler/` | `.context/modules/event-handler/` | ui, ui-form, db-helper, composer |
| `ui` | User Interface | Central rendering layer of the framework: the UI singleton, Bootstrap component abstractions, page composition, client-side resource management, and the PHP-based theming engine. | `src/classes/UI/` | `.context/modules/ui/` | event-handler, db-helper, ui-datagrid, ui-tree, ui-markup-editor, ui-page, ui-form, ui-admin-urls, ui-bootstrap, ui-client-resources, ui-properties-grid, ui-themes |
| `ui-admin-urls` | UI Admin URLs | Type-safe fluent URL builder for constructing admin-screen navigation links using the framework's area/mode/submode/action routing scheme. | `src/classes/UI/AdminURLs/` | `.context/modules/ui/admin-urls/` | ui, ui-tree |
| `ui-bootstrap` | UI Bootstrap Components | PHP abstractions for Bootstrap v2 UI components — dropdowns, tabs, button groups, popovers, and the BigSelection widget — each exposing a fluent builder API. | `src/classes/UI/Bootstrap/` | `.context/modules/ui/bootstrap/` | ui |
| `ui-client-resources` | UI Client Resources | Manages client-side JS and CSS resource registration with load-key deduplication, ensuring each asset is injected into the page exactly once across both full-page loads and AJAX requests. | `src/classes/UI/ClientResource/` | `.context/modules/ui/client-resources/` | ui |
| `ui-datagrid` | UI DataGrid | Renders tabular data with built-in column sorting, pagination, per-user column configuration, row selection, and bulk actions driven by a pluggable list-builder source. | `src/classes/UI/DataGrid/` | `.context/modules/ui/datagrid/` | ui, db-helper |
| `ui-form` | UI Form | Handles form creation, element composition, pluggable rendering, and server-side validation, built on HTML_QuickForm2 with framework-layer conventions for AJAX submit and typed rules. | `src/classes/UI/Form/` | `.context/modules/ui/form/` | ui, event-handler, ui-markup-editor |
| `ui-markup-editor` | UI Markup Editor | Integrates WYSIWYG rich-text editors (CKEditor 5 and Redactor) into forms through a unified abstract API with configurable toolbar composition. | `src/classes/UI/MarkupEditor/` | `.context/modules/ui/markup-editor/` | ui, ui-form, ui-themes |
| `ui-page` | UI Page | Orchestrates full-page composition by aggregating the header, sidebar, footer, breadcrumb trail, and named navigations, delegating rendering to the active theme's frame template. | `src/classes/UI/Page/` | `.context/modules/ui/page/` | ui, ui-themes |
| `ui-properties-grid` | UI Properties Grid | Renders a key/value property table for detail views, supporting typed property variants, inline action buttons, and conditional row visibility. | `src/classes/UI/PropertiesGrid/` | `.context/modules/ui/properties-grid/` | ui |
| `ui-themes` | UI Themes | PHP-based template and theming engine where templates are PHP classes; framework templates can be transparently overridden by application-level themes without copying the entire theme. | `src/classes/UI/Themes/` | `.context/modules/ui/themes/` | ui, ui-markup-editor, ui-page |
| `ui-tree` | UI Tree | Hierarchical tree widget composed of nestable nodes that support icons, URL or JavaScript link targets, active/selected states, and per-node action buttons. | `src/classes/UI/Tree/` | `.context/modules/ui/tree/` | ui, ui-admin-urls |

## Module Relationships

- **application-sets** → db-helper
- **composer** → event-handler
- **db-helper** → event-handler, ui, ui-datagrid, application-sets
- **event-handler** → ui, ui-form, db-helper, composer
- **ui** → event-handler, db-helper, ui-datagrid, ui-tree, ui-markup-editor, ui-page, ui-form, ui-admin-urls, ui-bootstrap, ui-client-resources, ui-properties-grid, ui-themes
- **ui-admin-urls** → ui, ui-tree
- **ui-bootstrap** → ui
- **ui-client-resources** → ui
- **ui-datagrid** → ui, db-helper
- **ui-form** → ui, event-handler, ui-markup-editor
- **ui-markup-editor** → ui, ui-form, ui-themes
- **ui-page** → ui, ui-themes
- **ui-properties-grid** → ui
- **ui-themes** → ui, ui-markup-editor, ui-page
- **ui-tree** → ui, ui-admin-urls
