# Bootstrap

PHP abstractions for **Bootstrap v2 UI components**. Each class wraps a specific Bootstrap widget, exposes a fluent builder API, and renders itself to HTML via the `UI_Renderable` contract. Components are composable — e.g. a `ButtonDropdown` contains a `DropdownMenu` which contains `DropdownAnchor` items.

## Integration

- Base class: `UI_Bootstrap` extends `UI_Renderable`, implements `UI_Interfaces_Bootstrap` and `UI_Interfaces_Conditional`.
- All components are visible to the Conditional trait, so they can be shown/hidden based on runtime logic without `if` blocks at the call site.
- Consumed by: admin screens, page headers, data-grid actions, and any view code that builds interactive UI elements.

## Folder Overview

| Folder / File | Contents |
|---|---|
| `BigSelection/` | Large card-style selection widget |
| `ButtonGroup/` | Bootstrap button group with individual button types |
| `Dropdown/` | Dropdown building blocks (items, dividers, headers, subMenus) |
| `Tab/` | Individual tab and its content panel |
| `Anchor.php` | Styled `<a>` element |
| `BadgeDropdown.php` | Dropdown triggered by a badge |
| `BaseDropdown.php` | Shared base for all dropdown variants |
| `ButtonDropdown.php` | Button + dropdown menu combo |
| `DropdownMenu.php` | The menu container, holds items |
| `Popover.php` | Bootstrap popover |
| `Tabs.php` | Tab strip managing multiple `Tab` instances |

## Typical Usage

```php
// Dropdown button
$dd = $ui->createBootstrap()->buttonDropdown('Actions');
$dd->getMenu()->addLink('Edit', $editURL);
$dd->getMenu()->addLink('Delete', $deleteURL);

// Tab strip
$tabs = $ui->createBootstrap()->tabs();
$tabs->addTab('Overview')->setContent($overviewHTML);
$tabs->addTab('History')->setContent($historyHTML);
```

> Related: [UI module overview](../README.md)
