# DataGrid

Renders **tabular data** with built-in sorting, pagination, column configuration, row selection, and bulk actions. A data grid is driven by a list builder that supplies the filtered, ordered record set; the grid handles all the display and interaction logic on top of it.

## Integration

- Entry point: `UI_DataGrid`, typically created via `$ui->createDataGrid()`.
- Optionally paired with a `BaseListBuilder` to feed records from a filter-criteria source.
- Uses `GridConfigurator` to persist per-user column visibility preferences.
- Consumed by: admin list screens throughout the application.

## Folder Overview

| Folder / File | Contents |
|---|---|
| `Action/` | Per-row and bulk-action definitions |
| `Column/` | Column types (text, date, number, custom renderer…) |
| `Entry/` | Row-level data wrapper and client-command helpers |
| `ListBuilder/` | Base classes for connecting a `FilterCriteria` source to the grid |
| `Row/` | Row rendering helpers |
| `Action.php` | Base action class |
| `Column.php` | Base column class |
| `Entry.php` | Represents a single rendered row |
| `GridClientCommands.php` | JavaScript command hooks for grid-level interactions |
| `EntryClientCommands.php` | JavaScript command hooks for row-level interactions |
| `GridConfigurator.php` | Persists column-visibility preferences per user |
| `RedirectMessage.php` | Post-action redirect with user feedback |
| `Exception.php` | Typed DataGrid exceptions |

## Key Request Parameters

| Constant | Query Parameter | Purpose |
|---|---|---|
| `REQUEST_PARAM_ORDERBY` | `datagrid_orderby` | Column to sort by |
| `REQUEST_PARAM_ORDERDIR` | `datagrid_orderdir` | `asc` or `desc` |
| `REQUEST_PARAM_PAGE` | `datagrid_page` | Current page number |
| `REQUEST_PARAM_PERPAGE` | `datagrid_perpage` | Rows per page |
| `REQUEST_PARAM_ACTION` | `datagrid_action` | Bulk action being submitted |

## Typical Usage

```php
$grid = $ui->createDataGrid('products');
$grid->addColumn('name', t('Name'))->setSortable();
$grid->addColumn('status', t('Status'));
$grid->addAction('delete', t('Delete'))->makeConfirm(t('Sure?'));

$grid->setListBuilder(new ProductListBuilder($filterCriteria));
echo $grid->render();
```

> Related: [UI module overview](../README.md)
