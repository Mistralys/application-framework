# Custom Tags Reference

The Markdown Renderer extends standard CommonMark syntax with framework-specific
custom tags. Tags are processed in a pre-parse / post-parse cycle: they are
replaced with numeric placeholders before CommonMark conversion, then restored
with rendered HTML afterwards.

## Tag Syntax

```
{tagName: value [attr1="value1" attr2="value2"]}
```

All custom tags are implemented as subclasses of `BaseCustomTag` in the
`CustomTags/` folder.

---

## `{TOC}` — Table of Contents

Inserts an auto-generated table of contents at the placeholder position.
The TOC is built from all headings up to level 4 (`####`) found in the document.

This is powered by the CommonMark `TableOfContentsExtension` (not a custom tag
class) and configured via the `table_of_contents` option.

```markdown
{TOC}

## Introduction

### Setup

## Advanced Usage
```

---

## `{media: ID}` — Embed Media

Embeds an image from the application media library by its numeric ID.
The image is rendered as a clickable `<a><img></a>` element.

**Class:** `CustomTags\MediaTag`

### Examples

```
{media: 42}
{media: 42 width="400"}
{media: 42 title="Screenshot of the dashboard"}
{media: 42 thumbnail="no"}
{media: 42 class="custom-style"}
{media: 42 width="400" title="Alt text" class="my-image"}
```

### Parameters

| Parameter   | Type    | Description                                                               |
|-------------|---------|---------------------------------------------------------------------------|
| `width`     | integer | Width in pixels applied to the `<img>` element.                           |
| `title`     | string  | Value of the `title` (and `alt`) attribute on the image.                  |
| `thumbnail` | string  | Set to `"no"` or `"false"` to use the full-size URL instead of thumbnail. |
| `class`     | string  | Additional CSS class(es) added to the `<img>` element.                    |

The image always receives the CSS class `visual` in addition to any custom classes.
If the media ID does not exist, a warning message is rendered instead.

---

## `{api: MethodName}` — API Method Link

Inserts a hyperlink to the documentation page of an API method.
The method name is case-insensitive and must be alphanumeric.

**Class:** `CustomTags\APIMethodDocTag`

### Examples

```
{api: GetSomething}
{api: CreateRecord}
```

### Output

```html
<a href="/path/to/api/docs/GetSomething">GetSomething</a>
```

---

## Adding a New Custom Tag

1. Create a new class in `CustomTags/` extending `BaseCustomTag`.
2. Implement the static `findTags(string $subject): array` method with a regex
   to detect tag occurrences.
3. Implement the `render(): string` method to produce the final HTML.
4. Register the new tag in `MarkdownRenderer::preParse()` by adding a
   `findTags()` call.
