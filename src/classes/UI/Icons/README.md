# UI\Icons — Runtime Icon Registry

The `UI\Icons` namespace provides a PHP-level API for querying all icons
available in the framework and (optionally) the application, returning typed
value objects that carry the icon's metadata and a factory method to
instantiate the matching `UI_Icon`.

## Classes

### `IconCollection` — Singleton Registry

`IconCollection` is the primary entry point. It loads both the framework icon
JSON file and the (optional) application custom icon JSON file, merges them,
and exposes the result through a typed PHP API.

```php
use UI\Icons\IconCollection;

$icons = IconCollection::getInstance();

// All icons, sorted alphabetically by ID
foreach($icons->getAll() as $icon) {
    echo $icon->getID() . ' → ' . $icon->getFullIconName();
}

// Look up a specific icon by ID (normalised form: underscores, not hyphens)
$icon = $icons->getByID('time_tracker');

// Create a UI_Icon instance for rendering
$uiIcon = $icon->createIcon();

// Split by source
$standard = $icons->getStandardIcons();
$custom   = $icons->getCustomIcons();
```

**JSON sources:**

| Source | Path | Required |
|---|---|---|
| Framework icons | `APP_INSTALL_FOLDER/themes/default/icons.json` | Yes |
| Application custom icons | `APP_ROOT/themes/custom-icons.json` | No |

**Key behaviours:**

- IDs are normalised on load: hyphens and spaces become underscores.
- Custom icons override standard icons with the same normalised ID.
- The merged collection is sorted alphabetically by ID.
- The singleton is created on first `getInstance()` call and reused thereafter.

**`getByID()` normalisation precondition:**

`getByID()` performs a direct map lookup — it does **not** normalise its
input. Always pass the normalised ID form (underscores) or the method will
throw a `\RuntimeException`. When the source ID is a raw JSON key that may
contain hyphens, normalise it first via `IconInfo::normaliseID()`:

```php
$normalisedID = IconInfo::normaliseID($rawID);
$icon = $icons->getByID($normalisedID);
```

`getByID()` throws `\RuntimeException` (not `ApplicationException`) because a
missing ID signals a programmer error — the collection is fully loaded at
construction time, so the caller is responsible for passing a valid ID. To
guard against an unknown ID at runtime, call `idExists()` first:

```php
$normalisedID = IconInfo::normaliseID($rawID);

if($icons->idExists($normalisedID)) {
    $icon = $icons->getByID($normalisedID);
}
```

> **Test coverage:** `tests/AppFrameworkTests/UI/IconCollectionTest.php` covers
> all 7 acceptance criteria with 14 tests and 234 assertions.

---

### `IconInfo` — Read-Only Icon Value Object

`IconInfo` is returned by all `IconCollection` query methods. It is a simple
value object with no side effects.

| Method | Returns | Description |
|---|---|---|
| `getID()` | `string` | Normalised icon ID (underscores) |
| `getIconName()` | `string` | FontAwesome icon name (e.g. `exclamation-triangle`) |
| `getPrefix()` | `string` | FA prefix/type (e.g. `far`, `fas`; empty = default `fa`) |
| `isCustom()` | `bool` | `true` for application custom icons |
| `isStandard()` | `bool` | `true` for framework standard icons; mutually exclusive with `isCustom()` |
| `createIcon()` | `UI_Icon` | Creates a `UI_Icon` with this icon's type pre-configured |
| `getMethodName()` | `string` | camelCase method name for use in code generation (e.g. `time_tracker` → `timeTracker`) |
| `getFullIconName()` | `string` | `prefix:name` when prefix is non-empty, bare `name` otherwise |
| `normaliseID()` _(static)_ | `string` | Canonical ID normaliser — replaces hyphens and spaces with underscores (e.g. `time-tracker` → `time_tracker`) |

---

## Relationship to the Build-Time Layer

The `UI\Icons` namespace is the **runtime** side. The **build-time** counterpart
lives in `Application\Composer\IconBuilder\` and operates on the same JSON
sources but with different value objects:

| Concern | Namespace | Class | Prefix field name |
|---|---|---|---|
| Runtime query | `UI\Icons` | `IconInfo` | `getPrefix()` |
| Build-time codegen | `Application\Composer\IconBuilder` | `IconDefinition` | `getIconType()` |

Both `prefix` and `iconType` refer to the same FontAwesome prefix concept
(e.g. `far`, `fas`). The naming difference is intentional — each class is
named to match its use-context. See
`src/classes/Application/Composer/README.md` for details.
