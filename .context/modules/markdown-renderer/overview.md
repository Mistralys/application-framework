# Markdown Renderer - Overview
_SOURCE: Markdown Renderer Overview_
# Markdown Renderer Overview
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── MarkdownRenderer/
                └── README.md

```
###  Path: `/src/classes/Application/MarkdownRenderer/README.md`

```md
# Markdown Renderer

Converts Markdown text to styled HTML for display in Framework-based applications.
Built on **CommonMark** with GitHub Flavored Markdown extensions, and extended
with custom tags for embedding media library images and API documentation links.

## Usage

```php
use Application\MarkdownRenderer\MarkdownRenderer;

// Block rendering (wrapped in <div class="markdown">)
$html = MarkdownRenderer::create()->render($markdown);

// Inline rendering (no wrapping <div> or outer <p> tags)
$html = MarkdownRenderer::create()->renderInline($markdown);
```

The renderer automatically loads the `ui-markdown.css` stylesheet via the UI layer.

## Dependencies

| Dependency | Role |
|---|---|
| [league/commonmark](https://commonmark.thephpleague.com/) | Core Markdown-to-HTML conversion engine. |
| `UI` (Framework) | Stylesheet injection (`ui-markdown.css`). |
| `Application_Media` (Framework) | Media library lookups for `{media}` tags. |
| `Application\API\APIManager` (Framework) | URL generation for `{api}` tags. |

## CommonMark Extensions

| Extension | What it adds |
|---|---|
| GitHub Flavored Markdown | Strikethrough, task lists, autolinks, tables |
| Heading Permalink | Anchor links (`#`) on every heading |
| Table of Contents | Auto-generated TOC via `{TOC}` placeholder |

## Custom Tags

The renderer supports framework-specific tags using the syntax `{tagName: value [attr="val"]}`.
Tags are processed in a pre-parse / post-parse cycle around CommonMark conversion.

| Tag | Purpose |
|---|---|
| `{TOC}` | Insert an auto-generated table of contents |
| `{media: ID}` | Embed a media library image |
| `{api: MethodName}` | Link to API method documentation |

See [docs/custom-tags.md](docs/custom-tags.md) for the full reference and extension guide.

## Folder Overview

| Path | Contents |
|---|---|
| `MarkdownRenderer.php` | Main renderer class — factory, options, parse pipeline. |
| `BaseCustomTag.php` | Abstract base class for custom tag implementations. |
| `CustomTags/` | Concrete tag classes (`MediaTag`, `APIMethodDocTag`). |
| `docs/` | Technical documentation (custom tags reference, public API). |

## Documentation

| Document | Description |
|---|---|
| [docs/custom-tags.md](docs/custom-tags.md) | Full custom tags reference with parameters and extension guide. |
| [docs/public-api.md](docs/public-api.md) | Public API surface for all classes in this module. |

For the full standard Markdown syntax reference see <https://commonmark.org/help/>.

```
---
**File Statistics**
- **Size**: 2.89 KB
- **Lines**: 90
File: `modules/markdown-renderer/overview.md`
