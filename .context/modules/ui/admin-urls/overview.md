# UI AdminURLs - Overview
_SOURCE: AdminURLs README_
# AdminURLs README
```
// Structure of documents
└── src/
    └── classes/
        └── UI/
            └── AdminURLs/
                └── README.md

```
###  Path: `/src/classes/UI/AdminURLs/README.md`

```md
# AdminURLs

Provides a **type-safe URL builder** for constructing admin-screen navigation links. Instead of manually assembling query strings, callers fluently set the `area`, `mode`, `submode`, and `action` parameters—the standard four-segment routing scheme used throughout the framework's admin UI.

## Integration

- Consumed by: admin screens, data-grid actions, navigation helpers, any code that produces a link to another admin location.
- Depends on: `AppUtils\URLBuilder\URLBuilderInterface` (upstream URL-building contract).

## Key Classes

| Class / Interface | Role |
|---|---|
| `AdminURLInterface` | Contract: `area()`, `mode()`, `submode()`, `action()`, and general URL-parameter methods |
| `AdminURL` | Concrete implementation of `AdminURLInterface` |
| `AdminURLsInterface` | Implemented by objects that can produce an `AdminURL` for themselves |
| `AdminURLException` | Typed exception for URL-construction errors |

## Typical Usage

```php
$url = AdminURL::create()
    ->area('Products')
    ->mode('Edit')
    ->submode('Details')
    ->param('id', 42);

echo $url; // renders as query string / href
```

> Related: [UI module overview](../README.md)

```
---
**File Statistics**
- **Size**: 1.54 KB
- **Lines**: 54
File: `modules/ui/admin-urls/overview.md`
