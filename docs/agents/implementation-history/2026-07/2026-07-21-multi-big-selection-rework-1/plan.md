# Plan

## Plan Audit Cycles
- Audits: 1 — Plan Auditor v1.6.0
- Architectural Reviews: none — Plan Architect Reviewer v2.1.0

## Prior Project Context
The `2026-07-21-multi-big-selection` project delivered checkable items for `BigSelectionWidget` across 3 work packages and 12 pipeline stages. Its synthesis identified several deferred items and the checkbox icons currently use Unicode ballot box characters (U+2610/U+2611) via CSS `::before` pseudo-elements. Knowledge insight KN-0002 established CSS-driven icon toggling as the canonical pattern for this widget. This rework replaces the Unicode characters with FontAwesome icons and addresses the most valuable deferred items from the synthesis.

## Summary

Replace the Unicode ballot box checkbox icons in `CheckableItem` with FontAwesome `itemActive`/`itemInactive` icons (solid and outline circles), bringing the checkable BigSelection items in line with the icon system used throughout the framework. Additionally, register the `big-selection` test suite in `phpunit.xml`, add the missing `prependCheckable()` ordering test, and standardize icon access in item classes to use the `Iconizable` trait's public API.

## Architectural Context

The `BigSelectionWidget` lives in `src/classes/UI/Bootstrap/BigSelection/`. `CheckableItem` renders a `<span class="bigselection-checkbox"></span>` whose visual content is injected by CSS `::before` pseudo-elements: `\2610` (empty ballot box) when unchecked, `\2611` (checked ballot box) when the parent `<li>` carries the `active` class. The JavaScript handler (`checkable.js`) toggles only the `active` class and the hidden input's `disabled` attribute — it has no knowledge of icons or CSS classes beyond `active`.

The framework's icon system (`UI_Icon`) renders FontAwesome icons as `<i class="fa fa-{type}"></i>` tags. Two semantic icon methods are relevant:
- `itemActive()` → `fa fa-circle` (solid filled circle)
- `itemInactive()` → `far fa-circle` (outline circle)

These are used throughout the codebase (DeveloperMenu, UserMenu, RadioGroup) to indicate active/inactive toggle states.

## Approach / Architecture

**Replace the empty checkbox span with two server-side-rendered FontAwesome icons** using `UI::icon()->itemInactive()` and `UI::icon()->itemActive()`. Both icons are rendered server-side in PHP and included in the initial HTML output. CSS controls which icon is visible based on the `active` class on the parent `<li>`. This preserves the established pattern from KN-0002: JS manages state (the `active` class), CSS manages visual presentation (which icon is shown).

The `CheckableItem::_render()` method will call `UI::icon()` to render both icons inside the existing `bigselection-checkbox` span, using `UI_Icon::addClass()` to apply the state CSS classes directly to the rendered `<i>` tags. CSS will show one and hide the other based on the `active` state. The `checkable.js` handler requires **no changes** — it already toggles only the `active` class.

This approach follows the **server-side icon rendering paradigm**: all icons are rendered in PHP via `UI::icon()`, never constructed in JavaScript or injected via CSS pseudo-element content. JavaScript and CSS may toggle visibility or styling of server-rendered icons, but never create them.

**Test suite registration** adds a named `big-selection` suite to `phpunit.xml` so `composer test-suite -- big-selection` works alongside the existing `composer test-filter -- BigSelection`.

**Icon API standardization** replaces `isset($this->icon)` with `$this->hasIcon()` and `$this->icon` with `$this->getIcon()` in both `RegularItem::renderLabel()` and `CheckableItem::_render()`.

## Rationale

- **Two `<i>` tags with CSS visibility** over JS class-swapping on a single icon: keeps JS unchanged (KN-0002 pattern), works for no-JS initial state (pre-selected items render the active icon server-side), and avoids coupling JS to FA class names.
- **Server-side `UI::icon()` rendering** over CSS pseudo-element content or JS-constructed icons: icons are PHP-rendered `<i>` tags, giving them proper DOM presence, accessibility attributes, and consistent styling. CSS and JS only toggle visibility/classes on these server-rendered elements.
- **`itemActive`/`itemInactive` icons** over custom checkbox icons: these are the framework's established semantic toggle icons, already used in menus, user settings, and radio groups. Using them for checkable items creates visual consistency.
- **Named test suite** over relying on `test-filter`: enables `composer test-suite -- big-selection` which is the standard way to run module-scoped tests.
- **`hasIcon()`/`getIcon()` over `isset($this->icon)`**: uses the trait's public API, decouples from property name, consistent with the interface contract.

## Considered Alternatives

| Decision | Chosen Shape | Alternatives Considered | Trade-Off Summary |
|----------|--------------|-------------------------|-------------------|
| Checkbox icon rendering | Two `<i>` tags toggled by CSS `display` | Single `<i>` with JS swapping FA class; CSS `::before` with FA content codes | Two-tag approach preserves the JS-untouched pattern from KN-0002 and supports no-JS. Single-tag requires JS to know FA class names. CSS content codes require knowing FA unicode points. |
| Which FontAwesome icons | `itemActive` (solid circle) / `itemInactive` (outline circle) | `fa-check-square`/`fa-square`; `fa-toggle-on`/`fa-toggle-off` | Circle icons are already the framework's semantic toggle indicators (DeveloperMenu, UserMenu, RadioGroup). Checkbox-specific icons would introduce a new visual pattern. |
| Icon state class application | `UI_Icon::addClass()` on each `<i>` tag | Wrapper `<span>` per icon | `addClass()` adds the class directly to the rendered `<i>` tag — no extra DOM elements, no extra CSS constants for wrapper spans, uses the existing public API on `UI_Icon`. |

## Pattern Alignment

- **CSS-driven icon toggling** — follows the established pattern from KN-0002 and the original `checkable.js` implementation. JS manages state class; CSS manages presentation.
- **FontAwesome icons via `UI_Icon`** — follows `DeveloperMenu`, `UserMenu`, `RadioGroup` pattern for active/inactive state indicators.
- **Named test suite in `phpunit.xml`** — follows the framework's existing (though currently single) suite registration pattern.
- **`hasIcon()`/`getIcon()` public API** — follows the `Application_Interfaces_Iconizable` contract established in `src/classes/Application/Traits/Iconizable.php`.

## Detailed Steps

### Step 1: Replace Unicode checkbox with server-side-rendered FontAwesome icons in `CheckableItem::_render()`

**File:** `src/classes/UI/Bootstrap/BigSelection/Item/CheckableItem.php`

Add `use UI;` to the file's imports.

In the `_render()` method, replace the empty `<span class="bigselection-checkbox"></span>` with a span containing two server-side-rendered FontAwesome icons. Use `UI_Icon::addClass()` to apply the state CSS classes directly to the rendered `<i>` tags:

```php
<span class="<?php echo BigSelectionCSS::CHECKBOX_ICON ?>">
    <?php echo UI::icon()->itemInactive()->addClass(BigSelectionCSS::CHECKBOX_ICON_UNCHECKED) ?>
    <?php echo UI::icon()->itemActive()->addClass(BigSelectionCSS::CHECKBOX_ICON_CHECKED) ?>
</span>
```

Both icons are rendered server-side in PHP. The unchecked icon (`itemInactive`) is displayed by default; the checked icon (`itemActive`) is hidden by default. CSS rules (Step 3) toggle visibility based on the `active` class on the parent `<li>`. JavaScript never creates or manipulates icon elements — it only toggles the `active` class.

### Step 2: Add CSS constants for icon state classes

**File:** `src/classes/UI/Bootstrap/BigSelection/BigSelectionCSS.php`

Add two new constants for the CSS classes applied directly to the rendered FontAwesome `<i>` tags via `UI_Icon::addClass()` in Step 1:

```php
public const string CHECKBOX_ICON_UNCHECKED = 'bigselection-checkbox-unchecked';
public const string CHECKBOX_ICON_CHECKED = 'bigselection-checkbox-checked';
```

### Step 3: Update CSS to toggle FontAwesome icon visibility

**File:** `src/themes/default/css/ui-bigselection.css`

Replace the existing `::before` content rules with visibility rules for the two icon state classes. These classes are applied directly to the FontAwesome `<i>` tags via `addClass()` (Step 1):

```css
/* =========================================================
   Checkable BigSelection Items
   ========================================================= */

.bigselection .bigselection-checkable .bigselection-checkbox {
    margin-right: 8px;
    display: inline-block;
    vertical-align: middle;
}

/* Unchecked state: show inactive icon, hide active icon */
.bigselection .bigselection-checkable .bigselection-checkbox-unchecked {
    display: inline;
}

.bigselection .bigselection-checkable .bigselection-checkbox-checked {
    display: none;
}

/* Checked state (active class on parent <li>): show active icon, hide inactive icon */
.bigselection .bigselection-checkable.active .bigselection-checkbox-unchecked {
    display: none;
}

.bigselection .bigselection-checkable.active .bigselection-checkbox-checked {
    display: inline;
}
```

### Step 4: Standardize icon access in `RegularItem` and `CheckableItem`

**File:** `src/classes/UI/Bootstrap/BigSelection/Item/RegularItem.php`

In `renderLabel()` (around L162), replace:
```php
if (isset($this->icon)) {
    $label = $this->icon . ' ' . $label;
}
```
with:
```php
if ($this->hasIcon()) {
    $label = $this->getIcon() . ' ' . $label;
}
```

**File:** `src/classes/UI/Bootstrap/BigSelection/Item/CheckableItem.php`

In `_render()` (around L183), replace:
```php
if (isset($this->icon)) {
    $labelHtml = $this->icon . ' ' . $labelHtml;
}
```
with:
```php
if ($this->hasIcon()) {
    $labelHtml = $this->getIcon() . ' ' . $labelHtml;
}
```

### Step 5: Register `big-selection` test suite in `phpunit.xml`

**File:** `phpunit.xml`

Add a named test suite after the existing `Framework Tests` suite:

```xml
<testsuite name="big-selection">
    <directory suffix=".php">./tests/AppFrameworkTests/UI/BigSelection</directory>
</testsuite>
```

### Step 6: Add `prependCheckable()` ordering test

**File:** `tests/AppFrameworkTests/UI/BigSelection/BigSelectionCheckableTest.php`

Add a test in the existing `addCheckable` region (or a new `prependCheckable` region) that verifies `prependCheckable()` inserts the item at position 0:

```php
/**
 * @see BigSelectionWidget::prependCheckable()
 */
public function test_prependCheckable_insertsAtBeginning() : void
{
    $widget = $this->createWidget($this->formName);
    $widget->addCheckable('Second', 'second');
    $widget->prependCheckable('First', 'first');

    $items = $widget->getCheckableItems();

    $this->assertCount(2, $items);
    $this->assertSame('first', $items[0]->getValue());
    $this->assertSame('second', $items[1]->getValue());
}
```

### Step 7: Add icon rendering test

**File:** `tests/AppFrameworkTests/UI/BigSelection/BigSelectionCheckableTest.php`

Add a test that verifies the rendered output contains FontAwesome icon markup instead of Unicode characters:

```php
/**
 * Checkable items must render FontAwesome itemActive/itemInactive icons
 * inside the checkbox span, not Unicode ballot box characters.
 *
 * @see CheckableItem::_render()
 */
public function test_checkableItem_rendersFontAwesomeIcons() : void
{
    $widget = $this->createWidget($this->formName);
    $widget->addCheckable('Icon Test', 'icon_val');

    $html = $widget->render();

    $this->assertStringContainsString('bigselection-checkbox-unchecked', $html);
    $this->assertStringContainsString('bigselection-checkbox-checked', $html);
    $this->assertStringContainsString('fa-circle', $html);
}
```

### Step 8: Update appinterface example descriptions

**File:** `src/themes/default/templates/appinterface/selection-lists/checkable/description.md`

Update the description to mention that checkable items display FontAwesome circle icons as their checkbox indicator.

### Step 9: Document server-side icon toggle paradigm in constraints

**File:** `docs/agents/project-manifest/constraints.md`

Add a new `## UI Icon Rendering` section (after the existing `## UI Localization` section) documenting the paradigm:

- All icons are rendered **server-side in PHP** via `UI::icon()` — never constructed in JavaScript or injected via CSS pseudo-element content.
- For toggle/state-driven icons (e.g. active/inactive indicators, checkboxes), render **all state variants** server-side and use CSS `display` rules to show/hide the appropriate icon based on a state class (e.g. `.active`).
- JavaScript may toggle CSS classes to switch visual state, but must not create, replace, or modify icon DOM elements.
- Reference the `BigSelectionWidget` checkable items as a canonical example: `UI::icon()->itemInactive()` and `UI::icon()->itemActive()` are both rendered in `CheckableItem::_render()`, and CSS toggles which is visible based on the `active` class.
- The `UI::icon()` factory returns a `UI_Icon` instance that renders as an `<i>` tag with FontAwesome CSS classes.

### Step 10: Regenerate CTX documentation

Run `composer build` to regenerate the `.context/` documentation so the updated `CheckableItem` signature (with `UI::icon()` calls) and new CSS constants are reflected in the architecture docs.

## Dependencies
- FontAwesome must be loaded in the page (it already is — the framework loads it globally).
- The `UI` facade must be accessible from `CheckableItem::_render()` (it is — `UI::icon()` is a static call available everywhere).

## Required Components
- `src/classes/UI/Bootstrap/BigSelection/Item/CheckableItem.php` — modification
- `src/classes/UI/Bootstrap/BigSelection/BigSelectionCSS.php` — modification
- `src/themes/default/css/ui-bigselection.css` — modification
- `src/classes/UI/Bootstrap/BigSelection/Item/RegularItem.php` — modification
- `phpunit.xml` — modification
- `tests/AppFrameworkTests/UI/BigSelection/BigSelectionCheckableTest.php` — modification
- `docs/agents/project-manifest/constraints.md` — modification (new section)
- `src/themes/default/templates/appinterface/selection-lists/checkable/description.md` — modification

## Assumptions
- FontAwesome is loaded globally by the framework and available on all pages where `BigSelectionWidget` renders.
- `UI::icon()` is callable from within item `_render()` methods (it is a static factory on a globally available class).
- The `checkable.js` handler does not need changes because it only toggles the `active` CSS class — the icon switching is entirely CSS-driven.

## Constraints
- The `checkable.js` handler must remain unchanged — no icon-specific JS logic.
- Array syntax must use `array()`, not `[]`.
- `declare(strict_types=1)` in every PHP file.
- Variable name `$sel` in appinterface examples (KN-0005).

## Out of Scope
- `SafeHtmlInterface` for label parameters (synthesis deferred #4) — requires cross-cutting type design.
- Separate `collect()` method for request-phase separation (synthesis deferred #5) — API design change beyond this scope.
- `example.json` schema validation (synthesis deferred #6) — CI/tooling change.
- JS style unification between `static.js` (jQuery) and `checkable.js` (vanilla) (synthesis deferred #7) — documented intentional divergence.

## Acceptance Criteria

- AC-01: Checkable items render FontAwesome `itemInactive` (outline circle) and `itemActive` (solid circle) icons instead of Unicode ballot box characters.
- AC-02: In the unchecked state, only the `itemInactive` icon is visible; in the checked state (CSS `active` class), only the `itemActive` icon is visible.
- AC-03: The `checkable.js` handler is unchanged — icon toggling is purely CSS-driven.
- AC-04: Pre-selected items (via `makeSelected()`) render with the `itemActive` icon visible on initial page load (no JS required).
- AC-05: `RegularItem::renderLabel()` and `CheckableItem::_render()` use `hasIcon()`/`getIcon()` instead of `isset($this->icon)`/`$this->icon`.
- AC-06: `phpunit.xml` contains a `big-selection` test suite pointing to `./tests/AppFrameworkTests/UI/BigSelection`.
- AC-07: A test verifies that `prependCheckable()` inserts the item at position 0.
- AC-08: A test verifies that rendered checkable items contain FontAwesome icon CSS classes (`fa-circle`).
- AC-09: All existing tests continue to pass.
- AC-10: PHPStan reports no new errors.
- AC-11: CTX documentation is regenerated and reflects the updated API surface.
- AC-12: The server-side icon rendering paradigm and CSS-driven toggle pattern are documented in `docs/agents/project-manifest/constraints.md`.

## Testing Strategy

All changes are tested via PHPUnit render-output assertions. The FontAwesome icon switch is verified by checking for the presence of FA CSS classes and the new state wrapper classes in the rendered HTML. The `prependCheckable()` ordering test uses value comparison on the ordered item list. Existing tests cover all other widget behavior (form name, submitted values, filtering, JS resource loading).

## Test Plan

- `tests/AppFrameworkTests/UI/BigSelection/BigSelectionCheckableTest.php::test_prependCheckable_insertsAtBeginning` — verifies prepended checkable item appears first in the list — AC-07
- `tests/AppFrameworkTests/UI/BigSelection/BigSelectionCheckableTest.php::test_checkableItem_rendersFontAwesomeIcons` — verifies rendered HTML contains `bigselection-checkbox-unchecked`, `bigselection-checkbox-checked`, and `fa-circle` — AC-01, AC-08
- `tests/AppFrameworkTests/UI/BigSelection/BigSelectionCheckableTest.php::test_checkableItem_renderOutput` (existing) — existing assertions still pass with new icon markup — AC-09
- `tests/AppFrameworkTests/UI/BigSelection/BigSelectionCheckableTest.php::test_makeSelected_addsActiveClassAndEnablesInput` (existing) — verifies pre-selected items render with `active` class — AC-04
- All existing tests in `BigSelectionCheckableTest.php` — regression coverage — AC-09

## Documentation Updates

- `src/classes/UI/Bootstrap/BigSelection/BigSelectionCSS.php` — new constants `CHECKBOX_ICON_UNCHECKED`, `CHECKBOX_ICON_CHECKED` with docblocks — follows manifest maintenance rules for DTO/constant changes
- `docs/agents/project-manifest/constraints.md` — new `## UI Icon Rendering` section documenting the server-side icon rendering paradigm and CSS-driven toggle pattern
- `docs/agents/project-manifest/testing.md` — update suite inventory: add `big-selection` suite entry; replace "Single suite" wording with "Two named suites" — mandated by AGENTS.md maintenance rule "Testing infrastructure changed → `testing.md`"
- `src/themes/default/templates/appinterface/selection-lists/checkable/description.md` — update to mention FontAwesome circle icons
- `.context/` — regenerated via `composer build` to reflect updated API surface — AC-11

## Deferred Items

| # | Deferred Item | Origin | Reason Deferred | Notes |
|---|---------------|--------|-----------------|-------|
| 1 | `SafeHtmlInterface` for widget label parameters | Synthesis deferred #4 | Cross-cutting type design affecting multiple widget APIs; requires broader design discussion | Reconsider when a new item type or widget accepts user-supplied HTML |
| 2 | Separate `collect()` method for request-phase separation | Synthesis deferred #5 | API design change that alters the widget's public contract; low incident rate for the ordering confusion it prevents | Reconsider if `getSubmittedValues()` misuse is reported |
| 3 | `example.json` schema validation in CI | Synthesis deferred #6 | Tooling/CI change outside the BigSelection module scope | Reconsider during a broader appinterface tooling improvement pass |
| 4 | JS style unification (`static.js` jQuery vs `checkable.js` vanilla) | Synthesis deferred #7 | Documented intentional divergence; both work correctly | No action unless a framework-wide JS modernization effort begins |

## Risks & Mitigations
| Risk | Mitigation |
|------|------------|
| **Existing tests break due to changed HTML structure** | The outer `bigselection-checkbox` wrapper class is preserved; only its children change. No existing test asserts Unicode ballot box characters — no test edits are needed for this risk. |
| **FA icons not rendering in specific contexts** | FontAwesome is loaded globally by the framework. The icons use the same FA classes as existing menu/radio toggle icons that are confirmed working. |
| **CSS specificity conflict with active state** | The new CSS rules use the same selector specificity pattern as the existing `::before` rules they replace. No specificity escalation needed. |

## Recommended Workflow
- **Workflow:** standalone
- **Rationale:** All changes are within the BigSelection module, follow established patterns, and the scope is small enough for a single developer session with self-review.
