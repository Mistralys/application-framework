# UI Properties Grid - Overview
_SOURCE: PropertiesGrid README_
# PropertiesGrid README
```
// Structure of documents
└── src/
    └── classes/
        └── UI/
            └── PropertiesGrid/
                └── README.md

```
###  Path: `/src/classes/UI/PropertiesGrid/README.md`

```md
# PropertiesGrid

Renders a **key/value property table** for detail views. Each row displays a label alongside a typed value; cells support rich content (HTML, buttons, formatted numbers) and conditional visibility.

## Integration

- Entry point: `UI_PropertiesGrid`, typically created via `$ui->createPropertiesGrid()`.
- Rows are `UI_PropertiesGrid_Property` instances; concrete subtypes live in `Property/`.
- Conditional rows use the `UI_Interfaces_Conditional` / `UI_Traits_Conditional` mechanism shared across the UI layer.

## Folder Overview

| Folder / File | Contents |
|---|---|
| `Property/` | Concrete property types (text, boolean, date, link, custom HTML …) |
| `Property.php` | Abstract base — `label`, `value`, optional `buttons`, empty-text fallback |

## Key `UI_PropertiesGrid_Property` API

| Method | Purpose |
|---|---|
| `addButton(UI_Button)` | Adds an action button rendered alongside the value |
| `setEmptyText(string)` | Override the "Empty" placeholder shown when the value is null/empty |
| `makeConditional(bool)` | Hide the row entirely when the condition is false |

## Typical Usage

```php
$grid = $ui->createPropertiesGrid();
$grid->addProperty(t('Name'), $record->getName());
$grid->addProperty(t('Status'), $record->getStatus()->getLabel())
     ->addButton($ui->createButton(t('Change'))->linkTo($changeURL));
$grid->addProperty(t('Created'), $record->getDateCreated());

echo $grid->render();
```

> Related: [UI module overview](../README.md)

```
---
**File Statistics**
- **Size**: 1.88 KB
- **Lines**: 61
File: `modules/ui/properties-grid/overview.md`
