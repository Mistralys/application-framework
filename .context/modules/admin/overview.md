# Admin - Overview
_SOURCE: Admin Module Overview_
# Admin Module Overview
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── Admin/
                └── README.md

```
###  Path: `/src/classes/Application/Admin/README.md`

```md
# Admin

The administration screen layer: provides the hierarchical screen system that powers the entire back-office UI. Every page a user sees in the admin panel is composed of screens organized in a strict **Area → Mode → Submode → Action** hierarchy, each level inheriting rendering, locking, rights-checking, and navigation from the `Skeleton` base class.

## Dependencies

| Module | Purpose |
|--------|---------|
| `UI` | Page rendering, breadcrumbs, sidebar, navigation, and content renderer |
| `Application_Driver` | Request routing, URL generation, and redirect handling |
| `Application_Formable` | Form creation and validation (parent of `Skeleton`) |
| `EventHandler` | Lifecycle events emitted during screen rendering |
| `Application_LockManager` | Concurrent-editing lock support for screens |
| `Application_User` | Rights-based access control per screen |
| `Application_Request` | HTTP request parameter access |
| `Application_Session` | Session state for the active user |

## Folder Overview

| Folder | Purpose |
|--------|---------|
| `Area/` | Base classes for modes and submodes within an area, plus lifecycle events |
| `Index/` | Screen indexer and sitemap builder — discovers all screens at build time and exposes them via API |
| `RequestTypes/` | Typed request parameter helpers for fetching records from the current request |
| `Screens/` | Lifecycle events fired during screen rendering (actions, breadcrumb, sidebar, content) |
| `Traits/` | Developer-mode interface/trait for screens exposed only in dev environments |
| `Welcome/` | Built-in Welcome/Quickstart area with overview and settings modes |
| `Wizard/` | Multi-step wizard support: base wizard mode, steps, invalidation handling, and Preselection API |

## Documentation

| Document | Contents |
|----------|----------|
| [`docs/public-api.md`](docs/public-api.md) | Public API entry points |
| [`docs/screen-hierarchy.md`](docs/screen-hierarchy.md) | The Area → Mode → Submode → Action lifecycle |
| [`docs/rights-and-locking.md`](docs/rights-and-locking.md) | Access control and concurrent-editing locks |
| [`docs/events.md`](docs/events.md) | Screen lifecycle events reference |
| [`docs/wizard.md`](docs/wizard.md) | Wizard system, Preselection API usage, and pitfalls |

```