# Public API

## MarkdownRenderer

Factory-created via `MarkdownRenderer::create()`. Implements `OptionableInterface`.

| Method | Description |
|---|---|
| `create(): self` | Static factory. |
| `render(string $markdown): string` | Converts Markdown to HTML wrapped in `<div class="markdown">`. Processes custom tags and loads the `ui-markdown.css` stylesheet. |
| `renderInline(string $markdown): string` | Converts Markdown to HTML without the wrapping `<div>` or outer `<p>` tags. Useful for single-line content. |
| `setHTMLInput(string $mode): self` | Controls how raw HTML in the source is handled: `'allow'`, `'strip'` (default), or `'escape'`. |
| `injectReference(?StringBuilder, bool $quickRef): StringBuilder` | Appends a human-readable Markdown syntax reference (with optional quick-ref bullet list) to a `StringBuilder`. |
| `parseParams(string $params): AttributeCollection` | Parses a tag's attribute string (`key="value"` pairs) into an `AttributeCollection`. |
| `getName(): string` | Returns the renderer name (`'Markdown'`). |

### Constants

| Constant | Value | Purpose |
|---|---|---|
| `WRAPPER_CLASS` | `'markdown'` | CSS class on the wrapping `<div>`. |
| `HTML_MODE_ALLOW` | `'allow'` | Allow raw HTML pass-through. |
| `HTML_MODE_STRIP` | `'strip'` | Strip raw HTML (default). |
| `HTML_MODE_ESCAPE` | `'escape'` | Escape raw HTML entities. |
| `MARKDOWN_DOCUMENTATION_URL` | CommonMark help URL | For linking end-users to syntax help. |

## BaseCustomTag

Abstract base class for all custom tags. Implements `RenderableInterface`.

| Method | Description |
|---|---|
| `getMatchedText(): string` | Returns the original matched tag string. |
| `getPlaceholder(): string` | Returns the numeric placeholder used during CommonMark conversion. |
| `getAttribute(string $name): string` | Retrieves a parsed attribute value. |
| `getAttributes(): AttributeCollection` | Returns the full attribute collection. |
| `render(): string` | *(abstract)* Produces the final HTML output. |

## Custom Tag Classes

### MediaTag

Renders media library images. See [Custom Tags Reference](custom-tags.md#media-id--embed-media).

| Method | Description |
|---|---|
| `findTags(string $subject): MediaTag[]` | Detects `{media: …}` occurrences. |
| `getMediaID(): int` | Returns the referenced media ID. |
| `getDocument(): ?Application_Media_Document` | Fetches the media record (or `null`). |
| `getWidth(): ?int` | Parsed `width` attribute. |
| `getTitle(): ?string` | Parsed `title` attribute. |
| `isThumbnail(): bool` | Whether to use the thumbnail URL. |
| `getClasses(): array` | Merged CSS classes (always includes `visual`). |

### APIMethodDocTag

Renders links to API method documentation. See [Custom Tags Reference](custom-tags.md#api-methodname--api-method-link).

| Method | Description |
|---|---|
| `findTags(string $subject): APIMethodDocTag[]` | Detects `{api: …}` occurrences. |
| `getMethodName(): string` | Returns the referenced API method name. |
