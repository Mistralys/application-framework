# ClientResource

Manages **client-side resource loading** (JavaScript and CSS). Every include is registered with a unique *load key* that is tracked both server-side and client-side. On each AJAX request the browser submits the load keys it has already received via the `_loadkeys` parameter, so the server only injects resources that are genuinely new to the current page state.

## Integration

- Owned by: `UI_ResourceManager` (held on the `UI` singleton).
- Consumed by: any code that needs to register a JS or CSS dependency — components, themes, form elements, plugins.
- Client-side counterpart: `application.registerLoadKey()` JavaScript method.

## Key Classes

| Class | Role |
|---|---|
| `UI_ResourceManager` | Central registry; resolves duplicates; writes `<script>` / `<link>` tags into the page |
| `UI_ClientResource` | Abstract base for a single includeable asset |
| `UI_ClientResource_Javascript` | JS include — supports `defer`, custom attributes, `integrity`, `type=module` |
| `UI_ClientResource_Stylesheet` | CSS include |
| `UI_ClientResourceCollection` | Ordered set of resources, allows bulk registration |

## Typical Usage

```php
// Register a JS file (deduplicated automatically)
$ui->addJavascript('vendor/my-plugin/plugin.js');

// Register a stylesheet
$ui->addStylesheet('themes/default/css/extras.css');
```

> Related: [UI module overview](../README.md)
