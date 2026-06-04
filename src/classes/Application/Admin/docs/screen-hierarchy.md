# Admin — Screen Hierarchy

## Overview

The admin layer enforces a strict four-level screen hierarchy. Each level is a separate class that extends the corresponding base:

```
Area → Mode → Submode → Action
```

URL parameters map directly to these levels:

| Level | URL Parameter | Base Class (namespaced) | Legacy Base Class |
|-------|--------------|------------------------|-------------------|
| Area | `page` | `Application\Admin\BaseArea` | `Application_Admin_Area` |
| Mode | `mode` | `Application\Admin\Area\BaseMode` | `Application_Admin_Area_Mode` |
| Submode | `submode` | `Application\Admin\Area\Mode\BaseSubmode` | `Application_Admin_Area_Mode_Submode` |
| Action | `action` | `Application\Admin\Area\Mode\Submode\BaseAction` | `Application_Admin_Area_Mode_Submode_Action` |

## Lifecycle

When a request arrives, the `Application_Driver` resolves the active area from the `page` parameter, then descends through modes, submodes, and actions by matching subsequent URL parameters to registered subscreens.

Each screen level executes the same rendering lifecycle (defined in `Skeleton`):

1. **`handleActions()`** — Execute business logic (form submissions, deletions, etc.)
2. **`handleBreadcrumb()`** — Append breadcrumb entries for the current level
3. **`handleSidebar()`** — Populate the page sidebar
4. **`handleSubnavigation()`** — Build subnavigation tabs/links
5. **`handleContextMenu()`** — Add items to the context dropdown
6. **`handleHelp()`** — Register help content
7. **`_renderContent()`** — Render the screen's HTML output

Lifecycle events are dispatched between phases (see [`events.md`](events.md)).

## Default Subscreens

Each screen can declare a **default subscreen** via `getDefaultMode()` (areas) or `getDefaultSubscreenID()`. When no deeper URL parameter is provided, the default subscreen is loaded automatically.

## Class Loader Discovery

Screens implementing `ClassLoaderScreenInterface` participate in the indexer's class-loader-based navigation tree. The indexer uses `getDefaultSubscreenClass()` and `getParentScreenClass()` to build a full parent-child map at build time.

## Stub Mode

Areas can be instantiated with `$adminMode = false`, which disables the full UI layer (no actions, no redirects). This is used by the indexer to inspect screen metadata without side effects.
