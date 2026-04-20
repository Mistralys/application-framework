# Work Packages — Icon Builder CLI

Generated from [plan.md](plan.md) on 2026-04-17.

---

## WP-1 — Create IconInfo Value Object

**Description:** Create the `IconInfo` read-only value object in the `UI\Icons` namespace. This is the foundational data type used by `IconCollection` and provides metadata getters plus a factory method for `UI_Icon` instances.

**Scope:**
- `src/classes/UI/Icons/IconInfo.php` (new file — Application Framework)

**Deliverables:**
- `IconInfo` class with constructor receiving `string $id`, `string $iconName`, `string $prefix`, `bool $isCustom`
- Getters: `getID()`, `getIconName()`, `getPrefix()`, `isCustom()`, `isStandard()`, `getMethodName()`, `getFullIconName()`
- Factory method: `createIcon() : UI_Icon` (calls `setType()` with one or two arguments depending on prefix)

**Acceptance Criteria:**
1. The class compiles without errors and follows project conventions (`declare(strict_types=1)`, `array()` syntax, no constructor promotion).
2. `getMethodName()` correctly converts underscore-separated IDs to camelCase.
3. `getFullIconName()` returns `prefix:iconName` when prefix is non-empty, otherwise just `iconName`.
4. `createIcon()` returns a `UI_Icon` with the correct type set (one-arg call when prefix is empty, two-arg call otherwise).
5. `isCustom()` and `isStandard()` return mutually exclusive boolean values matching the `$isCustom` constructor argument.

**Estimated Complexity:** Low

**Notes:** No dependencies on other new classes. Can be implemented first.

---

## WP-2 — Create IconCollection Singleton

**Description:** Create the `IconCollection` singleton class in the `UI\Icons` namespace. Loads framework and application icon JSON files, normalizes IDs, and provides query/iteration methods over `IconInfo` instances.

**Scope:**
- `src/classes/UI/Icons/IconCollection.php` (new file — Application Framework)

**Deliverables:**
- `IconCollection` singleton with `getInstance()` factory
- JSON loading from `APP_INSTALL_FOLDER . '/themes/default/icons.json'` and optional `APP_ROOT . '/themes/custom-icons.json'`
- ID normalization (hyphens/spaces → underscores)
- Custom icon override behavior (same ID replaces standard entry)
- Alphabetical sorting by ID
- Query methods: `getAll()`, `getStandardIcons()`, `getCustomIcons()`, `getByID()`, `idExists()`, `countIcons()`

**Acceptance Criteria:**
1. `getInstance()` returns the same instance on repeated calls.
2. `getAll()` returns `IconInfo[]` sorted alphabetically by ID.
3. `getCustomIcons()` returns only icons where `isCustom()` is `true`.
4. `getStandardIcons()` returns only icons where `isStandard()` is `true`.
5. `getByID()` returns the correct `IconInfo` for a known icon ID.
6. `idExists()` returns `false` for non-existent IDs.
7. When a custom icon has the same ID as a standard icon, the custom icon replaces it.

**Estimated Complexity:** Medium

**Notes:** Depends on WP-1 (IconInfo). Requires `APP_INSTALL_FOLDER` and `APP_ROOT` constants (available at runtime bootstrap).

---

## WP-3 — Create IconDefinition Value Object

**Description:** Port `Migrator_Framework_IconBuilder_IconDef` to the `Application\Composer\IconBuilder` namespace as `IconDefinition`. This is the build-time data object for a single icon definition.

**Scope:**
- `src/classes/Application/Composer/IconBuilder/IconDefinition.php` (new file — Application Framework)

**Deliverables:**
- `IconDefinition` class with the same public API as the original: `getID()`, `getIconName()`, `getIconType()`, `getFullIconName()`, `getConstantName()`
- Proper namespace, `declare(strict_types=1)`, `array()` syntax

**Acceptance Criteria:**
1. The class compiles without errors and passes PHPStan.
2. All getter methods return correct values based on constructor input.
3. `getConstantName()` returns the expected constant-style name.

**Estimated Complexity:** Low

**Notes:** No dependencies on other new classes. Direct port from existing code.

---

## WP-4 — Create IconsReader

**Description:** Port `FrameworkManager\Migrator\Framework\IconBuilder\IconsReader` to the `Application\Composer\IconBuilder` namespace. Parses an `icons.json` file into `IconDefinition[]`.

**Scope:**
- `src/classes/Application/Composer/IconBuilder/IconsReader.php` (new file — Application Framework)

**Deliverables:**
- `IconsReader` class that reads a JSON file path and produces an array of `IconDefinition` objects
- ID normalization (hyphens/spaces → underscores), alphabetical sorting
- Exclusion of the `spinner` icon from code generation

**Acceptance Criteria:**
1. Given a valid `icons.json`, produces the correct number of `IconDefinition` instances.
2. IDs are normalized (hyphens/spaces → underscores).
3. Icons are sorted alphabetically by ID.
4. The `spinner` icon is excluded from the result set.

**Estimated Complexity:** Low

**Notes:** Depends on WP-3 (IconDefinition). Uses `AppUtils\FileHelper\JSONFile` (already a framework dependency).

---

## WP-5 — Create AbstractLanguageRenderer

**Description:** Port `Migrator_Framework_IconBuilder_Language` to `AbstractLanguageRenderer` in the `Application\Composer\IconBuilder` namespace. This is the abstract base for language-specific method code rendering.

**Scope:**
- `src/classes/Application/Composer/IconBuilder/AbstractLanguageRenderer.php` (new file — Application Framework)

**Deliverables:**
- Abstract class with shared rendering logic and abstract methods for PHP/JS-specific code generation
- References changed from `IconDef` to `IconDefinition`

**Acceptance Criteria:**
1. The class compiles without errors and passes PHPStan.
2. Abstract methods are correctly declared for subclass implementation.
3. Shared rendering logic (region markers, method assembly) is functional.

**Estimated Complexity:** Low

**Notes:** Depends on WP-3 (IconDefinition). Direct port from existing code with type reference updates.

---

## WP-6 — Create PHPRenderer and JSRenderer

**Description:** Port the PHP and JS language renderers to the new namespace. `PHPRenderer` extends `AbstractLanguageRenderer` and generates PHP icon methods; `JSRenderer` does the same for JavaScript.

**Scope:**
- `src/classes/Application/Composer/IconBuilder/PHPRenderer.php` (new file — Application Framework)
- `src/classes/Application/Composer/IconBuilder/JSRenderer.php` (new file — Application Framework)

**Deliverables:**
- `PHPRenderer` class that generates PHP method code for each `IconDefinition`
- `JSRenderer` class that generates JS method code for each `IconDefinition`
- PHP region label changed from `Icon type methods` to `Icon methods` (unification with JS convention)

**Acceptance Criteria:**
1. `PHPRenderer` generates method code matching the current output format in `Icon.php` (except the region label change).
2. `JSRenderer` generates method code matching the current output format in `icon.js`.
3. The PHP region label is `// region: Icon methods` (not `Icon type methods`).
4. Both classes compile without errors and pass PHPStan.

**Estimated Complexity:** Medium

**Notes:** Depends on WP-5 (AbstractLanguageRenderer) and WP-3 (IconDefinition). The region label change is intentional and documented in the plan's acceptance criteria.

---

## WP-7 — Create IconBuilder Orchestrator

**Description:** Port `Migrator_Framework_IconBuilder` to `IconBuilder` in the `Application\Composer\IconBuilder` namespace. This is the main orchestrator that reads a JSON source file and performs marker-based code insertion into PHP and JS target files.

**Scope:**
- `src/classes/Application/Composer/IconBuilder/IconBuilder.php` (new file — Application Framework)

**Deliverables:**
- `IconBuilder` class with constructor taking three string paths (JSON source, PHP target, JS target)
- `build() : OperationResult` method that performs marker-based code insertion
- `getIcons() : IconsReader` accessor
- Error code constants: `ERROR_PHP_ICON_FILE_NOT_FOUND`, `ERROR_JS_ICON_FILE_NOT_FOUND`, `ERROR_START_MARKER_NOT_FOUND`
- Marker constants: `/* START METHODS */` and `/* END METHODS */`
- `t()` calls replaced with plain English strings

**Acceptance Criteria:**
1. Given valid JSON, PHP, and JS files with markers, `build()` replaces marker content and returns a successful `OperationResult`.
2. When PHP target file is missing, returns an `OperationResult` error with `ERROR_PHP_ICON_FILE_NOT_FOUND`.
3. When JS target file is missing, returns an `OperationResult` error with `ERROR_JS_ICON_FILE_NOT_FOUND`.
4. When markers are missing in a target file, returns an `OperationResult` error with `ERROR_START_MARKER_NOT_FOUND`.
5. No dependency on `LocalFrameworkClone` or `t()` function.

**Estimated Complexity:** Medium

**Notes:** Depends on WP-4 (IconsReader), WP-6 (PHPRenderer, JSRenderer). Uses `AppUtils\OperationResult` and `AppUtils\FileHelper`.

---

## WP-8 — Wire IconBuilder into Framework ComposerScripts

**Description:** Add the `rebuildIcons()` method to the framework's `ComposerScripts` and integrate it into the `build()` sequence.

**Scope:**
- `src/classes/Application/Composer/ComposerScripts.php` (modify — Application Framework)

**Deliverables:**
- `public static function rebuildIcons() : void` method that creates an `IconBuilder` for framework icon files and calls `build()`
- `self::rebuildIcons()` call added to `build()` after `generateCSSClassesJS()` and before `updateContextGenerateDate()`

**Acceptance Criteria:**
1. `rebuildIcons()` resolves the framework root path and constructs the correct file paths.
2. `rebuildIcons()` calls `self::init()` and echoes progress messages following existing patterns.
3. `build()` includes the icon rebuild step in the correct sequence position.
4. Running `composer rebuild-icons` in the framework produces the expected output.

**Estimated Complexity:** Low

**Notes:** Depends on WP-7 (IconBuilder). Follows the existing `ComposerScripts` pattern — no `do*` split.

---

## WP-9 — Add `rebuild-icons` Composer Script (Framework)

**Description:** Register the `rebuild-icons` script in the framework's `composer.json`.

**Scope:**
- `composer.json` (modify — Application Framework)

**Deliverables:**
- `"rebuild-icons": "Application\\Composer\\ComposerScripts::rebuildIcons"` entry in the `scripts` section

**Acceptance Criteria:**
1. `composer rebuild-icons` invokes `ComposerScripts::rebuildIcons()` successfully.
2. No other scripts or configuration are affected.

**Estimated Complexity:** Low

**Notes:** Depends on WP-8 (rebuildIcons method exists).

---

## WP-10 — Add Markers to HCP Editor CustomIcon.php

**Description:** Add `/* START METHODS */` and `/* END METHODS */` markers to the HCP Editor's `CustomIcon.php` file, wrapping the existing region block.

**Scope:**
- `assets/classes/Maileditor/CustomIcon.php` (modify — HCP Editor)

**Deliverables:**
- `/* START METHODS */` inserted before `// region: Icon type methods`
- `/* END METHODS */` inserted after `// endregion`

**Acceptance Criteria:**
1. The markers are present and correctly wrap the existing icon methods region.
2. The file compiles without errors.
3. The existing generated methods inside the markers are unchanged.

**Estimated Complexity:** Low

**Notes:** One-time file modification. No code logic changes — only marker insertion.

---

## WP-11 — Add Markers to HCP Editor custom-icon.js

**Description:** Add `/* START METHODS */` and `/* END METHODS */` markers to the HCP Editor's `custom-icon.js` file, wrapping the existing region block.

**Scope:**
- `themes/default/js/ui/custom-icon.js` (modify — HCP Editor)

**Deliverables:**
- `/* START METHODS */` inserted before the existing region block
- `/* END METHODS */` inserted after the existing endregion

**Acceptance Criteria:**
1. The markers are present and correctly wrap the existing icon methods region.
2. The file parses without JS syntax errors.
3. The existing generated methods inside the markers are unchanged.

**Estimated Complexity:** Low

**Notes:** One-time file modification. No code logic changes — only marker insertion.

---

## WP-12 — Wire IconBuilder into HCP Editor ComposerScripts

**Description:** Add the `rebuildIcons()` method to the HCP Editor's `ComposerScripts` and integrate it into both `build()` and `buildDEV()` sequences.

**Scope:**
- `assets/classes/Maileditor/Composer/ComposerScripts.php` (modify — HCP Editor)

**Deliverables:**
- `public static function rebuildIcons() : void` method that creates an `IconBuilder` for custom icon files and calls `build()`
- `self::rebuildIcons()` call added to both `build()` and `buildDEV()` after `apiMethodIndex()` and before `updateContext()`

**Acceptance Criteria:**
1. `rebuildIcons()` resolves the application root path and constructs correct file paths for `custom-icons.json`, `CustomIcon.php`, and `custom-icon.js`.
2. `rebuildIcons()` calls `self::initAutoloader()` and echoes progress messages.
3. Both `build()` and `buildDEV()` include the icon rebuild step in the correct position.
4. Running `composer rebuild-icons` in the HCP Editor produces the expected output.

**Estimated Complexity:** Low

**Notes:** Depends on WP-7 (IconBuilder exists in framework). The HCP Editor only rebuilds custom icons — never vendor framework icons.

---

## WP-13 — Add `rebuild-icons` Composer Script (HCP Editor)

**Description:** Register the `rebuild-icons` script in the HCP Editor's `composer.json` and `composer/composer-prod.json`.

**Scope:**
- `composer.json` (modify — HCP Editor)
- `composer/composer-prod.json` (modify — HCP Editor)

**Deliverables:**
- `"rebuild-icons": "Maileditor\\Composer\\ComposerScripts::rebuildIcons"` entry in the `scripts` section of both files

**Acceptance Criteria:**
1. `composer rebuild-icons` invokes `Maileditor\Composer\ComposerScripts::rebuildIcons()` successfully.
2. The script is present in both the main `composer.json` and `composer/composer-prod.json`.

**Estimated Complexity:** Low

**Notes:** Depends on WP-12 (rebuildIcons method exists).

---

## WP-14 — Autoload Dump and Idempotency Verification

**Description:** Run `composer dump-autoload` in the framework to register all new classes, then run `composer rebuild-icons` in both codebases and verify idempotent output.

**Scope:**
- Application Framework (autoload + verification)
- HCP Editor (verification)

**Deliverables:**
- Classmap updated with all new classes
- `composer rebuild-icons` in framework produces no changes to `Icon.php` / `icon.js` (except the region label change on first run)
- `composer rebuild-icons` in HCP Editor produces no changes to `CustomIcon.php` / `custom-icon.js` (except the region label change on first run)

**Acceptance Criteria:**
1. `composer dump-autoload` completes successfully in the framework.
2. Running `composer rebuild-icons` twice in the framework results in zero `git diff` on the second run.
3. Running `composer rebuild-icons` twice in the HCP Editor results in zero `git diff` on the second run.
4. `composer build` in the framework completes without errors.
5. `composer build-dev` in the HCP Editor completes without errors.

**Estimated Complexity:** Low

**Notes:** Depends on all previous WPs. This is the integration verification step.

---

## WP-15 — PHPStan Verification

**Description:** Run `composer analyze` in the framework to ensure all new classes pass PHPStan static analysis.

**Scope:**
- Application Framework (static analysis)

**Deliverables:**
- All new classes in `Application\Composer\IconBuilder\` and `UI\Icons\` pass PHPStan analysis
- No regressions introduced in existing code

**Acceptance Criteria:**
1. `composer analyze` completes with no new errors attributable to the new classes.
2. Any pre-existing PHPStan findings are not worsened.

**Estimated Complexity:** Low

**Notes:** Depends on WP-14 (autoload dump completed). May require iterative fixes.

---

## WP-16 — IconCollection Unit Test

**Description:** Create a unit test for `IconCollection` using the framework's test application, which has `tests/application/themes/custom-icons.json` with test custom icons.

**Scope:**
- New test file in the framework's test suite (Application Framework)

**Deliverables:**
- Test class verifying `IconCollection` behavior with framework + custom icons
- Test cases for: `getAll()`, `getCustomIcons()`, `getStandardIcons()`, `getByID()`, `idExists()`, `createIcon()`

**Acceptance Criteria:**
1. `getAll()` returns both framework icons and the test custom icons.
2. `getCustomIcons()` returns exactly the test custom icons.
3. `getStandardIcons()` returns only framework icons (none of the custom test icons).
4. `getByID('planet')->isCustom()` returns `true` (assuming `planet` is a test custom icon).
5. `getByID('add')->isStandard()` returns `true`.
6. `getByID('add')->createIcon()` returns a `UI_Icon` with the correct type set.
7. `idExists('nonexistent')` returns `false`.

**Estimated Complexity:** Medium

**Notes:** Depends on WP-1 and WP-2. Uses the test application's existing `custom-icons.json`.
