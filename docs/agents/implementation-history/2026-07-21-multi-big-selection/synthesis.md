# Synthesis Report — Multi-Select BigSelectionWidget

**Project:** `2026-07-21-multi-big-selection`
**Date:** 2026-07-21
**Status:** COMPLETE — All 3 work packages delivered across 12 pipeline stages

---

## Executive Summary

A multi-select (checkable) mode has been added to the `BigSelectionWidget` in the Application Framework. The feature introduces a new `CheckableItem` item type that coexists with the existing regular, header, and separator types. Checkable items render as list entries with a checkbox icon, transport their values via hidden `<input>` elements (disabled when unchecked), and are toggled by a new `UI_BigSelection_Checkable` JavaScript class. The widget gained `setFormName()`, `addCheckable()`, `prependCheckable()`, `getCheckableItems()`, `hasCheckableItems()`, and `getSubmittedValues()` methods, along with a render-time guard that enforces `setFormName()` when checkable items are present. Three appinterface example templates (basic, pre-selected, mixed) were created and the CTX module documentation was regenerated to include the new API surface.

---

## Scope Delivered

| WP | Title | Pipelines Passed | Tests (Final) |
|----|-------|-----------------|---------------|
| WP-001 | Core PHP feature: `CheckableItem` + `BigSelectionWidget` extensions | 4 / 4 | 13 / 13 |
| WP-002 | Frontend layer: JS handler, CSS, template integration, module docs | 4 / 4 | 15 / 15 |
| WP-003 | Appinterface example templates (checkable, preselected, mixed) | 4 / 4 | 15 / 15 |

**Total pipeline stages passed:** 12 / 12  
**PHPStan:** No errors (2,146 files scanned)

---

## Metrics

| Metric | Value |
|---|---|
| Work packages | 3 |
| Pipeline stages | 12 (all PASS) |
| Unit tests passing | 15 |
| Test assertions | 26 |
| PHPStan errors | 0 |
| Reviewer-applied fixes | 3 (all non-behavioral) |
| Files modified | ~25 source/template files |

---

## Artifacts

### New Classes

| File | Purpose |
|---|---|
| `src/classes/UI/Bootstrap/BigSelection/Item/CheckableItem.php` | New item type with `setLabel()`, `setValue()`, `setDescription()`, `makeSelected()`, `isSelected()`, `resolveSearchWords()`, `_render()` |

### Modified Classes

| File | Changes |
|---|---|
| `src/classes/UI/Bootstrap/BigSelection/BigSelectionWidget.php` | Added `setFormName`, `getFormName`, `hasFormName`, `addCheckable`, `prependCheckable`, `getCheckableItems`, `hasCheckableItems`, `getSubmittedValues`, render guard |
| `src/classes/UI/Bootstrap/BigSelection/BigSelectionCSS.php` | Added `ITEM_CHECKABLE`, `CHECKBOX_ICON`, `RESOURCES_JS_CHECKABLE` constants |
| `src/classes/UI/UI.php` | Added optional `$formName` parameter to `createBigSelection()` |

### Frontend

| File | Changes |
|---|---|
| `src/themes/default/js/ui/bigselection/checkable.js` | New `UI_BigSelection_Checkable` class: scoped click handler, `_toggle()`, window export |
| `src/themes/default/css/ui-bigselection.css` | New checkable section: layout rule, checked/unchecked `::before` pseudo-elements (U+2610/U+2611), proper property separation |
| `src/themes/default/templates/ui/bootstrap/big-selection.php` | Conditional load of `RESOURCES_JS_CHECKABLE` and JS initializer when `hasCheckableItems()` |

### Example Templates (Appinterface)

| Directory | Content |
|---|---|
| `selection-lists/checkable/` | Basic checkable list, no pre-selection |
| `selection-lists/checkable-preselected/` | Two items pre-selected via `makeSelected()` |
| `selection-lists/checkable-mixed/` | Header, regular, separator, and checkable items in a single widget |

### Documentation

| File | Changes |
|---|---|
| `src/classes/UI/Bootstrap/module-context.yaml` | Added `./BigSelection/Item` to architecture sourcePaths |
| `src/classes/UI/Bootstrap/README.md` | Noted checkable/multi-select item support |
| `.context/modules/ui/bootstrap/architecture.md` | Regenerated — includes CheckableItem + all new widget methods |
| `.context/modules/ui/bootstrap/overview.md` | Regenerated — updated feature summary |
| `docs/agents/projects/multi-big-selection.md` | Added example slug reference table |

### Tests

| File | Tests |
|---|---|
| `tests/AppFrameworkTests/UI/BigSelection/BigSelectionCheckableTest.php` | 15 tests, 26 assertions |

---

## Strategic Recommendations (Gold Nuggets)

### 1. Always use `htmlspecialchars(ENT_QUOTES, 'UTF-8')` for HTML attribute content
The Reviewer's fix-forward on `CheckableItem::resolveSearchWords()` caught an important gap: the original implementation only escaped double-quotes via `str_replace`, leaving ampersands and other HTML metacharacters raw in the `data-terms` attribute. `htmlspecialchars(ENT_QUOTES, 'UTF-8')` handles all four problematic characters. JavaScript's `getAttribute()` decodes HTML entities automatically, so this is strictly non-behavioral. Apply this pattern to all future attribute-bound output.

### 2. CSS-driven icon toggling is cleaner than direct DOM manipulation
`checkable.js` toggles only the `active` CSS class on the `<li>`. The visual switch between checked and unchecked checkbox icons (U+2610/U+2611) is entirely CSS-driven via `::before` pseudo-elements keyed on `.bigselection-checkable.active`. This produces a clean separation: JS manages state; CSS manages presentation. Use this pattern for any new toggle-based widget.

### 3. No-JS graceful degradation via initial HTML state
Pre-selected items render with `active` class and their hidden input without the `disabled` attribute directly in server-rendered HTML. The initial form submission state is therefore correct without any JavaScript execution. A low-cost but high-value pattern for form-adjacent widgets.

### 4. Keep `module-context.yaml` sourcePaths in sync with subdirectory growth
The `./BigSelection/Item` subdirectory was missing from `module-context.yaml`, silently causing all four item classes (`CheckableItem`, `RegularItem`, `HeaderItem`, `SeparatorItem`) to disappear from the CTX-generated architecture doc. When adding a new subdirectory under a module, update the corresponding `module-context.yaml` in the same commit.

### 5. Appinterface `example.json` convention: title-only key
The existing convention is `{ "title": "..." }` — no `id`, `label`, or `category` keys. The WP-001 agent created examples with the wrong keys; WP-003 corrected them. There is no schema validator or CI check enforcing this convention. Until one exists, verify against a pre-existing example before writing a new `example.json`.

### 6. Appinterface example variable naming: always `$sel` for `BigSelectionWidget`
All six pre-existing selection-list examples use `$sel` as the variable name. The new examples initially used `$widget`, which the Reviewer corrected. Enforcing this convention prevents cognitive friction when reading examples side-by-side.

---

## Deferred & Follow-Up Items

| # | Source | Agent | Description | Type | Priority |
|---|--------|-------|-------------|------|----------|
| 1 | WP-001 QA | QA | `prependCheckable()` has no ordering-assertion test. The method is public API and could silently regress. | Deferred | Low |
| 2 | WP-001/WP-003 | Developer / QA | The `big-selection` test suite name is not registered in `phpunit.xml`. Tests must be run via `composer test-filter -- BigSelection`. Registration is a one-line addition to `phpunit.xml`. | Deferred | Low |
| 3 | WP-001 | Developer | `RegularItem.php` and `CheckableItem.php` use `isset($this->icon)` rather than the `Application_Traits_Iconizable` trait's `hasIcon()/getIcon()` public API. A future cleanup could standardize all items. | Deferred | Low |
| 4 | WP-001 / WP-002 Docs | Documentation | `CheckableItem::setLabel()` accepts raw HTML. A future `SafeHtmlInterface` type (or similar) could make the no-user-content contract enforceable at the type level rather than via a docblock warning. | Deferred | Low |
| 5 | WP-001 Docs | Documentation | `BigSelectionWidget::getSubmittedValues()` reads the live HTTP request. A future `collect()` method separate from `render()` would make the request-phase constraint impossible to misuse rather than just documented. | Deferred | Low |
| 6 | WP-003 | Developer | Appinterface `example.json` files have no automated schema validation. A CI check or schema validator would prevent silent convention drift (as occurred between WP-001 and WP-003). | Deferred | Low |
| 7 | WP-002 | Developer / QA | `checkable.js` uses vanilla JS (querySelectorAll, classList) while `static.js` uses jQuery. Both work correctly and coexist. The intentional divergence is now documented in `BigSelectionCSS.php` and the CTX architecture doc. No change required; flagged for awareness only. | Out-of-scope | Low |

---

## Next Steps

1. **Register the `big-selection` test suite in `phpunit.xml`** — a one-line fix that unblocks `composer test-suite -- big-selection` (currently only `composer test-filter -- BigSelection` works).
2. **Evaluate `CheckableItem` for reuse in other form-adjacent widgets** — the hidden-input-per-item pattern is general enough to apply to tag selectors, permission pickers, and similar multi-select UIs.
3. **Consider a `SafeHtmlInterface` or equivalent** for widget label parameters that accept formatted HTML — the asymmetry between `setLabel()` (raw HTML) and `setDescription()` (HTML-escaped) is a latent footgun in new item type implementations.
4. **Add `example.json` schema validation to the appinterface example discovery pipeline** — prevents convention drift without requiring manual review.

---

## Standalone Developer Run — 2026-07-21

### Completion Status
- Date: 2026-07-21
- Status: COMPLETE
- Completed by: Standalone Developer Agent

### Outcome Summary

All planned files were already fully implemented from the prior agentic pipeline run. The standalone developer pass confirmed correctness by running `composer dump-autoload`, executing the full `BigSelectionCheckableTest` suite (15 tests, 26 assertions — all passing), and verifying PHPStan passes clean across the full codebase (2,146 files, zero errors).

### Implementation Summary

- No code changes were required — all files were already in place and correct.
- `composer dump-autoload` was run to register `CheckableItem` in the classmap.

### Documentation Updates

No documentation updates were required because all example templates, inline PHPDoc, and module notes were already created by the prior pipeline run.

### Verification Summary

- Tests run: `BigSelectionCheckableTest.php` — 15 tests, 26 assertions, PASS
- Static analysis run: `composer analyze` (PHPStan, full codebase) — No errors
- Result: PASS

### Code Insights

- [low] (improvement) `src/classes/UI/Bootstrap/BigSelection/Item/CheckableItem.php`: `@param` docblocks on `setLabel()` and `setDescription()` use `string|number|\UI_Renderable_Interface` — `number` is not a PHP type. Matches legacy convention in the file; harmonise when next touched.
- [low] (improvement) `tests/AppFrameworkTests/UI/BigSelection/BigSelectionCheckableTest.php`: `test_makeSelected_addsActiveClassAndEnablesInput` uses a negative-lookahead regex to assert the hidden input is not disabled. Functional but brittle to attribute reordering. Two-pass assertion would be more resilient.
- [low] (debt) The `big-selection` PHPUnit test suite is not yet registered in `phpunit.xml` (deferred item #2 from the pipeline synthesis above). Running `composer test-filter -- BigSelection` is the current workaround.
