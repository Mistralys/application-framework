# Markdown Renderer

The framework's markdown renderer is based on CommonMark,
and adds some framework-specific tags that extend the base
capabilities of the Markdown syntax for Framework features.

## Custom Tag Syntax

Custom tags follow this format:

`{tagName: parameters}`

Example:

`{media: 42 width="400"}`

## Usage

```php
use Application\MarkdownRenderer\MarkdownRenderer;

$html = MarkdownRenderer::create()->render($markdown);
```
