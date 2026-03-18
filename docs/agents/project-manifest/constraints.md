# Constraints & Conventions

Project-specific rules, conventions, and non-obvious gotchas for the Application Framework codebase.
For detailed examples and patterns, refer to the [agent documentation](/docs/agents/).

---

## PHP Version & Language Features

- **Minimum PHP version:** 8.4+ (`"php": "^8.4"` in `composer.json`).
- **Strict typing is the standard.** All new files must use `declare(strict_types=1);`.
- **No PHP enums.** Enum-like patterns are implemented via class constants and dedicated type classes.
- **No `readonly` properties.** Prefer conventional private/protected properties with getter methods.

---

## Namespace Migration (Ongoing)

The codebase is transitioning from a legacy pre-namespace class naming convention to fully namespaced classes.

### Legacy Convention (being phased out)

Legacy classes use **underscore-separated names** that mirror the directory path:

```php
class Application_Admin_Area_Settings extends Application_Admin_Area
```

These classes have **no `namespace` declaration**.

### Modern Convention (standard for new code)

New classes must use proper **PHP namespaces** matching the directory structure:

```php
namespace Application\Composer\ModulesOverview;

class ModulesOverviewGenerator
```

### Migration Status

- **~1,031 files use namespaces** (67%).
- **~514 files still use the legacy convention** (33%).
- Legacy classes are concentrated in older modules: Admin screens, AjaxMethods, DBHelper UI, and various Application subsystems.
- Migration is incremental — legacy classes may reference namespaced classes and vice versa.

### Autoloading

The project uses Composer's **classmap** autoloading (not PSR-4). This means both legacy and namespaced classes coexist without autoloader issues, but it requires running `composer dump-autoload` after adding or renaming files.

```json
"autoload": {
    "classmap": [
        "src/classes/",
        "src/themes/default/templates",
        "tests/application/assets/classes",
        "tests/AppFrameworkTestClasses"
    ]
}
```

---

## Array Syntax

**Always use the verbose `array()` syntax** for array initialization. Do not use short array syntax `[]` for creating arrays.

```php
// Correct
$data = array('key' => 'value');
$list = array();

// Wrong
$data = ['key' => 'value'];
$list = [];
```

Note: Square brackets for array access (`$arr['key']`, `$arr[] = $val`) are fine — this rule only applies to array **creation**.

---

## Naming Conventions

| Context | Convention | Example |
|---|---|---|
| PHP methods/properties | camelCase | `getLabel()`, `$campaignManager` |
| Database columns | snake_case | `record_id`, `created_at` |
| Class names | PascalCase | `AppFactory`, `DBHelper_BaseCollection` |
| Constants | UPPER_SNAKE_CASE | `ERROR_NO_DATA_AVAILABLE` |
| Short acronyms ("ID", "URL") | Always uppercase | `getID()`, `getUserID()` |
| Exception classes | Suffixed with `Exception` | `ApplicationException`, `ConnectorException` |
| Filter criteria classes | Named `FilterCriteria` or suffixed | `DBHelper_BaseFilterCriteria` |

---

## Class Architecture Patterns

### AppFactory

`AppFactory` is the **central factory class** for the framework. It provides static factory methods for all major collections and services:

```php
$media = AppFactory::createMedia();
$users = AppFactory::createUsers();
$countries = AppFactory::createCountries();
```

Application projects extend `AppFactory` into their own `ClassFactory` to register additional factory methods. All factory methods follow the `create{ModuleName}()` naming convention.

### Database Records & Collections

The framework provides a Collection/Record pattern for database entities:

- **Collections** extend `DBHelper_BaseCollection` or `BaseRevisionableCollection` — responsible for CRUD operations and querying.
- **Records** extend `DBHelper_BaseRecord` — represent individual database rows.
- **FilterCriteria** extend `DBHelper_BaseFilterCriteria` or `Application_FilterCriteria` — provide typed query building for collections.

### Type Checking

Use `ClassHelper::requireObjectInstanceOf()` for type assertions instead of manual `instanceof` checks with exceptions:

```php
use AppUtils\ClassHelper;

$record = ClassHelper::requireObjectInstanceOf(
    SomeRecord::class,
    $rawRecord
);
```

### Lazy Initialization

Use `isset()` to guard lazy initialization of nullable properties:

```php
private ?SomeManager $manager = null;

public function getManager(): SomeManager
{
    if (!isset($this->manager)) {
        $this->manager = new SomeManager();
    }
    return $this->manager;
}
```

---

## Exception Handling

### Exception Structure

All exceptions take four parameters in this order:

1. **User-facing message** — No technical details, safe to display.
2. **Developer details** — Full context for debugging (sprintf-formatted).
3. **Error code** — Unique integer constant.
4. **Previous exception** — For exception chaining.

### Error Code Generation

Error codes are **globally unique integers** obtained from a dedicated error code service. The format is `{unique_id}{error_number}`, e.g., `505001` where `505` is the class identifier and `001` is the error number (always 3 digits, zero-padded).

Error codes are defined as `public const ERROR_*` constants in the relevant exception or class:

```php
public const ERROR_NO_DATA_AVAILABLE = 44201;
public const ERROR_COULD_NOT_ENCODE_JSON = 44202;
```

### Exception Hierarchy

Module-specific exceptions extend the application's base exception, which extends `Application_Exception`:

```
Application_Exception
└── ApplicationException (namespaced alias)
    └── ModuleException
```

### Control Flow

Return success values early; throw exceptions at the end of the method (keep the "happy path" un-nested).

---

## Array Handling

Use `ArrayDataCollection` for type-safe access to associative array data:

```php
use AppUtils\ArrayDataCollection;

$data = ArrayDataCollection::create(array(
    'name' => 'John Doe',
    'integer' => 45,
));

$name = $data->getString('name');
$age = $data->getInt('integer');
```

---

## File & JSON Handling

- Use `FileInfo::factory()` / `FolderInfo::factory()` for filesystem operations.
- Use `JSONFile::factory()` for JSON file I/O.
- Use `JSONConverter::var2json()` / `JSONConverter::json2array()` instead of raw `json_encode()` / `json_decode()`.

---

## UI Localization

- Wrap user-facing strings with `t()` from `AppLocalize`.
- Use numbered `sprintf`-style placeholders: `t('Welcome, %1$s!', $username)`.
- Use `tex()` when translator context is needed for ambiguous placeholders.
- Systematically split sentences into multiple `t()` calls for maximum reuse.

---

## Event System

Modules register behavior through **event listeners** extending framework base listener classes. The framework supports:

- **Global events** — application-wide via `EventHandler`.
- **Instance-scoped events** — via the `Eventable` trait on objects.
- **Offline events** — just-in-time listeners discovered and registered at build time.

Listener classes are stored in an `Events/` subdirectory within their module folder.

---

## API Methods

Public API methods extend `Application_API_Method` and follow a composition pattern with interfaces and traits for request/response handling.

API method classes are stored in `API/Methods/` subdirectories within their module folder.

**Method names must be PascalCase** — the `.htaccess` rewrite rule generated by the OpenAPI submodule uses the regex `^([A-Za-z][A-Za-z0-9]*)$`. Method names containing underscores, hyphens, or dots will not be rewritten and will return 404 on the clean-path URL.

---

## Trait Consumer Policy

### Rule

**Never add `trait.unused` suppressions to `phpstan.neon`.** PHPStan's `trait.unused` notice indicates that a trait exists with no concrete consumer in the analysed codebase. The correct response is to **create a test-application consumer class**, not to suppress the notice.

### Why suppression is harmful

`trait.unused` suppression disables PHPStan's analysis of the **entire trait method body** for the duration of the suppression. This creates a static-analysis blind spot: type errors and logic bugs inside the trait's methods become invisible to the analyser. Every suppressed trait is untested dead code from PHPStan's perspective.

### What to do instead

When a library trait has no consumer in the main codebase:

1. Create a minimal screen or class in `tests/application/assets/classes/TestDriver/` that:
   - Implements the corresponding interface (if one exists).
   - Uses the trait.
   - Provides only the minimal stubs required to satisfy interface contracts.
2. Run `composer dump-autoload` so the classmap picks up the new file.
3. Verify with `composer analyze` that the `trait.unused` notice is gone and no new errors are introduced.

### Example

See [tests/application/assets/classes/TestDriver/Area/TestingScreen/CountryRequestScreen.php](../../../tests/application/assets/classes/TestDriver/Area/TestingScreen/CountryRequestScreen.php) as a reference implementation of this pattern for `CountryRequestTrait`.

