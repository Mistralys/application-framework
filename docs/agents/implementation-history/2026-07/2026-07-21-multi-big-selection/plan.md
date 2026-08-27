# Plan

## Plan Audit Cycles
- Audits: 4 — Plan Auditor v1.6.0
- Architectural Reviews: 1 (findings integrated) — Plan Architect Reviewer v2.1.0

## Summary

Add a multi-select mode to the `BigSelectionWidget` that allows checkable items alongside the existing regular, header, and separator item types. Checkable items render with checkboxes, use the existing `active` CSS state for visual feedback, and emit their values as a standard indexed array form variable. The containing form is the caller's responsibility; the widget provides `setFormName()` for naming, hidden `<input>` elements for value transport, and a lazy request getter for reading submitted values.

## Architectural Context

The `BigSelectionWidget` is a presentation-layer component in `src/classes/UI/Bootstrap/BigSelection/`. It extends `UI_Bootstrap` and manages a heterogeneous list of `BaseItem` children (`RegularItem`, `HeaderItem`, `SeparatorItem`). Each item type is a separate class under `Item/` with its own `_render()` method producing inline HTML via `OutputBuffering`. CSS class constants are centralized in `BigSelectionCSS`. The widget template (`big-selection.php`) iterates all children calling `$item->display()` — new item types are rendered automatically. Client-side behavior lives in `src/themes/default/js/ui/bigselection/` with a jQuery-based class pattern. Request values are read via `AppFactory::createRequest()->getParam()`.

## Approach / Architecture

1. **New item type**: Add `CheckableItem` extending `BaseItem` (following the established one-class-per-type pattern under `Item/`). It renders as a `<li>` with a hidden `<input type="checkbox">` and a clickable anchor that toggles checked state via JavaScript. The hidden input's `name` uses the array syntax (`formName[]`) and its `value` is the item's value. The checkbox is visually represented by a checkbox icon prefixed to the label, toggled between checked/unchecked states.

2. **Form name on widget**: Add `setFormName(string)` and optional `$formName` parameter to `createBigSelection()` on `UI`. The form name is stored as an option on `BigSelectionWidget` and propagated to `CheckableItem` instances at render time.

3. **Value resolution**: The widget provides `getSubmittedValues(): array` which lazily reads from `AppFactory::createRequest()->getParam($formName)`, validates each value against the set of registered checkable item values, and returns only valid entries. This method works regardless of call order — it resolves values at call time, not at registration time.

4. **JavaScript handler**: A new `UI_BigSelection_Checkable` JS class toggles the `active` CSS class and the hidden input's `disabled`/checked state on click. Unchecked items have their hidden input `disabled` so they are not submitted.

5. **CSS**: Minimal additions — the checkbox icon and any checkbox-specific spacing. The `active` state styling is already defined and reused as-is.

## Rationale

- **Extending the existing item hierarchy** is the natural path — each item type is already its own class. A `CheckableItem` fits this pattern without modifying existing types.
- **Hidden inputs per item** (disabled when unchecked) is simpler than a single hidden field managed via JS. It works without JavaScript for the initial state and degrades gracefully.
- **Form ownership is external** — the widget does not render `<form>` tags. This preserves the widget's composability and avoids nested-form issues.
- **Lazy value resolution** avoids ordering constraints. The getter reads from the request and validates at call time, so it works whether called before or after items are added, as long as items are added before the result is used for logic.

## Considered Alternatives

| Decision | Chosen Shape | Alternatives Considered | Trade-Off Summary |
|----------|--------------|-------------------------|-------------------|
| Checked state transport | Hidden `<input>` per item (disabled when unchecked) | Single hidden field with JSON array updated by JS; visible `<input type="checkbox">` | Hidden-per-item works without JS for initial state and avoids JSON serialization complexity. Visible checkboxes break the existing `<a>` anchor markup pattern. |
| Checkbox visual | Icon prefix in label span (e.g. checkbox icon toggled via JS) | Actual `<input type="checkbox">` before anchor | Keeps the `<li>` → `<a>` markup pattern consistent with `RegularItem`. Actual checkbox elements would require restructuring the anchor layout. |
| Value getter ordering | Lazy resolution at call time | Eager resolution requiring items-first ordering | Lazy eliminates the fragile ordering constraint entirely. Minimal cost since it just reads `$_REQUEST` via the request abstraction. |
| `makeSelected()` method | Alias for `makeActive()` on `CheckableItem` | Separate CSS class | Reusing `active` gives consistent visual treatment with zero CSS additions. `makeSelected()` as a semantic alias is more intuitive for the checkable context. |

## Pattern Alignment

- **One class per item type** under `Item/` extending `BaseItem` — follows `RegularItem`, `HeaderItem`, `SeparatorItem` pattern. `src/classes/UI/Bootstrap/BigSelection/Item/`
- **CSS constants in `BigSelectionCSS`** — new constants added there. `src/classes/UI/Bootstrap/BigSelection/BigSelectionCSS.php`
- **Private factory method in widget** — `createCheckableItem()` follows `createRegularItem()` pattern. `src/classes/UI/Bootstrap/BigSelection/BigSelectionWidget.php`
- **JS class with `window.` export** — follows `UI_BigSelection_Static` pattern. `src/themes/default/js/ui/bigselection/static.js`
- **Request access via `AppFactory::createRequest()->getParam()`** — follows `ButtonGroup`, `Tabs` pattern.
- **Example templates in subfolders** with `code.php`, `description.md`, `example.json` — follows existing examples in `src/themes/default/templates/appinterface/selection-lists/`. Three separate subdirectories cover: basic usage, pre-selected items, and mixed item types.

## Detailed Steps

### Step 1: Add CSS constants to `BigSelectionCSS`

**File:** `src/classes/UI/Bootstrap/BigSelection/BigSelectionCSS.php`

Add new constants:

```php
public const string ITEM_CHECKABLE = 'bigselection-checkable';
public const string CHECKBOX_ICON = 'bigselection-checkbox';
public const string RESOURCES_JS_CHECKABLE = 'ui/bigselection/checkable.js';
```

### Step 2: Create the `CheckableItem` class

**File (new):** `src/classes/UI/Bootstrap/BigSelection/Item/CheckableItem.php`

Extends `BaseItem`. Key responsibilities:

- Stores `$label` (string), `$value` (string), `$description` (optional string).
- `setValue(string): self` — sets the form value this item represents.
- `getValue(): string` — returns the value.
- `setLabel($label): self` — sets the display label.
- `setDescription($text): self` — sets optional description text.
- `makeSelected(): self` — adds `BigSelectionCSS::STATE_ACTIVE` class (alias for the active visual state, also sets the hidden input to enabled/checked for server-side pre-selection).
- `isSelected(): bool` — checks if the active class is present.
- `resolveSearchWords(): string` — returns label + description text (for filtering compatibility).
- `_render(): string` — produces:

```html
<li class="bigselection-entry bigselection-checkable" data-terms="...">
    <input type="hidden" name="formName[]" value="itemValue" disabled>
    <a href="javascript:void(0)" class="bigselection-anchor">
        <span class="bigselection-checkbox">{checkbox-icon}</span>
        <span class="bigselection-label">{icon} {label}</span>
        <span class="bigselection-description">{description}</span>
    </a>
</li>
```

When `makeSelected()` has been called, the `<li>` also gets the `active` class and the hidden input is **not** disabled (so it submits).

The `formName` is obtained from the parent widget via `$this->parent->getFormName()`.

### Step 3: Add form name and checkable item support to `BigSelectionWidget`

**File:** `src/classes/UI/Bootstrap/BigSelection/BigSelectionWidget.php`

Add:

- **Option constant:** `public const string OPTION_FORM_NAME = 'formName';`
- **`setFormName(string $name): self`** — stores the form name as an option.
- **`getFormName(): string`** — pure getter; returns the stored form name (empty string if not set). Never throws.
- **`hasFormName(): bool`** — checks if a form name is set.
- **`addCheckable(string|int|float|UI_Renderable_Interface $label, string $value): CheckableItem`** — creates a `CheckableItem`, sets its label and value, appends it. Returns the item for fluent configuration.
- **`prependCheckable(string|int|float|UI_Renderable_Interface $label, string $value): CheckableItem`** — same but prepends.
- **`getCheckableItems(): array`** — returns only `CheckableItem` instances from children.
- **`hasCheckableItems(): bool`** — whether any checkable items exist.
- **`getSubmittedValues(): array`** — lazy value getter:
  1. If no form name set, returns empty array.
  2. Reads `AppFactory::createRequest()->getParam($formName)` → expects array or null.
  3. If not an array, returns empty array.
  4. Collects all registered checkable item values into a valid-values set.
  5. Filters the submitted values: only strings present in the valid-values set are kept.
  6. Returns the filtered array (re-indexed).
  
  The PHPDoc for this method must explicitly document that it validates submitted values against currently registered checkable items, so it must be called after all checkable items have been added. Calling it before all items are registered may cause valid submitted values to be silently discarded.
- **Private factory:** `createCheckableItem(string $label, string $value): CheckableItem`.

**`_render()` validation guard:** Immediately after the existing empty-children guard in `_render()`, add:

```php
if ($this->hasCheckableItems() && !$this->hasFormName()) {
    throw new Application_Exception(
        'BigSelection checkable items require a form name',
        'Call setFormName() before rendering a BigSelection that contains checkable items.',
        self::ERROR_FORM_NAME_REQUIRED
    );
}
```

Add the error code constant to the widget class:

```php
public const int ERROR_FORM_NAME_REQUIRED = {classID}001;
```

> **Implementation note:** Obtain a unique class identifier for `BigSelectionWidget` from the project's error code service before assigning this constant. The value must be a globally unique multi-digit integer following the project's `{class_id}{error_number}` format (e.g. `159301`). Do **not** use `1` or any other low integer — those values collide with the countless other error codes across the project.

Update `getDefaultOptions()` to include `self::OPTION_FORM_NAME => ''`.

### Step 4: Update the template for validation and JS loading

**File:** `src/themes/default/templates/ui/bootstrap/big-selection.php`

In `generateOutput()`:

- After resolving items, if the widget `hasCheckableItems()`:
  - Call `$this->selection->getFormName()` to retrieve the form name for the JS initializer (validation has already occurred in `_render()` before the template is invoked).
  - Add the checkable JS: `$this->ui->addJavascript(BigSelectionCSS::RESOURCES_JS_CHECKABLE)`.
  - Add JS onload: `(new UI_BigSelection_Checkable('{$jsID}')).Start()`.

### Step 5: Create the checkable JS handler

**File (new):** `src/themes/default/js/ui/bigselection/checkable.js`

`UI_BigSelection_Checkable` class:

- Constructor takes `elementID`.
- `Start()`: Binds click handler to all `.bigselection-checkable .bigselection-anchor` elements within the widget.
- Click handler toggles:
  - The `active` CSS class on the parent `<li>`.
  - The `disabled` attribute on the sibling hidden `<input>` (disabled = unchecked, enabled = checked).
  - The checkbox icon between checked and unchecked states.
- Uses `window.UI_BigSelection_Checkable = UI_BigSelection_Checkable` for global scope.

### Step 6: Add CSS for checkable items

**File:** `src/themes/default/css/ui-bigselection.css`

Add:

```css
.bigselection .bigselection-checkable .bigselection-checkbox {
    margin-right: 8px;
    display: inline-block;
    vertical-align: middle;
}
```

The `active` state styling is already defined for `.bigselection-entry.active` and applies automatically.

### Step 7: Update `UI::createBigSelection()` to accept optional form name

**File:** `src/classes/UI/UI.php`

Update the factory method signature:

```php
public function createBigSelection(string $formName = ''): BigSelectionWidget
```

If `$formName` is non-empty, call `setFormName()` on the created instance before returning.

### Step 8: Add example templates

Create three separate example subdirectories, each with the standard three-file structure (`code.php`, `description.md`, `example.json`). The `example.json` in each directory must contain only a `title` key — no `description` field (follows existing convention).

---

#### Example 8a — Basic checkable items

**Directory (new):** `src/themes/default/templates/appinterface/selection-lists/checkable/`

- **`code.php`**: Creates a `BigSelectionWidget` with `setFormName('fruits')` and adds three or four checkable items (e.g. Apple, Banana, Cherry) via `addCheckable()`. Wraps the widget in a plain HTML `<form method="post">` so the rendered example is self-contained and the submit path is visible. No items are pre-selected.
- **`description.md`**: Explains that checkable items render with a toggle-able checkbox, require a form name, and submit their values as an indexed array under that name. Notes that `getSubmittedValues()` validates submitted values against registered item values.
- **`example.json`**: `{"title": "Checkable items"}`

---

#### Example 8b — Pre-selected checkable items

**Directory (new):** `src/themes/default/templates/appinterface/selection-lists/checkable-preselected/`

- **`code.php`**: Same setup as 8a (`setFormName('fruits')`, same item list) but calls `makeSelected()` on one or two items after `addCheckable()`. Wraps in a `<form>` as in 8a.
- **`description.md`**: Explains `makeSelected()` — it pre-selects an item server-side so the item appears checked on initial render and its value is included in the form submission without any user interaction. Notes the use case: editing an existing record and restoring previously chosen values.
- **`example.json`**: `{"title": "Pre-selected checkable items"}`

---

#### Example 8c — Mixed item types

**Directory (new):** `src/themes/default/templates/appinterface/selection-lists/checkable-mixed/`

- **`code.php`**: Creates a `BigSelectionWidget` with `setFormName('options')`, adds a header item, two regular linked items, a separator, then two checkable items. Demonstrates that all item types coexist in a single widget without interfering with each other. Wraps in a `<form>`.
- **`description.md`**: Notes that checkable items can be freely mixed with regular items, headers, and separators. Only checkable items participate in form submission; regular and header items are navigational only.
- **`example.json`**: `{"title": "Mixed item types with checkable"}`

### Step 9: Run `composer dump-autoload`

Required after adding the new `CheckableItem` class (classmap autoloading).

### Step 10: Add unit tests

**File (new):** `tests/AppFrameworkTests/UI/BigSelection/BigSelectionCheckableTest.php`

Namespace: `testsuites\UI\BigSelection`. Follows the `tests/AppFrameworkTests/UI/ButtonTest.php` pattern — that file uses `namespace testsuites\UI`, as do all other UI test files in the same directory (`CriticalityEnumTest.php`, `IconCollectionTest.php`, `IconInfoTest.php`, `InstanceTest.php`). The `AppFrameworkTestClasses/` directory is reserved for base test classes and is not scanned by PHPUnit.

See Test Plan below for specific test cases.

## Dependencies

- No external dependencies required.
- The `CheckableItem` depends on `BaseItem` and `BigSelectionCSS` (both existing).
- The JS handler depends on jQuery (already loaded by the framework).

## Required Components

- `src/classes/UI/Bootstrap/BigSelection/Item/CheckableItem.php` (new)
- `src/classes/UI/Bootstrap/BigSelection/BigSelectionWidget.php` (modified)
- `src/classes/UI/Bootstrap/BigSelection/BigSelectionCSS.php` (modified)
- `src/classes/UI/UI.php` (modified — factory signature)
- `src/themes/default/templates/ui/bootstrap/big-selection.php` (modified)
- `src/themes/default/js/ui/bigselection/checkable.js` (new)
- `src/themes/default/css/ui-bigselection.css` (modified)
- `src/themes/default/templates/appinterface/selection-lists/checkable/` (new — 3 files: basic checkable items)
- `src/themes/default/templates/appinterface/selection-lists/checkable-preselected/` (new — 3 files: pre-selected items via `makeSelected()`)
- `src/themes/default/templates/appinterface/selection-lists/checkable-mixed/` (new — 3 files: checkable items mixed with regular, header, and separator items)
- `tests/AppFrameworkTests/UI/BigSelection/BigSelectionCheckableTest.php` (new)

## Assumptions

- jQuery is available globally in the framework's frontend (verified — used by `UI_BigSelection_Static`).
- The `active` CSS class provides sufficient visual distinction for checked items (verified — inverted colors with active background).
- The widget is always rendered inside a `<form>` when checkable items are used (caller's responsibility, enforced by documentation, not code).
- The checkbox icon is available in the framework's icon set (needs verification at implementation time — fall back to Unicode checkbox characters if not).

## Constraints

- Array syntax: `array()` only, never `[]`.
- `declare(strict_types=1)` in all new files.
- No PHP enums, no `readonly`, no constructor promotion.
- `composer dump-autoload` must run after adding new class files.
- The `BigSelectionCSS` class must be updated with all new CSS constants before they are used.

## Out of Scope

- "Select all" / "Deselect all" toggle buttons.
- Minimum/maximum selection count enforcement.
- AJAX-based value submission (values are submitted via standard form POST).
- Disabled/read-only state for individual checkable items.
- Drag-to-reorder for checkable items.

## Acceptance Criteria

- AC-01: `addCheckable($label, $value)` adds a checkable item that renders with a checkbox icon and a hidden input.
- AC-02: Clicking a checkable item toggles its visual state (active class) and its hidden input enabled/disabled state via JavaScript.
- AC-03: `setFormName($name)` sets the form variable name; an exception is thrown at render time if checkable items exist without a form name.
- AC-04: Submitted form data produces an indexed array of checked values under the configured form name.
- AC-05: `getSubmittedValues()` returns only values that match registered checkable item values (validation against known values).
- AC-06: `makeSelected()` pre-selects a checkable item (active class + enabled hidden input) on initial render.
- AC-07: Regular items, headers, and separators continue to work unchanged when mixed with checkable items.
- AC-08: Filtering (when enabled) correctly shows/hides checkable items based on search terms.
- AC-09: The `createBigSelection()` factory method accepts an optional `$formName` parameter.
- AC-10: Three example templates demonstrate the checkable mode: basic usage, pre-selected items via `makeSelected()`, and mixed item types.

## Testing Strategy

Unit tests verify the PHP-side behavior: item creation, form name handling, value validation, rendering output (HTML structure), and exception conditions. The JS toggle behavior is verified manually via the example templates in the framework's appinterface demo.

The appinterface test application is reachable locally at:

```
http://127.0.0.1/Workspaces/hcp-editor/STABLE/application-framework/tests/application/?example=selection-lists.regular&mode=appinterface&page=devel
```

Replace `selection-lists.regular` with the example slug to navigate to a specific example (e.g. `selection-lists.checkable`, `selection-lists.checkable-preselected`, `selection-lists.checkable-mixed`).

## Test Plan

- **Test: addCheckable creates CheckableItem** — calling `addCheckable('Label', 'val')` returns a `CheckableItem` instance and increases `countItems()`. — AC-01
- **Test: CheckableItem render output** — rendered HTML contains `bigselection-checkable` class, hidden input with correct `name[]` and `value`, and the checkbox icon span. — AC-01
- **Test: makeSelected sets active class and enables input** — calling `makeSelected()` results in rendered HTML with `active` class on `<li>` and the hidden input without `disabled`. — AC-06
- **Test: unchecked item renders disabled input** — a checkable item without `makeSelected()` renders the hidden input with `disabled` attribute. — AC-01
- **Test: setFormName stores and retrieves name** — `setFormName('myfield')` → `getFormName()` returns `'myfield'`. — AC-03
- **Test: missing form name throws exception** — adding a checkable item and calling `render()` without `setFormName()` throws `Application_Exception`. — AC-03
- **Test: no exception without checkable items** — a BigSelection with only regular items renders without a form name. — AC-07
- **Test: getSubmittedValues filters invalid values** — mock request with values `array('valid1', 'invalid', 'valid2')` where only `valid1` and `valid2` are registered item values → returns `array('valid1', 'valid2')`. — AC-05
- **Test: getSubmittedValues returns empty array when no submission** — no request param → returns `array()`. — AC-05
- **Test: getSubmittedValues with no form name returns empty array** — calling without `setFormName()` returns `array()` without error. — AC-05
- **Test: mixed item types** — adding regular items, headers, separators, and checkable items → all render correctly, `getCheckableItems()` returns only checkable ones. — AC-07
- **Test: createBigSelection with formName parameter** — `createBigSelection('myfield')` → `getFormName()` returns `'myfield'`. — AC-09
- **Test: filtering data-terms attribute on checkable items** — checkable item with label and description includes both in `data-terms`. — AC-08

## Documentation Updates

- `src/themes/default/templates/appinterface/selection-lists/checkable/description.md` — basic checkable items example. — AC-10
- `src/themes/default/templates/appinterface/selection-lists/checkable-preselected/description.md` — pre-selected checkable items example. — AC-10
- `src/themes/default/templates/appinterface/selection-lists/checkable-mixed/description.md` — mixed item types with checkable items example. — AC-10
- `.context/` — run `composer build` after implementation to regenerate CTX docs with the new class and updated API surface.
- `docs/agents/project-manifest/modules-overview.md` — will be auto-regenerated by `composer build`.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **Checkbox icon not available in the framework's icon set** | Fall back to Unicode characters (☐/☑) or a simple CSS-based checkbox square. Verify available icons during implementation. |
| **Filtering hides checked items, losing their state** | The hidden inputs remain in the DOM even when the `<li>` is hidden by filtering — form submission still includes them. Document this behavior. |
| **JS not loaded (no-JS scenario)** | Pre-selected items (`makeSelected()`) still submit correctly because their hidden input is not disabled. Unchecked items default to disabled. The widget degrades to a static display without toggle capability. |
| **Form name collision with other form elements** | Documented as caller responsibility. The array syntax (`name[]`) is standard HTML and unlikely to collide. |
