# Plan: Move Icon Builder into the Framework as a CLI Tool

## Summary

Migrate the icon generation logic currently in the appframework-manager (`Migrator_Framework_IconBuilder` and associated classes) into the Application Framework as a first-class Composer build tool. The builder reads icon definitions from JSON files and generates typed PHP methods (in `UI_Icon` / custom icon subclasses) and equivalent JS methods (in `icon.js` / `custom-icon.js`). Once in the framework, both the framework itself and any application can use it to rebuild their icon classes via `composer rebuild-icons` or automatically during `composer build`.

Additionally, introduce a runtime `UI\Icons\IconCollection` class that provides the first official way to query all available icons (both framework defaults and application custom icons), returning `IconInfo` value objects with metadata and factory methods. The icon builder will use the same `IconCollection` as its data source.

## Architectural Context

### Current State (appframework-manager)

The icon generation lives in the **appframework-manager** (external tool, not a Composer dependency):

| Class | Location | Responsibility |
|---|---|---|
| `Migrator_Framework_IconBuilder` | `assets/classes/Migrator/Framework/IconBuilder.php` | Main orchestrator: reads JSON, writes PHP/JS files via marker-based insertion |
| `Migrator_Framework_IconBuilder_IconDef` | `assets/classes/Migrator/Framework/IconBuilder/IconDef.php` | Data object for a single icon definition |
| `FrameworkManager\Migrator\Framework\IconBuilder\IconsReader` | `assets/classes/Migrator/Framework/IconBuilder/IconsReader.php` | Parses an `icons.json` file into `IconDef[]` |
| `Migrator_Framework_IconBuilder_Language` | `assets/classes/Migrator/Framework/IconBuilder/Language.php` | Abstract base for language-specific code generation |
| `Migrator_Framework_IconBuilder_Language_PHP` | `assets/classes/Migrator/Framework/IconBuilder/Language/PHP.php` | PHP method code generation |
| `Migrator_Framework_IconBuilder_Language_JS` | `assets/classes/Migrator/Framework/IconBuilder/Language/JS.php` | JS method code generation |
| `FrameworkManager\Migrator\Framework\IconBuilder\CustomIconsGenerator` | `assets/classes/Migrator/Framework/IconBuilder/CustomIconsGenerator.php` | Generates application custom icon files from templates |

**Two distinct flows exist:**

1. **Framework core icons** — Reads `src/themes/default/icons.json` → inserts generated methods into `src/classes/UI/Icon.php` and `src/themes/default/js/ui/icon.js` using `/* START METHODS */` / `/* END METHODS */` markers.

2. **Application custom icons** — Reads `themes/custom-icons.json` → generates entire `CustomIcon.php` and `custom-icon.js` files from `.tpl` templates via the `Migrator_CodeGenerator` system.

### Framework Target Architecture

The framework already has a `Application\Composer` namespace with build-time code generators:

| Existing Class | File |
|---|---|
| `Application\Composer\ComposerScripts` | `src/classes/Application/Composer/ComposerScripts.php` |
| `Application\Composer\CSSClassesGenerator` | `src/classes/Application/Composer/CSSClassesGenerator.php` |
| `Application\Composer\KeywordGlossary\KeywordGlossaryGenerator` | `src/classes/Application/Composer/KeywordGlossary/` |
| `Application\Composer\ModulesOverview\ModulesOverviewGenerator` | `src/classes/Application/Composer/ModulesOverview/` |

The icon builder will follow this established pattern as `Application\Composer\IconBuilder\`.

### Target Files (Framework)

| File | Marker-based insertion |
|---|---|
| `src/classes/UI/Icon.php` | `/* START METHODS */` … `/* END METHODS */` (already present, line 68 and line 927) |
| `src/themes/default/js/ui/icon.js` | `/* START METHODS */` … `/* END METHODS */` (already present, line 108 and line 328) |
| `src/themes/default/icons.json` | Source JSON (already present) |

### Existing Icon Discovery (runtime)

The framework already has an AJAX method `Application_AjaxMethods_GetIconsReference` (`src/classes/Application/AjaxMethods/GetIconsReference.php`) that loads both icon JSON files and merges them. However, this is a clientside-only endpoint — there is no PHP-level API that returns a typed collection of all available icons. The new `IconCollection` fills this gap.

Icon JSON paths are resolved using the `APP_ROOT` and `APP_INSTALL_FOLDER` constants set at bootstrap time:
- Framework icons: `APP_INSTALL_FOLDER . '/themes/default/icons.json'`
- Application custom icons: `APP_ROOT . '/themes/custom-icons.json'` (optional — may not exist)

### Target Files (HCP Editor — Custom Icons)

| File | Current marker state |
|---|---|
| `assets/classes/Maileditor/CustomIcon.php` | No `/* START METHODS */` markers — uses `// region:` only |
| `themes/default/js/ui/custom-icon.js` | No `/* START METHODS */` markers — uses `// region:` only |
| `themes/custom-icons.json` | Source JSON (already present) |

## Approach / Architecture

### Unified Marker-Based Code Insertion

Both flows (framework core icons and application custom icons) will use the **same marker-based approach**: read a JSON file, render method code, and replace everything between `/* START METHODS */` and `/* END METHODS */` markers in the target PHP and JS files.

This eliminates the template-based generation used by the appframework-manager's `CustomIconsGenerator`. The existing HCP Editor `CustomIcon.php` and `custom-icon.js` files will be updated (one-time) to include the `/* START METHODS */` / `/* END METHODS */` markers.

### Class Design

New namespace: `Application\Composer\IconBuilder\`

| New Class | Responsibility |
|---|---|
| `IconDefinition` | Value object for a single icon (ID, icon name, icon type/prefix). Ported from `Migrator_Framework_IconBuilder_IconDef`. |
| `IconsReader` | Parses an `icons.json` file into `IconDefinition[]`. Ported from `FrameworkManager\Migrator\Framework\IconBuilder\IconsReader`. |
| `AbstractLanguageRenderer` | Abstract base for language-specific method code rendering. Ported from `Migrator_Framework_IconBuilder_Language`. |
| `PHPRenderer` | Renders PHP icon methods. Ported from `Migrator_Framework_IconBuilder_Language_PHP`. |
| `JSRenderer` | Renders JS icon methods. Ported from `Migrator_Framework_IconBuilder_Language_JS`. |
| `IconBuilder` | Main orchestrator: takes a JSON source file and PHP/JS target file paths, performs the marker-based code insertion. Replaces `Migrator_Framework_IconBuilder`. |

### Runtime Icon Collection (UI\Icons)

New namespace: `UI\Icons\`

| New Class | Location | Responsibility |
|---|---|---|
| `IconCollection` | `src/classes/UI/Icons/IconCollection.php` | Singleton collection that loads framework + custom icon JSON files and provides query/iteration methods. |
| `IconInfo` | `src/classes/UI/Icons/IconInfo.php` | Read-only value object for a single icon: ID, FA icon name, FA prefix, source (standard/custom), plus a factory method to create the matching `UI_Icon` instance. |

#### `IconCollection` API Surface

```php
namespace UI\Icons;

class IconCollection
{
    public static function getInstance() : self;

    /**
     * @return IconInfo[]
     */
    public function getAll() : array;

    /**
     * @return IconInfo[]
     */
    public function getStandardIcons() : array;

    /**
     * @return IconInfo[]
     */
    public function getCustomIcons() : array;

    public function idExists(string $iconID) : bool;
    public function getByID(string $iconID) : IconInfo;
    public function countIcons() : int;
}
```

#### `IconInfo` API Surface

```php
namespace UI\Icons;

class IconInfo
{
    public function getID() : string;
    public function getIconName() : string;
    public function getPrefix() : string;
    public function isCustom() : bool;
    public function isStandard() : bool;

    /**
     * Creates a UI_Icon instance with this icon's type pre-configured.
     */
    public function createIcon() : \UI_Icon;

    /**
     * Returns the method name used in the icon classes (camelCase).
     */
    public function getMethodName() : string;
}
```

#### Integration with the Icon Builder

The `IconBuilder` (in `Application\Composer\IconBuilder\`) does **not** use `IconCollection` at runtime. The builder operates on explicit JSON file paths passed to its constructor and is designed for build-time use without requiring the full application bootstrap. It uses its own `IconsReader` to parse JSON files.

`IconCollection` is the runtime counterpart: it provides a queryable registry for application code, UI components, and the `GetIconsReference` AJAX method (which can be refactored to delegate to `IconCollection` in a future step).

Both share the same JSON source format and the same ID normalization rules (hyphens/spaces → underscores, sorted alphabetically).

### Icon Builder API Surface

```php
namespace Application\Composer\IconBuilder;

class IconBuilder
{
    /**
     * @param string $iconsJsonPath Path to the icons.json source file
     * @param string $phpFilePath   Path to the PHP target file
     * @param string $jsFilePath    Path to the JS target file
     */
    public function __construct(
        string $iconsJsonPath,
        string $phpFilePath,
        string $jsFilePath
    );

    /**
     * Rebuilds the icon methods in both target files.
     * @return OperationResult
     */
    public function build() : OperationResult;

    public function getIcons() : IconsReader;
}
```

### Composer Script Integration

**Framework** (`composer.json`):
- New script: `"rebuild-icons": "Application\\Composer\\ComposerScripts::rebuildIcons"`
- Added to the `build` sequence

**HCP Editor** (`composer.json`):
- New script: `"rebuild-icons": "Maileditor\\Composer\\ComposerScripts::rebuildIcons"`
- Added to the `build` and `build-dev` sequences

### How Application Custom Icons Are Discovered

The `ComposerScripts::rebuildIcons()` method in the framework:
1. Resolves the framework root path (from `__DIR__`)
2. Constructs an `IconBuilder` pointing at the framework's own `icons.json`, `Icon.php`, and `icon.js`
3. Calls `build()`

The `ComposerScripts::rebuildIcons()` method in the HCP Editor:
1. Calls the framework's `rebuildIcons()` first (for framework core icons) — **only when running in DEV mode** with the symlinked framework
2. Constructs a second `IconBuilder` pointing at the HCP Editor's `custom-icons.json`, `CustomIcon.php`, and `custom-icon.js`
3. Calls `build()`

> **Note:** When the HCP Editor has the framework installed via Packagist (PROD mode), it should NOT rebuild the framework's icons — those are read-only in `vendor/`. Only in DEV mode (symlinked framework) is rebuilding framework icons relevant, and even then, the framework's own `composer build` is the canonical way to do it. The HCP Editor's `rebuildIcons` will only rebuild *custom* icons by default.

## Rationale

1. **Follows existing patterns.** The framework already has Composer build tools in `Application\Composer\` — the icon builder fits naturally.
2. **Marker-based insertion is simpler.** The template-based approach used by `CustomIconsGenerator` requires the entire `Migrator_CodeGenerator` infrastructure. Marker-based insertion only requires string manipulation and avoids that dependency.
3. **Self-contained.** The icon builder has no dependencies outside of `AppUtils` (which is already a framework dependency). No dependency on the appframework-manager.
4. **Decouples from the appframework-manager.** The appframework-manager was the only way to rebuild icons. Now the framework and applications can do it independently.

## Detailed Steps

### Step 1: Create the IconCollection and IconInfo Classes

Create the following files under `src/classes/UI/Icons/`:

1. **`IconInfo.php`** — Read-only value object in the `UI\Icons` namespace. Constructor receives: `string $id`, `string $iconName`, `string $prefix`, `bool $isCustom`. Provides getters for all properties plus:
   - `createIcon() : UI_Icon` — creates a new `UI_Icon` and calls `setType($this->iconName, $this->prefix)` on it. **Note:** when `$this->prefix` is empty, call `setType($this->iconName)` with one argument only (matching the generated method pattern, where unprefixed icons omit the second parameter).
   - `getMethodName() : string` — converts the underscore-separated ID to camelCase (same logic as `PHPRenderer::getMethodName()`).
   - `getFullIconName() : string` — returns `prefix:iconName` if prefix is not empty, otherwise just `iconName`.

2. **`IconCollection.php`** — Singleton in the `UI\Icons` namespace. On first access:
   - Loads the framework's `icons.json` from `APP_INSTALL_FOLDER . '/themes/default/icons.json'`.
   - Loads the application's `custom-icons.json` from `APP_ROOT . '/themes/custom-icons.json'` (if it exists).
   - Normalizes IDs (hyphens/spaces → underscores) and creates `IconInfo` instances with the `$isCustom` flag set accordingly.
   - Custom icons with the same ID as a standard icon override the standard entry (application can override framework icons).
   - Sorts all icons alphabetically by ID.
   - Provides query methods: `getAll()`, `getStandardIcons()`, `getCustomIcons()`, `getByID()`, `idExists()`, `countIcons()`.

### Step 2: Create IconBuilder Classes in the Framework

Create the following files under `src/classes/Application/Composer/IconBuilder/`:

1. **`IconDefinition.php`** — Port `Migrator_Framework_IconBuilder_IconDef` to the `Application\Composer\IconBuilder` namespace. Add proper namespace declaration and `declare(strict_types=1)`. Use `array()` syntax. Keep the same public API: `getID()`, `getIconName()`, `getIconType()`, `getFullIconName()`, `getConstantName()`.

2. **`IconsReader.php`** — Port `FrameworkManager\Migrator\Framework\IconBuilder\IconsReader` to the new namespace. Adjust the `use` import for `IconDefinition`.

3. **`AbstractLanguageRenderer.php`** — Port `Migrator_Framework_IconBuilder_Language`. Change `IconDef` references to `IconDefinition`.

4. **`PHPRenderer.php`** — Port `Migrator_Framework_IconBuilder_Language_PHP`. Extends `AbstractLanguageRenderer`. **Region unification:** change the region label from the original `Icon type methods` to `Icon methods` so that both PHP and JS renderers use the same region text (`// region: Icon methods`).

5. **`JSRenderer.php`** — Port `Migrator_Framework_IconBuilder_Language_JS`. Extends `AbstractLanguageRenderer`. The JS renderer already uses `// region: Icon methods` — no change needed.

6. **`IconBuilder.php`** — Port `Migrator_Framework_IconBuilder`, removing the `LocalFrameworkClone` dependency. Constructor takes three string paths (JSON source, PHP target, JS target). The marker constants and `insertIconCode()` logic remain the same. **Error codes:** port the original error code constants (`ERROR_PHP_ICON_FILE_NOT_FOUND = 82301`, `ERROR_JS_ICON_FILE_NOT_FOUND = 82302`, `ERROR_START_MARKER_NOT_FOUND = 82303`) to the new class and use them with `OperationResult::makeError()`.

### Step 3: Wire into Framework ComposerScripts

In `src/classes/Application/Composer/ComposerScripts.php`:

1. Add a `public static function rebuildIcons() : void` method that:
   - Calls `self::init()`
   - Resolves the framework root path: `$rootPath = realpath(__DIR__ . '/../../../../');`
   - Creates an `IconBuilder` with the framework's file paths:
     - JSON: `$rootPath . '/src/themes/default/icons.json'`
     - PHP: `$rootPath . '/src/classes/UI/Icon.php'`
     - JS: `$rootPath . '/src/themes/default/js/ui/icon.js'`
   - Calls `build()` and logs results
   - Echoes progress messages following the existing pattern (`echo '- Rebuilding framework icons...'`)
   - **Note:** do NOT split into `rebuildIcons()` + `doRebuildIcons()`. The existing `ComposerScripts` pattern has every method call `self::init()` and do its work directly (see `clearCaches()`, `apiMethodIndex()`, etc.). Follow the same convention.

2. Add `self::rebuildIcons()` to the `build()` method sequence, **after** `self::generateCSSClassesJS()` and **before** `self::updateContextGenerateDate()`. This places the icon rebuild alongside the other code generation steps.

### Step 4: Add Standalone Composer Script (Framework)

In `composer.json`, add to the `scripts` section:

```json
"rebuild-icons": "Application\\Composer\\ComposerScripts::rebuildIcons"
```

### Step 5: Add Markers to HCP Editor Custom Icon Files

**`assets/classes/Maileditor/CustomIcon.php`** — Add `/* START METHODS */` before the `// region:` block and `/* END METHODS */` after the `// endregion` block:

```php
class CustomIcon extends UI_Icon
{
    /* START METHODS */

    // region: Icon type methods
    
    // ... existing generated methods ...
    
    // endregion

    /* END METHODS */
}
```

**`themes/default/js/ui/custom-icon.js`** — Same pattern, add `/* START METHODS */` and `/* END METHODS */` around the existing region block.

### Step 6: Wire into HCP Editor ComposerScripts

In `assets/classes/Maileditor/Composer/ComposerScripts.php`:

1. Add a `public static function rebuildIcons() : void` method that:
   - Calls `self::initAutoloader()` (this is `private static` in HCP Editor's ComposerScripts, unlike the framework's `public static init()` — both are valid for internal use)
   - Resolves the application root: `$rootPath = realpath(__DIR__ . '/../../../../');`
   - Creates an `IconBuilder` with the custom icon file paths:
     - JSON: `$rootPath . '/themes/custom-icons.json'`
     - PHP: `$rootPath . '/assets/classes/Maileditor/CustomIcon.php'`
     - JS: `$rootPath . '/themes/default/js/ui/custom-icon.js'`
   - Calls `build()` and logs results

2. Add `self::rebuildIcons()` to both the `build()` and `buildDEV()` method sequences, **after** `apiMethodIndex()` and **before** `updateContext()`. This places the icon rebuild alongside the other code generation steps.

### Step 7: Add Standalone Composer Script (HCP Editor)

In the HCP Editor's `composer.json` (and `composer/composer-prod.json`), add:

```json
"rebuild-icons": "Maileditor\\Composer\\ComposerScripts::rebuildIcons"
```

### Step 8: Run `composer dump-autoload` (Framework)

New class files require classmap autoloading to be refreshed.

### Step 9: Verify with Tests

1. **Framework:** Run `composer rebuild-icons` — verify `Icon.php` and `icon.js` are unchanged (idempotent rebuild).
2. **HCP Editor:** Run `composer rebuild-icons` — verify `CustomIcon.php` and `custom-icon.js` are unchanged after the marker addition.
3. **Framework:** Run `composer build` — confirm icons are rebuilt as part of the full build.
4. **HCP Editor:** Run `composer build-dev` — confirm custom icons are rebuilt as part of the build.

## Dependencies

- `AppUtils\FileHelper\JSONFile` — already a framework dependency (used to parse the JSON files)
- `AppUtils\OperationResult` — already a framework dependency (used for build result reporting)
- `AppUtils\FileHelper` — already a framework dependency (file read/write)

No new external dependencies required.

## Required Components

### Framework (new files)

- `src/classes/UI/Icons/IconInfo.php` (new) — runtime icon value object
- `src/classes/UI/Icons/IconCollection.php` (new) — runtime singleton icon registry
- `src/classes/Application/Composer/IconBuilder/IconDefinition.php` (new)
- `src/classes/Application/Composer/IconBuilder/IconsReader.php` (new)
- `src/classes/Application/Composer/IconBuilder/AbstractLanguageRenderer.php` (new)
- `src/classes/Application/Composer/IconBuilder/PHPRenderer.php` (new)
- `src/classes/Application/Composer/IconBuilder/JSRenderer.php` (new)
- `src/classes/Application/Composer/IconBuilder/IconBuilder.php` (new)

### Framework (modified files)

- `src/classes/Application/Composer/ComposerScripts.php` — add `rebuildIcons()` + wire into `build()`
- `composer.json` — add `rebuild-icons` script

### HCP Editor (modified files)

- `assets/classes/Maileditor/CustomIcon.php` — add `/* START METHODS */` / `/* END METHODS */` markers
- `themes/default/js/ui/custom-icon.js` — add `/* START METHODS */` / `/* END METHODS */` markers
- `assets/classes/Maileditor/Composer/ComposerScripts.php` — add `rebuildIcons()` + wire into `build()`/`buildDEV()`
- `composer.json` — add `rebuild-icons` script
- `composer/composer-prod.json` — add `rebuild-icons` script

## Assumptions

- The `icons.json` and `custom-icons.json` formats remain unchanged (object with icon IDs as keys, each having `icon` and `type` properties).
- The `/* START METHODS */` / `/* END METHODS */` marker convention is stable and will not change.
- The `spinner` icon exclusion from code generation (present in the original builder) should be preserved.
- The `OperationResult` return value is sufficient for error reporting in both standalone and build contexts.
- **The HCP Editor is in DEV mode** (`composer.json.DEV` marker present, `composer switch-dev` has been run, `composer update` completed). The framework is symlinked into the HCP Editor's `vendor/mistralys/application_framework/`, so new framework classes are immediately available to the HCP Editor without publishing.

## Constraints

- All new PHP files must use `declare(strict_types=1)`.
- All new PHP files must use the appropriate namespace: `Application\Composer\IconBuilder` for builder classes, `UI\Icons` for runtime collection classes.
- Array syntax must be `array()`, never `[]`.
- No constructor promotion.
- Run `composer dump-autoload` after adding the new class files (classmap autoloading).
- The `t()` translation function used in the original builder's error messages must be replaced with plain strings, since the ComposerScripts context may not have the full application localization booted. Follow the pattern used by other ComposerScripts methods (plain `echo` messages).

## Out of Scope

- Removing the icon builder from the appframework-manager. It can be deprecated there later.
- Adding unit tests for the icon builder classes. The builder's correctness is validated by running it and checking the output (idempotency test). A formal test recommendation is made below.
- Changing the `icons.json` or `custom-icons.json` format.
- Adding new icons to either JSON file.
- Modifying the generated method code format (the PHP/JS templates remain identical to the current output), with the exception of the region label unification (see Step 2, item 4).
- Updating the appframework-manager to delegate to the framework's builder.

## Acceptance Criteria

1. Running `composer rebuild-icons` in the **framework** reads `icons.json` and regenerates the methods in `Icon.php` and `icon.js` with identical output.
2. Running `composer rebuild-icons` in the **HCP Editor** reads `custom-icons.json` and regenerates the methods in `CustomIcon.php` and `custom-icon.js` with identical output.
3. Running `composer build` in the **framework** includes the icon rebuild step.
4. Running `composer build-dev` in the **HCP Editor** includes the custom icon rebuild step.
5. All generated method code is byte-identical to the current output (ensures no formatting regressions), with the exception that PHP region labels change from `// region: Icon type methods` to `// region: Icon methods` for consistency with JS.
6. The builder reports errors via `OperationResult` when source files or markers are missing.
7. No new Composer dependencies are introduced.
8. All new classes pass PHPStan analysis.
9. `IconCollection::getInstance()->getAll()` returns all icons (framework + custom) with correct metadata.
10. `IconCollection::getInstance()->getByID('add')->createIcon()` returns a `UI_Icon` with the correct type set.
11. `IconCollection::getInstance()->getCustomIcons()` returns only custom icons.
12. `IconCollection::getInstance()->getStandardIcons()` returns only framework icons.

## Testing Strategy

1. **Idempotency verification:** Run `composer rebuild-icons` twice and confirm no file changes on the second run (use `git diff` to verify).
2. **Build integration:** Run `composer build` in the framework and `composer build-dev` in the HCP Editor; confirm the build completes without errors and icon files are current.
3. **Error handling:** Temporarily rename the JSON source file and run `composer rebuild-icons`; confirm a clear error message is produced.
4. **PHPStan:** Run `composer analyze` in the framework after adding the new classes.
5. **IconCollection unit test:** Create a test that boots the test application (which has `tests/application/themes/custom-icons.json` with 2 custom icons) and verifies:
   - `getAll()` returns framework icons + 2 custom icons.
   - `getCustomIcons()` returns exactly the 2 test custom icons (`planet`, `revisionable`).
   - `getStandardIcons()` returns only framework icons.
   - `getByID('planet')->isCustom()` is `true`.
   - `getByID('add')->isStandard()` is `true`.
   - `getByID('add')->createIcon()` returns a `UI_Icon` with the correct type.
   - `idExists('nonexistent')` returns `false`.
6. **Recommendation for future:** Add a unit test that reads the JSON, generates methods, and asserts against a known-good snapshot.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **Generated output differs from current** | The renderers are direct ports of the existing code with one intentional change: the PHP region label is unified from `Icon type methods` to `Icon methods` to match the JS convention. Byte-comparison against current files will catch any unintentional drift. |
| **Breaking the build pipeline** | The icon rebuild step is additive; existing build steps are unchanged. If `rebuild-icons` fails, it returns an `OperationResult` error without stopping other build steps. |
| **Missing markers in custom icon files** | Step 4 explicitly adds the markers. The builder validates marker presence and reports a clear error if missing. |
| **`t()` function not available in Composer context** | Replace `t()` calls with plain English strings in error messages, following the pattern of existing ComposerScripts methods. |
| **DEV/PROD confusion for framework icons** | The HCP Editor's `rebuildIcons()` only rebuilds custom icons, never vendor framework icons. Framework icon rebuilding is only done via the framework's own `composer rebuild-icons`. |
| **IconCollection used before bootstrap** | `IconCollection` requires `APP_INSTALL_FOLDER` and `APP_ROOT` constants. These are always defined at bootstrap. The builder does NOT depend on `IconCollection`, so the build-time path is unaffected. |
| **Custom icons override standard icons** | Documented and intentional behavior — applications can override framework icon definitions. `IconInfo::isCustom()` returns `true` for overrides. |
