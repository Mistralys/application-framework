# UI DataGrid - Architecture
_SOURCE: Public class signatures for grid, columns, rows, actions, and list builders_
# Public class signatures for grid, columns, rows, actions, and list builders
```
// Structure of documents
└── src/
    └── classes/
        └── UI/
            └── DataGrid.php
            └── DataGrid/
                └── Action.php
                └── Action/
                    ├── Confirm.php
                    ├── Default.php
                    ├── Javascript.php
                    ├── Separator.php
                └── BaseListBuilder.php
                └── Column.php
                └── Column/
                    ├── ColumnSettingStorage.php
                    ├── MultiSelect.php
                └── Entry.php
                └── Entry/
                    ├── Heading.php
                    ├── Merged.php
                └── EntryClientCommands.php
                └── Exception.php
                └── GridClientCommands.php
                └── GridConfigurator.php
                └── ListBuilder/
                    ├── ListBuilderScreenInterface.php
                    ├── ListBuilderScreenTrait.php
                └── RedirectMessage.php
                └── Row.php
                └── Row/
                    └── Sums.php
                    └── Sums/
                        └── ColumnDef.php
                        └── ColumnDef/
                            └── Callback.php

```
###  Path: `/src/classes/UI/DataGrid.php`

```php
namespace ;

use AppUtils\HTMLTag as HTMLTag;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use Application\AppFactory as AppFactory;
use Application\Application as Application;
use Application\Driver\DriverException as DriverException;
use Application\FilterSettings\FilterSettingsInterface as FilterSettingsInterface;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use Application\Interfaces\FilterCriteriaInterface as FilterCriteriaInterface;
use Application\Interfaces\HiddenVariablesInterface as HiddenVariablesInterface;
use Application\Traits\HiddenVariablesTrait as HiddenVariablesTrait;
use UI\DataGrid\GridClientCommands as GridClientCommands;
use UI\DataGrid\GridConfigurator as GridConfigurator;

/**
 * Handles displaying data in a tabular grid, with extended functionality
 * like applying custom actions to entries, allowing the user to reorder
 * list items, and more.
 *
 * @package User Interface
 * @subpackage Data Grids
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_DataGrid implements HiddenVariablesInterface
{
	use HiddenVariablesTrait;

	public const ERROR_MISSING_PRIMARY_KEY_NAME = 599901;
	public const ERROR_ALLSELECTED_FILTER_CRITERIA_MISSING = 599903;
	public const ERROR_ALLSELECTED_PRIMARY_KEYNAME_MISSING = 599904;
	public const ERROR_DUPLICATE_DATAGRID_ID = 599905;
	public const ERROR_UNKNOWN_OPTION = 599907;
	public const ERROR_COLUMN_NAME_DOES_NOT_EXIST = 599908;
	public const ERROR_ACTION_NOT_FOUND = 599909;
	public const ERROR_ACTION_ALREADY_ADDED = 599910;
	public const REQUEST_PARAM_ORDERBY = 'datagrid_orderby';
	public const REQUEST_PARAM_ORDERDIR = 'datagrid_orderdir';
	public const REQUEST_PARAM_ACTION = 'datagrid_action';
	public const REQUEST_PARAM_SUBMITTED = 'datagrid_submitted';
	public const REQUEST_PARAM_PERPAGE = 'datagrid_perpage';
	public const REQUEST_PARAM_PAGE = 'datagrid_page';
	public const REQUEST_PARAM_CONFIGURE_GRID = 'configure_data_grid';
	public const COLUMN_START_INDEX = 1;
	public const SETTING_SEPARATOR = '_';
	public const DEFAULT_LIMIT_CHOICES = [10, 20, 40, 60, 120];

	/**
	 * @param string|number|UI_Renderable_Interface|NULL $message
	 * @return $this
	 */
	public function setEmptyMessage($message): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * @return string
	 */
	public function getID(): string
	{
		/* ... */
	}


	/**
	 * @return UI
	 */
	public function getUI(): UI
	{
		/* ... */
	}


	/**
	 * Retrieves the currently selected sorting column.
	 *
	 * @return UI_DataGrid_Column|NULL
	 */
	public function getOrderColumn(): ?UI_DataGrid_Column
	{
		/* ... */
	}


	/**
	 * Enables clientside controls to adjust the number of columns that get
	 * displayed, and to navigate between them.
	 *
	 * @param int $maxColumns The maximum number of columns to display; Any above will be hidden.
	 * @return $this
	 */
	public function enableColumnControls(int $maxColumns = 5): self
	{
		/* ... */
	}


	/**
	 * Retrieves a column by its data key name.
	 * @param string $dataKeyName
	 * @return UI_DataGrid_Column|NULL
	 */
	public function getColumnByName(string $dataKeyName): ?UI_DataGrid_Column
	{
		/* ... */
	}


	/**
	 * Retrieves a column by its order key name.
	 * @param string $orderKeyName
	 * @return UI_DataGrid_Column|NULL
	 */
	public function getColumnByOrderKey(string $orderKeyName): ?UI_DataGrid_Column
	{
		/* ... */
	}


	/**
	 * Distributes widths evenly over all columns in the grid.
	 * The optional parameter can force all existing with settings
	 * of individual columns to be overwritten. Default is to
	 * retain any existing width settings.
	 *
	 * Note: any columns that have a pixel width set will be
	 * reset and given an automatic percentual width, since the
	 * two do not mix well.
	 *
	 * @param bool $overwriteExisting
	 * @return $this
	 */
	public function makeEvenColumnWidths(bool $overwriteExisting = false): self
	{
		/* ... */
	}


	/**
	 * Moves the specified column to the desired position, starting at
	 * 1 for the first column in the grid.
	 *
	 * @param UI_DataGrid_Column $column
	 * @param int|string $position
	 * @return boolean Whether the column was moved
	 * @deprecated Not supported anymore. Use custom ordering instead.
	 */
	public function moveColumn(UI_DataGrid_Column $column, $position): bool
	{
		/* ... */
	}


	public function resetColumnWidths(): self
	{
		/* ... */
	}


	/**
	 * Retrieves the column by its column number (starting at 1).
	 * @param integer $number
	 * @return UI_DataGrid_Column|NULL
	 */
	public function getColumn(int $number): ?UI_DataGrid_Column
	{
		/* ... */
	}


	public function getLastColumn(): ?UI_DataGrid_Column
	{
		/* ... */
	}


	/**
	 * Counts all columns, excluding hidden columns.
	 * @return int
	 */
	public function countColumns(): int
	{
		/* ... */
	}


	/**
	 * Counts the number of columns that the user has
	 * chosen not to display.
	 *
	 * @return int
	 */
	public function countUserHiddenColumns(): int
	{
		/* ... */
	}


	/**
	 * @param string $dataKey
	 * @param string|number|UI_Renderable_Interface $title
	 * @param array<string,mixed> $options
	 * @return UI_DataGrid_Column
	 */
	public function addColumn(string $dataKey, $title, array $options = []): UI_DataGrid_Column
	{
		/* ... */
	}


	public function setColumnEnabled(string $keyName, bool $enabled): self
	{
		/* ... */
	}


	/**
	 * @param string $keyName
	 * @return false|UI_DataGrid_Column
	 */
	public function hasColumn(string $keyName)
	{
		/* ... */
	}


	/**
	 * Adds a row with sums of values in columns.
	 *
	 * @return UI_DataGrid_Row_Sums
	 */
	public function addSumsRow(): UI_DataGrid_Row_Sums
	{
		/* ... */
	}


	/**
	 * Adds controls in the grid to select multiple entries and add
	 * actions for them. Use the {@link addAction()} method to add
	 * available actions.
	 *
	 * @param string $primaryKeyName The name of the primary key in the records, only optional if you plan to set it separately.
	 * @param bool $forced Whether the force the checkboxes even when the grid has no actions (if you plan on processing the selected items manually)
	 * @see addAction()
	 */
	public function enableMultiSelect(string $primaryKeyName = '', bool $forced = false): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * Makes the multiselect menu open towards the top of
	 * the page instead of towards the bottom. Only used
	 * if the multiselect is enabled.
	 *
	 * @return UI_DataGrid
	 */
	public function setMultiSelectDropUp(): UI_DataGrid
	{
		/* ... */
	}


	public function setPrimaryName(string $keyName): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * Disables the multiselect functionality, even
	 * if it was enabled prior to calling this.
	 */
	public function disableMultiSelect(): UI_DataGrid
	{
		/* ... */
	}


	public function optionExists(string $name): bool
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param mixed $value
	 * @return $this
	 * @throws Exception
	 */
	public function setOption(string $name, $value): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @return mixed
	 * @throws UI_DataGrid_Exception
	 */
	public function getOption(string $name)
	{
		/* ... */
	}


	public function getClientObjectName(): string
	{
		/* ... */
	}


	/**
	 * @param string $actionName
	 * @return string
	 * @deprecated Use {@see self::clientCommands()} instead.
	 */
	public function getClientSubmitStatement(string $actionName): string
	{
		/* ... */
	}


	/**
	 * @return string
	 * @deprecated Use {@see self::clientCommands()} instead.
	 */
	public function getClientToggleSelectionStatement(): string
	{
		/* ... */
	}


	/**
	 * Gets the helper class used to access client-side commands
	 * related to this data grid.
	 *
	 * @return GridClientCommands
	 */
	public function clientCommands(): GridClientCommands
	{
		/* ... */
	}


	/**
	 * Adds an action to the grid that can be run for the selected
	 * elements (works only if multi select is enabled).
	 * @param string $name
	 * @param string|int|float|StringableInterface|null $label
	 * @return UI_DataGrid_Action_Default
	 */
	public function addAction(string $name, string|int|float|StringableInterface|null $label): UI_DataGrid_Action_Default
	{
		/* ... */
	}


	/**
	 * Adds a separator between multiselect actions.
	 * @see addConfirmAction()
	 * @see addAction()
	 */
	public function addSeparatorAction(): void
	{
		/* ... */
	}


	/**
	 * Adds an action to the grid that can be run for the selected
	 * elements, but which will display a confirmation dialog before
	 * starting the action. Only works if multi select is enabled.
	 * @param string $name
	 * @param string|int|float|StringableInterface|NULL $label
	 * @param string|int|float|StringableInterface|NULL $confirmMessage
	 * @return UI_DataGrid_Action_Confirm
	 * @throws UI_Exception
	 */
	public function addConfirmAction(
		string $name,
		string|int|float|StringableInterface|null $label,
		string|int|float|StringableInterface|null $confirmMessage,
	): UI_DataGrid_Action_Confirm
	{
		/* ... */
	}


	/**
	 * Checks whether any actions have been added to the grid.
	 * @return boolean
	 */
	public function hasActions(): bool
	{
		/* ... */
	}


	/**
	 * Adds an action that executed the specified javascript function
	 * when the action is selected. For this to work correctly, place
	 * the placeholder <code>%1$s</code> where you wish the datagrid
	 * object instance to be inserted, and <code>%2$s</code> for the
	 * name of the action.
	 *
	 * Example:
	 *
	 * <pre>
	 * addJSAction('do_something(%1$s, %2$s)');
	 * </pre>
	 *
	 * Important: use only single quotes in the code!
	 *
	 * @param string $name
	 * @param string $label
	 * @param string $function
	 * @return UI_DataGrid_Action_Javascript
	 */
	public function addJSAction(string $name, string $label, string $function): UI_DataGrid_Action_Javascript
	{
		/* ... */
	}


	public function disableRowSeparator(): UI_DataGrid
	{
		/* ... */
	}


	public function enableRowSeparator(): UI_DataGrid
	{
		/* ... */
	}


	public function disableBorder(): UI_DataGrid
	{
		/* ... */
	}


	public function enableBorder(): UI_DataGrid
	{
		/* ... */
	}


	public function disableMargins(): UI_DataGrid
	{
		/* ... */
	}


	public function enableMargins(): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * Makes the list more compact by reducing cell padding.
	 * Alias for setting the "compact" option to true.
	 */
	public function enableCompactMode(): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * Makes a mini table of the list by removing table borders and reducing padding/margin.
	 * Alias for setting the "mini" option to true.
	 */
	public function enableMiniMode(): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * Reduces the size of the columns to fit the content inside
	 * Alias for setting the "fit-content" option to true.
	 */
	public function enableFitContent(): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * If disabled, the datagrid will be rendered without an
	 * enclosing form tag. In this case, actions and the like
	 * which depend on the form will be disabled as well.
	 *
	 * @return UI_DataGrid
	 */
	public function disableForm(): UI_DataGrid
	{
		/* ... */
	}


	public function isFormEnabled(): bool
	{
		/* ... */
	}


	public function disableCompactMode(): UI_DataGrid
	{
		/* ... */
	}


	public function enableHover(): UI_DataGrid
	{
		/* ... */
	}


	public function disableHover(): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * Enables the multiple choice selector for choosing the
	 * number of items to display per page. If no current
	 * choice is set, the first item in the selector is used.
	 * The choices have to be an indexed array of numeric values.
	 *
	 * @param int[] $choices
	 * @return UI_DataGrid
	 */
	public function enableLimitOptions(array $choices): UI_DataGrid
	{
		/* ... */
	}


	public function enableLimitOptionsDefault(): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @return string
	 */
	public function resolveSettingName(string $name): string
	{
		/* ... */
	}


	/**
	 * Disables the multiple choice selector for choosing the
	 * number of items to show per page (only useful if it
	 * has been enabled prior to calling this, as it is
	 * disabled by default).
	 */
	public function disableLimitOptions(): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * Disables the hint message "X items are hidden by filter settings"
	 * that is displayed when no entries are found.
	 *
	 * @return UI_DataGrid
	 */
	public function disableFilterHint(): UI_DataGrid
	{
		/* ... */
	}


	public function enableFilterHint(): UI_DataGrid
	{
		/* ... */
	}


	public function getOffset(): int
	{
		/* ... */
	}


	/**
	 * Retrieves the current items per page limit.
	 * @return int
	 */
	public function getLimit(): int
	{
		/* ... */
	}


	/**
	 * Retrieves the name of the primary field. The value
	 * has to be set in the data records, even if it is not
	 * shown in a column.
	 *
	 * @return string
	 */
	public function getPrimaryField(): string
	{
		/* ... */
	}


	/**
	 * Checks whether the primary field name has been set.
	 * @return boolean
	 */
	public function hasPrimaryField(): bool
	{
		/* ... */
	}


	/**
	 * Adds a class name that will be added to the data grid's main table HTML element.
	 * @param string $class
	 * @return $this
	 */
	public function addTableClass(string $class): self
	{
		/* ... */
	}


	/**
	 * Renders the grid with the specified set of data rows.
	 *
	 * Expects an indexed array with associative array entries
	 * containing key => value pairs of column data, or entry
	 * objects, or a mix of both.
	 *
	 * If you need to customize individual rows in the grid, you
	 * have the possibility to create entry objects manually,
	 * and mix these into the set of entries.
	 *
	 * Example:
	 *
	 * <pre>
	 * $entries = array();
	 *
	 * // add a traditional entry
	 * $entries[] = array(
	 *    'title' => 'First product',
	 *    'state' => 'Published'
	 * );
	 *
	 * // create a custom entry and give the table row a custom class
	 * $entries[] = $datagrid->createEntry(array(
	 *    'title' => 'Second product',
	 *    'state' => 'Draft'
	 * ))->addClass('custom-class');
	 *
	 * $datagrid->render($entries);
	 * </pre>
	 *
	 * @param array<int,array<string,mixed>|UI_DataGrid_Entry> $entries
	 * @return string
	 * @throws Application_Exception
	 */
	public function render(array $entries): string
	{
		/* ... */
	}


	/**
	 * Renders a JS statement that can be used to submit the grid's form.
	 * @return string
	 */
	public function renderJSSubmitHandler(bool $simulate = false): string
	{
		/* ... */
	}


	public function renderHiddenVars(): string
	{
		/* ... */
	}


	/**
	 * Configures the data grid using the specified filter settings and filter criteria.
	 *
	 * @param FilterSettingsInterface $settings
	 * @param FilterCriteriaInterface $criteria
	 * @return UI_DataGrid
	 */
	public function configure(FilterSettingsInterface $settings, FilterCriteriaInterface $criteria): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * Parses the specified set of entries and converts
	 * all array data sets to entry objects. Entries that
	 * are already entry objects are not modified.
	 *
	 * @param array<int,array<string,mixed>|UI_DataGrid_Entry> $entries
	 * @return UI_DataGrid_Entry[]
	 */
	public function filterAndSortEntries(array $entries): array
	{
		/* ... */
	}


	/**
	 * Creates an entry object for the grid: these are used internally
	 * to handle individual rows in the table.
	 *
	 * @param array<string, string|int|float|DateTime|StringableInterface|NULL> $data Associative array with key => value pairs for columns in the row.
	 * @return UI_DataGrid_Entry
	 */
	public function createEntry(array $data = []): UI_DataGrid_Entry
	{
		/* ... */
	}


	/**
	 * Creates a heading entry that can be used to create subtitles in a grid.
	 *
	 * @param string|StringableInterface $title
	 * @return UI_DataGrid_Entry_Heading
	 */
	public function createHeadingEntry(string|StringableInterface $title): UI_DataGrid_Entry_Heading
	{
		/* ... */
	}


	/**
	 * Creates a merged entry that spans the whole columns.
	 *
	 * @param string $title
	 * @return UI_DataGrid_Entry_Merged
	 */
	public function createMergedEntry(string $title): UI_DataGrid_Entry_Merged
	{
		/* ... */
	}


	public function getFormID(string $part = ''): string
	{
		/* ... */
	}


	public function getAction(): string
	{
		/* ... */
	}


	/**
	 * @return string[]
	 */
	public function getSelected(): array
	{
		/* ... */
	}


	public function isSubmitted(): bool
	{
		/* ... */
	}


	/**
	 * @return $this
	 * @deprecated
	 * @see UI_DataGrid::disableFooter()
	 */
	public function hideFooter(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 * @throws Exception
	 */
	public function disableFooter(): UI_DataGrid
	{
		/* ... */
	}


	public function enableFooter(): UI_DataGrid
	{
		/* ... */
	}


	public function isFooterEnabled(): bool
	{
		/* ... */
	}


	/**
	 * Ensures that the primary key name has been set.
	 * Throws an exception otherwise.
	 *
	 * @throws Application_Exception
	 */
	public function requirePrimaryName(): void
	{
		/* ... */
	}


	public function getValidActions(): array
	{
		/* ... */
	}


	public function getTotal(): int
	{
		/* ... */
	}


	public function getTotalUnfiltered(): ?int
	{
		/* ... */
	}


	/**
	 * Counts the amount of items that have been added to the
	 * grid. Note that this does not necessarily match the actual
	 * amount of rows, since these can be excluded from the count.
	 *
	 * @return int
	 * @see UI_DataGrid_Entry::isCountable()
	 */
	public function countEntries(): int
	{
		/* ... */
	}


	/**
	 * @return int
	 */
	public function countPages(): int
	{
		/* ... */
	}


	public function getPage(): int
	{
		/* ... */
	}


	public function setTotal(int $total): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * Sets the total number of records without any filtering.
	 * If no set, assumes the total is the unfiltered total.
	 * Otherwise, displays information about filtered item counts
	 * as needed.
	 *
	 * Note: set automatically if a filter criteria instance is provided.
	 *
	 * @param integer $total
	 * @return UI_DataGrid
	 */
	public function setTotalUnfiltered(int $total): UI_DataGrid
	{
		/* ... */
	}


	public function configureFromFilters(FilterCriteriaInterface $criteria): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * @deprecated
	 * @return $this
	 * @throws Exception
	 * @see UI_DataGrid::disableHeader()
	 */
	public function hideHeader(): UI_DataGrid
	{
		/* ... */
	}


	public function disableHeader(): UI_DataGrid
	{
		/* ... */
	}


	public function enableHeader(): UI_DataGrid
	{
		/* ... */
	}


	public function isHeaderEnabled(): bool
	{
		/* ... */
	}


	public function renderCells(UI_DataGrid_Entry $cell, bool $register = true): string
	{
		/* ... */
	}


	/**
	 * Executes the action callbacks if the data grid has been
	 * submitted, an action has been selected, and any action
	 * callbacks have been defined. Use this to automate the
	 * handling of actions.
	 */
	public function executeCallbacks(): UI_DataGrid
	{
		/* ... */
	}


	/**
	 * Checks whether the grid is currently in batch processing mode.
	 * This is different from the list being in AJAX mode:
	 * The list can be in batch processing mode but not in AJAX mode.
	 *
	 * @return boolean
	 */
	public function isBatchProcessing(): bool
	{
		/* ... */
	}


	public function isBatchComplete(): bool
	{
		/* ... */
	}


	/**
	 * Retrieves the currently selected action, if any.
	 * @return UI_DataGrid_Action|NULL
	 */
	public function getActiveAction(): ?UI_DataGrid_Action
	{
		/* ... */
	}


	public function isAllSelected(): bool
	{
		/* ... */
	}


	/**
	 * Whether the grid is currently in AJAX mode.
	 * @return boolean
	 */
	public function isAjax(): bool
	{
		/* ... */
	}


	/**
	 * Sets the optional title for the grid.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $title
	 * @throws UI_Exception
	 */
	public function setTitle($title): self
	{
		/* ... */
	}


	/**
	 * Sets the title of the table when it is shown in the full view mode
	 * (which is only available when the column controls are enabled).
	 *
	 * @param string|number|StringableInterface|NULL $title
	 * @return $this
	 */
	public function setFullViewTitle($title): self
	{
		/* ... */
	}


	/**
	 * Sets that the visible entries in the grid can be sorted
	 * clientside. Requires a clientside object name to be set
	 * that will handle the sorting events for the list, as well
	 * as provide additional configuration options.
	 *
	 * @param string|StringableInterface $clientsideHandler The name of a clientside variable holding the sorting events handler object
	 * @param string|NULL $primaryKeyName The name of the primary key in the records. Optional only if set separately.
	 * @return $this
	 */
	public function makeEntriesSortable($clientsideHandler, ?string $primaryKeyName = null): self
	{
		/* ... */
	}


	/**
	 * Sets that elements may be dragged into the list to add new
	 * entries.
	 *
	 * @param string|StringableInterface $clientsideHandler The name of a clientside variable holding the droppable events handler object
	 * @param string|NULL $primaryKeyName The name of the primary key in the records. Optional only if set separately.
	 * @return $this
	 */
	public function makeEntriesDroppable($clientsideHandler, ?string $primaryKeyName = null): self
	{
		/* ... */
	}


	/**
	 * Checks whether the sortable entries feature is enabled.
	 * @return boolean
	 */
	public function isEntriesSortable(): bool
	{
		/* ... */
	}


	/**
	 * @return string|NULL
	 */
	public function getOrderBy(): ?string
	{
		/* ... */
	}


	/**
	 * Retrieves the selected direction in which to sort the grid.
	 *
	 * @return string asc|desc
	 */
	public function getOrderDir()
	{
		/* ... */
	}


	/**
	 * @param string $dir
	 * @return $this
	 */
	public function setDefaultOrderDir(string $dir): self
	{
		/* ... */
	}


	/**
	 * Sets the column to use as default sorting column.
	 * @param UI_DataGrid_Column $column
	 * @return $this
	 */
	public function setDefaultSortColumn(UI_DataGrid_Column $column, string $dir = 'ASC'): self
	{
		/* ... */
	}


	/**
	 * Adds the java scripts and stylesheets required to use the
	 * data grid support clientside to build grids with the API.
	 */
	public static function addClientSupport(): void
	{
		/* ... */
	}


	/**
	 * Configures the data grid for the administration screen,
	 * by setting all required hidden variables to stay on the
	 * current page when using the pager.
	 *
	 * @param AdminScreenInterface $screen
	 * @return $this
	 * @throws Application_Exception
	 */
	public function configureForScreen(AdminScreenInterface $screen): self
	{
		/* ... */
	}


	/**
	 * @param string $footerText
	 */
	public function setFooterCountText(string $footerText): void
	{
		/* ... */
	}


	/**
	 * @param int $from
	 * @param int $to
	 * @param int $total
	 * @return string
	 */
	public function getFooterCountText(int $from, int $to, int $total): string
	{
		/* ... */
	}


	/**
	 * Sets the `action` parameter of the data grid's form.
	 *
	 * @param string $dispatcher
	 * @return $this
	 */
	public function setDispatcher(string $dispatcher): UI_DataGrid
	{
		/* ... */
	}


	public function getRefreshURL(array $params = []): string
	{
		/* ... */
	}


	/**
	 * @return UI_DataGrid_Column[]
	 */
	public function getAllColumns(): array
	{
		/* ... */
	}


	/**
	 * @return UI_DataGrid_Column[]
	 */
	public function getValidColumns(): array
	{
		/* ... */
	}


	public function requireColumnByName(string $columnName): UI_DataGrid_Column
	{
		/* ... */
	}


	public function resetSettings(): void
	{
		/* ... */
	}


	/**
	 * Turns off the default behavior of tables to fill 100%
	 * of the available space, making the grid use only the
	 * space that its columns require.
	 *
	 * @return $this
	 */
	public function makeAutoWidth(): self
	{
		/* ... */
	}


	/**
	 * @return string[]
	 */
	public function getActionNames(): array
	{
		/* ... */
	}


	public function actionExists(string $name): bool
	{
		/* ... */
	}


	public function getActionByName(string $actionName): UI_DataGrid_Action
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/DataGrid/Action.php`

```php
namespace ;

use AppUtils\Interfaces\ClassableInterface as ClassableInterface;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\NamedClosure as NamedClosure;
use AppUtils\OutputBuffering as OutputBuffering;
use AppUtils\OutputBuffering_Exception as OutputBuffering_Exception;
use AppUtils\Traits\ClassableTrait as ClassableTrait;
use Application\Application as Application;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use Utilities\CallableContainer as CallableContainer;

abstract class UI_DataGrid_Action implements Application_Interfaces_Iconizable, UI_Interfaces_Conditional, UI_Renderable_Interface, ClassableInterface
{
	use Application_Traits_Iconizable;
	use UI_Traits_RenderableGeneric;
	use UI_Traits_Conditional;
	use ClassableTrait;

	public function getUI(): UI
	{
		/* ... */
	}


	public function getID(): string
	{
		/* ... */
	}


	/**
	 * Retrieves the identifying name of the action.
	 * @return string
	 */
	public function getName(): string
	{
		/* ... */
	}


	/**
	 * Allows automating acting on a submitted list action: the callback
	 * will be called if the user selects this action.
	 *
	 * The callback gets the following parameters:
	 *
	 * - This list action object instance
	 * - The selected item IDs
	 * - [Optional arguments]
	 *
	 * @param callable(UI_DataGrid_Action, array, mixed...): void $callback The callback to use.
	 * @param array<int,mixed> $arguments Optional list of arguments to include in the callback.
	 * @return $this
	 */
	public function setCallback(callable $callback, array $arguments = []): self
	{
		/* ... */
	}


	/**
	 * Disables the "Select all entries" functionality
	 * for this list action.
	 *
	 * @return $this
	 */
	public function disableSelectAll(): self
	{
		/* ... */
	}


	public function isSelectAllEnabled(): bool
	{
		/* ... */
	}


	/**
	 * @param string $class
	 * @return $this
	 */
	public function addLIClass(string $class): self
	{
		/* ... */
	}


	/**
	 * Renders the markup for the action, to be included in the action
	 * drop-down menu in the data grid.
	 *
	 * @return string
	 * @throws OutputBuffering_Exception
	 */
	public function render(): string
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeDangerous(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeSuccess(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeDeveloper(): self
	{
		/* ... */
	}


	/**
	 * Checks whether this is the last batch of actions to
	 * execute when the user selected all entries in a
	 * datagrid.
	 *
	 * Note: this always returns true when not in select all mode.
	 *
	 * @return boolean
	 */
	public function isLastBatch(): bool
	{
		/* ... */
	}


	/**
	 * Executes this action's callback, if any. If no
	 * callback has been set using the {@link setCallback()}
	 * method, this will not do anything.
	 *
	 * @param bool $isLastBatch
	 * @return $this
	 */
	public function executeCallback(bool $isLastBatch = false)
	{
		/* ... */
	}


	public function getSelectedValues(): array
	{
		/* ... */
	}


	/**
	 * Sets a javascript method to call when the link is clicked.
	 * Note: the action does not get submitted serverside anymore,
	 * it must be handled entirely clientside.
	 *
	 * The specified method gets two parameters:
	 *
	 * - An indexed array with all selected list entries.
	 * - The datagrid object instance
	 *
	 * @param string $methodName Only the method name, e.g. "DoSomething".
	 * @return $this
	 */
	public function setJSMethod(string $methodName)
	{
		/* ... */
	}


	/**
	 * Sets a tooltip to show when hovering over the action menu item.
	 * @param string|int|UI_Renderable_Interface $text
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setTooltip($text)
	{
		/* ... */
	}


	/**
	 * Adds a confirmation message to the action: a message dialog will
	 * be shown before the action is submitted.
	 *
	 * @param string|int|UI_Renderable_Interface $message The confirmation message. HTML is allowed.
	 * @param boolean $withInput Whether this is a critical message for which the user must type a confirmation string.
	 * @return $this
	 * @throws UI_Exception
	 */
	public function makeConfirm($message, bool $withInput = false)
	{
		/* ... */
	}


	/**
	 * Sets a freeform parameter: these can be used to
	 * store data that the callback function can use.
	 * It has no functionality beyond storing data.
	 *
	 * NOTE: The value must be convertible to a string.
	 * When using the select all feature, the parameters
	 * are passed on via AJAX.
	 *
	 * @param string $name
	 * @param string|number|UI_Renderable_Interface $value
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setParam(string $name, $value)
	{
		/* ... */
	}


	/**
	 * Retrieves a previously added parameter, if any.
	 *
	 * @param string $name
	 * @param string $default
	 * @return string
	 */
	public function getParam(string $name, string $default = ''): string
	{
		/* ... */
	}


	/**
	 * @return array<string,string>
	 */
	public function getParams(): array
	{
		/* ... */
	}


	/**
	 * Creates a configurable redirect message for the specified
	 * number of affected records: determines the message that needs
	 * to be added, and redirects to the target URL.
	 *
	 * @param string|AdminURLInterface $redirectURL
	 * @return UI_DataGrid_RedirectMessage
	 */
	public function createRedirectMessage($redirectURL): UI_DataGrid_RedirectMessage
	{
		/* ... */
	}


	/**
	 * Sets the form's target to use if this action is executed,
	 * overriding the target set on the main data grid.
	 *
	 * This allows each action to be submitted differently as
	 * needed. For example, the grid itself may be set to open
	 * in a new tab, but a specific action may need to open in
	 * the same tab.
	 *
	 * @param string|NULL $target
	 * @return self
	 */
	public function setFormTarget(?string $target): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/DataGrid/Action/Confirm.php`

```php
namespace ;

class UI_DataGrid_Action_Confirm extends UI_DataGrid_Action_Default
{
}


```
###  Path: `/src/classes/UI/DataGrid/Action/Confirm.php`

```php
namespace ;

class UI_DataGrid_Action_Confirm extends UI_DataGrid_Action_Default
{
}


```
###  Path: `/src/classes/UI/DataGrid/Action/Default.php`

```php
namespace ;

class UI_DataGrid_Action_Default extends UI_DataGrid_Action
{
}


```
###  Path: `/src/classes/UI/DataGrid/Action/Default.php`

```php
namespace ;

class UI_DataGrid_Action_Default extends UI_DataGrid_Action
{
}


```
###  Path: `/src/classes/UI/DataGrid/Action/Javascript.php`

```php
namespace ;

class UI_DataGrid_Action_Javascript extends UI_DataGrid_Action
{
}


```
###  Path: `/src/classes/UI/DataGrid/Action/Javascript.php`

```php
namespace ;

class UI_DataGrid_Action_Javascript extends UI_DataGrid_Action
{
}


```
###  Path: `/src/classes/UI/DataGrid/Action/Separator.php`

```php
namespace ;

class UI_DataGrid_Action_Separator extends UI_DataGrid_Action
{
	public function render(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/DataGrid/Action/Separator.php`

```php
namespace ;

class UI_DataGrid_Action_Separator extends UI_DataGrid_Action
{
	public function render(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/DataGrid/BaseListBuilder.php`

```php
namespace UI\DataGrid;

use AppUtils\ConvertHelper as ConvertHelper;
use AppUtils\Interfaces\OptionableInterface as OptionableInterface;
use AppUtils\Microtime as Microtime;
use AppUtils\Traits\OptionableTrait as OptionableTrait;
use Application\Exception\ApplicationException as ApplicationException;
use Application\FilterSettings\FilterSettingsInterface as FilterSettingsInterface;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use Application\Interfaces\FilterCriteriaInterface as FilterCriteriaInterface;
use Application_Driver as Application_Driver;
use Application_User as Application_User;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use DateTime as DateTime;
use UI as UI;
use UI\Interfaces\ListBuilderInterface as ListBuilderInterface;
use UI_DataGrid as UI_DataGrid;
use UI_DataGrid_Entry as UI_DataGrid_Entry;
use UI_DataGrid_Exception as UI_DataGrid_Exception;
use UI_Page_Sidebar as UI_Page_Sidebar;
use UI_Renderable_Interface as UI_Renderable_Interface;
use UI_Traits_RenderableGeneric as UI_Traits_RenderableGeneric;

/**
 * Helper class that is used to create a list of records
 * in an administration screen, with options to customize it.
 * It handles the collecting of data and the rendering.
 *
 * @package User Interface
 * @subpackage DataGrids
 */
abstract class BaseListBuilder implements OptionableInterface, UI_Renderable_Interface, ListBuilderInterface
{
	use OptionableTrait;
	use UI_Traits_RenderableGeneric;

	public const ERROR_LIST_ALREADY_INITIALIZED = 86001;

	public function getDataGrid(): UI_DataGrid
	{
		/* ... */
	}


	public function isColumnEnabled(string $colName): bool
	{
		/* ... */
	}


	/**
	 * @param string $colName
	 * @return $this
	 */
	public function disableColumn(string $colName): self
	{
		/* ... */
	}


	public function getFilterCriteria(): FilterCriteriaInterface
	{
		/* ... */
	}


	public function getFilterSettings(): ?FilterSettingsInterface
	{
		/* ... */
	}


	public function getFilteredCriteria(): FilterCriteriaInterface
	{
		/* ... */
	}


	public function debug(bool $enabled = true): self
	{
		/* ... */
	}


	/**
	 * @param bool $enabled
	 * @return $this
	 */
	public function enableAdvancedMode(bool $enabled): self
	{
		/* ... */
	}


	/**
	 * @param array<string,string> $vars
	 * @return $this
	 */
	public function addHiddenVars(array $vars): self
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param string|int|float|NULL $value
	 * @return $this
	 */
	public function addHiddenVar(string $name, $value): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function disableEntryActions(): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function disableMultiActions(): self
	{
		/* ... */
	}


	public function getGridID(): string
	{
		/* ... */
	}


	public function getUI(): UI
	{
		/* ... */
	}


	public function getDefaultOptions(): array
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	/**
	 * Sets the ID to use for the list and the filter settings.
	 * Lists that use the same ID share the same filter settings.
	 *
	 * @param string $id
	 * @return $this
	 * @throws UI_DataGrid_Exception
	 * @see BaseListBuilder::ERROR_LIST_ALREADY_INITIALIZED
	 */
	public function setListID(string $id): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function handleActions(): self
	{
		/* ... */
	}


	/**
	 * @param UI_Page_Sidebar $sidebar
	 * @return $this
	 */
	public function addFilterSettings(UI_Page_Sidebar $sidebar): self
	{
		/* ... */
	}


	public function renderDate(?DateTime $date = null): string
	{
		/* ... */
	}


	public function adjustLabel(string $label): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/DataGrid/Column.php`

```php
namespace ;

class UI_DataGrid_Column implements UI_Interfaces_Conditional
{
	use UI_Traits_Conditional;

	public const ERROR_SORT_DATA_COLUMN_MISSING = 17903;
	public const ERROR_UNKNOWN_OPTION_NAME = 17904;
	public const OPTION_HIDDEN = 'hidden';
	public const OPTION_SORT_CALLBACK = 'sortCallback';
	public const OPTION_SORT_DATA_COLUMN = 'sortDataColumn';
	public const OPTION_SORTABLE = 'sortable';
	public const OPTION_SORT_KEY = 'sortKey';
	public const OPTION_NOWRAP = 'nowrap';
	public const OPTION_WIDTH = 'width';
	public const OPTION_WIDTH_TYPE = 'width-type';
	public const OPTION_TOOLTIP = 'tooltip';
	public const OPTION_ALIGN = 'align';
	public const ROLE_ACTIONS = 'actions';
	public const ROLE_HEADING = 'heading';
	public const ROLE_CELL = 'cell';

	/**
	 * @return UI_DataGrid
	 */
	public function getDataGrid(): UI_DataGrid
	{
		/* ... */
	}


	public function getType(): string
	{
		/* ... */
	}


	public function getDataKey(): string
	{
		/* ... */
	}


	public function getTitle(): string
	{
		/* ... */
	}


	/**
	 * Forces contents of the column's cells not to break to a new line.
	 * @return $this
	 */
	public function setNowrap(): UI_DataGrid_Column
	{
		/* ... */
	}


	/**
	 * Aligns the contents of the column's cells to the center.
	 * @return $this
	 */
	public function alignCenter(): UI_DataGrid_Column
	{
		/* ... */
	}


	/**
	 * Adds a class that will be added to all cells in this column.
	 *
	 * @param string $class
	 * @return $this
	 */
	public function addClass(string $class): UI_DataGrid_Column
	{
		/* ... */
	}


	/**
	 * Aligns the contents of the column's cells to the right.
	 * @return $this
	 */
	public function alignRight(): UI_DataGrid_Column
	{
		/* ... */
	}


	/**
	 * Sets the tooltip text for the column: this will add the tooltip
	 * to the column header.
	 *
	 * @param string|number|UI_Renderable_Interface $text
	 * @return $this
	 */
	public function setTooltip($text): UI_DataGrid_Column
	{
		/* ... */
	}


	/**
	 * Makes the column as compact as possible. To avoid line
	 * breaks in texts, combine this with {@setNowrap()}.
	 * @return $this
	 */
	public function setCompact(): UI_DataGrid_Column
	{
		/* ... */
	}


	/**
	 * Sets the column width as a percentage value.
	 * @param int $width
	 * @return $this
	 */
	public function setWidthPercent(int $width): UI_DataGrid_Column
	{
		/* ... */
	}


	/**
	 * Sets the column width as a fixed pixel value.
	 * @param int $width
	 * @return $this
	 */
	public function setWidthPixels(int $width): UI_DataGrid_Column
	{
		/* ... */
	}


	/**
	 * Checks whether a width has been set for the column
	 * @return boolean
	 */
	public function hasWidth(): bool
	{
		/* ... */
	}


	/**
	 * Retrieves the type of width set for the column.
	 * This can be either "percent" or "pixels"
	 *
	 * @return string
	 */
	public function getWidthType(): string
	{
		/* ... */
	}


	public function getWidth(): int
	{
		/* ... */
	}


	public function resetWidth(): UI_DataGrid_Column
	{
		/* ... */
	}


	/**
	 * Sets this column as sortable, which will allow the user to
	 * sort by the contents of the column using clientside controls.
	 *
	 * @param string|NULL $dataKeyName The name of the data key to sort: use this if it is not the same as the column name.
	 * @return UI_DataGrid_Column
	 */
	public function setSortable(bool $default = false, ?string $dataKeyName = null): UI_DataGrid_Column
	{
		/* ... */
	}


	/**
	 * Sets a callback function to use to sort this column.
	 * Enables sorting the column.
	 *
	 * @param callable $callback
	 * @param string|NULL $dataColumn The name of the column in the data set to use as value for the sorting. Defaults to this column, but a different one can be specified as needed.
	 * @return $this
	 */
	public function setSortingCallback(callable $callback, ?string $dataColumn = null): UI_DataGrid_Column
	{
		/* ... */
	}


	public function setSortingDateTime(?string $dataColumn = null): UI_DataGrid_Column
	{
		/* ... */
	}


	public function setSortingNumeric(?string $dataColumn = null): UI_DataGrid_Column
	{
		/* ... */
	}


	public function setSortingString(?string $dataColumn = null): UI_DataGrid_Column
	{
		/* ... */
	}


	/**
	 * Called by the grid when the column has a sorting callback,
	 * to sort the entries manually.
	 *
	 * @param UI_DataGrid_Entry $entryA
	 * @param UI_DataGrid_Entry $entryB
	 * @return int
	 */
	public function callback_sortEntries(UI_DataGrid_Entry $entryA, UI_DataGrid_Entry $entryB)
	{
		/* ... */
	}


	/**
	 * Sets the column as hidden.
	 *
	 * Can be used to hide ID columns, for example.
	 *
	 * NOTE: This is a column option
	 *
	 * @return $this
	 */
	public function setHidden(): UI_DataGrid_Column
	{
		/* ... */
	}


	/**
	 * Sets a column option. Also see the dedicated methods that
	 * allow setting the options without having to know the option's
	 * name.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return $this
	 * @throws UI_DataGrid_Exception
	 * @see setHidden()
	 * @see setNowrap()
	 * @see alignRight()
	 * @see alignCenter()
	 * @see setSortable()
	 */
	public function setOption(string $name, $value): UI_DataGrid_Column
	{
		/* ... */
	}


	/**
	 * Checks whether this column is hidden (either via
	 * the option or by the user in their configuration).
	 *
	 * @return boolean
	 * @see self::isHiddenForUser()
	 */
	public function isHidden(): bool
	{
		/* ... */
	}


	/**
	 * Whether this column has been specifically hidden using
	 * {@see self::setHidden()} (as compared to hidden by the
	 * user in the grid's configuration).
	 *
	 * @return bool
	 * @see self::isHiddenForUser()
	 */
	public function isHiddenByOption(): bool
	{
		/* ... */
	}


	public function renderCell(UI_DataGrid_Entry $entry): string
	{
		/* ... */
	}


	public function renderHeaderCell(bool $duplicate = false): string
	{
		/* ... */
	}


	/**
	 * Checks whether this column is the one the list is currently
	 * being ordered by.
	 *
	 * @return boolean
	 */
	public function isSorted(): bool
	{
		/* ... */
	}


	/**
	 * Reshuffles the grid's columns to insert this column at
	 * the desired column number, starting at 1.
	 *
	 * @param integer $position
	 * @return $this
	 * @deprecated Use the custom ordering instead.
	 */
	public function moveTo(int $position): UI_DataGrid_Column
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function roleHeading(): UI_DataGrid_Column
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function roleActions(): UI_DataGrid_Column
	{
		/* ... */
	}


	public function getNumber(): int
	{
		/* ... */
	}


	public function getRole(): string
	{
		/* ... */
	}


	public function getObjectName(): string
	{
		/* ... */
	}


	public function injectJavascript(UI $ui, string $gridName): void
	{
		/* ... */
	}


	/**
	 * Makes this column editable: requires a clientside handler object
	 * that will be used anytime a user clicks on an editable cell to
	 * handle rendering the edit controls and saving the changes.
	 *
	 * NOTE: Requires the grid's primary key name to be set when
	 * calling the method.
	 *
	 * @param string $clientClassName
	 * @return UI_DataGrid_Column
	 */
	public function setEditable(string $clientClassName): UI_DataGrid_Column
	{
		/* ... */
	}


	/**
	 * Whether the cells in this column are editable.
	 * @return boolean
	 */
	public function isEditable(): bool
	{
		/* ... */
	}


	/**
	 * Checks whether the role of this column is for
	 * actions.
	 *
	 * @return boolean
	 */
	public function isAction(): bool
	{
		/* ... */
	}


	/**
	 * Checks whether the column is sortable.
	 * @return boolean
	 */
	public function isSortable(): bool
	{
		/* ... */
	}


	/**
	 * Retrieves the name of the data key by which this column should be sorted.
	 * @return string|NULL
	 * @see setSortable()
	 */
	public function getOrderKey(): ?string
	{
		/* ... */
	}


	public function hasSortingCallback(): bool
	{
		/* ... */
	}


	public function setHiddenForUser(bool $hidden, ?Application_User $user = null): UI_DataGrid_Column
	{
		/* ... */
	}


	public function isHiddenForUser(?Application_User $user = null): bool
	{
		/* ... */
	}


	/**
	 * Sets a custom ordering index for the column.
	 * @param int $order
	 * @return $this
	 */
	public function setOrder(int $order): self
	{
		/* ... */
	}


	/**
	 * Used for ordering the column using a custom, user-based
	 * order. If no custom order has been specified, uses
	 * {@see self::getNumber()}, the order in which the column
	 * was added to the grid.
	 *
	 * @return int
	 */
	public function getOrder(): int
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/DataGrid/Column/ColumnSettingStorage.php`

```php
namespace ;

use AppUtils\ConvertHelper as ConvertHelper;
use Application\Application as Application;

/**
 * Specialized data grid column storage class used
 * to store column settings for a specific user.
 *
 * It uses the user settings to store the column settings,
 * using data key prefixes based on the column and grid IDs.
 *
 * @package User Interface
 * @subpackage Data Grids
 */
class ColumnSettingStorage
{
	public const SETTING_HIDDEN = 'hidden';
	public const SETTING_ORDER = 'order';

	public function setHiddenForUser(bool $hidden, ?Application_User $user = null): ColumnSettingStorage
	{
		/* ... */
	}


	public function isHiddenForUser(?Application_User $user = null): bool
	{
		/* ... */
	}


	public function setSetting(string $name, string $value, ?Application_User $user): ColumnSettingStorage
	{
		/* ... */
	}


	public function getSetting(string $name, ?Application_User $user): string
	{
		/* ... */
	}


	public function setOrder(int $order, ?Application_User $user = null): self
	{
		/* ... */
	}


	public function getOrder(?Application_User $user = null): int
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/DataGrid/Column/ColumnSettingStorage.php`

```php
namespace ;

use AppUtils\ConvertHelper as ConvertHelper;
use Application\Application as Application;

/**
 * Specialized data grid column storage class used
 * to store column settings for a specific user.
 *
 * It uses the user settings to store the column settings,
 * using data key prefixes based on the column and grid IDs.
 *
 * @package User Interface
 * @subpackage Data Grids
 */
class ColumnSettingStorage
{
	public const SETTING_HIDDEN = 'hidden';
	public const SETTING_ORDER = 'order';

	public function setHiddenForUser(bool $hidden, ?Application_User $user = null): ColumnSettingStorage
	{
		/* ... */
	}


	public function isHiddenForUser(?Application_User $user = null): bool
	{
		/* ... */
	}


	public function setSetting(string $name, string $value, ?Application_User $user): ColumnSettingStorage
	{
		/* ... */
	}


	public function getSetting(string $name, ?Application_User $user): string
	{
		/* ... */
	}


	public function setOrder(int $order, ?Application_User $user = null): self
	{
		/* ... */
	}


	public function getOrder(?Application_User $user = null): int
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/DataGrid/Column/MultiSelect.php`

```php
namespace ;

use AppUtils\OutputBuffering as OutputBuffering;

class UI_DataGrid_Column_MultiSelect extends UI_DataGrid_Column
{
	public const ERROR_COLUMN_CANNOT_BE_EDITABLE = 513131;

	public function getType(): string
	{
		/* ... */
	}


	public function renderCell(UI_DataGrid_Entry $entry): string
	{
		/* ... */
	}


	public function renderHeaderCell(bool $duplicate = false): string
	{
		/* ... */
	}


	/**
	 * @param string $clientClassName
	 * @return UI_DataGrid_Column
	 * @throws UI_DataGrid_Exception
	 */
	public function setEditable(string $clientClassName): UI_DataGrid_Column
	{
		/* ... */
	}


	public function isAction(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/DataGrid/Column/MultiSelect.php`

```php
namespace ;

use AppUtils\OutputBuffering as OutputBuffering;

class UI_DataGrid_Column_MultiSelect extends UI_DataGrid_Column
{
	public const ERROR_COLUMN_CANNOT_BE_EDITABLE = 513131;

	public function getType(): string
	{
		/* ... */
	}


	public function renderCell(UI_DataGrid_Entry $entry): string
	{
		/* ... */
	}


	public function renderHeaderCell(bool $duplicate = false): string
	{
		/* ... */
	}


	/**
	 * @param string $clientClassName
	 * @return UI_DataGrid_Column
	 * @throws UI_DataGrid_Exception
	 */
	public function setEditable(string $clientClassName): UI_DataGrid_Column
	{
		/* ... */
	}


	public function isAction(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/DataGrid/Entry.php`

```php
namespace ;

use AppUtils\ConvertHelper as ConvertHelper;
use AppUtils\HTMLTag as HTMLTag;
use AppUtils\Interfaces\ClassableInterface as ClassableInterface;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\Traits\ClassableTrait as ClassableTrait;
use UI\DataGrid\EntryClientCommands as EntryClientCommands;

/**
 * Container for a single row in a data grid. Offers an API
 * to customize entries, and is used for some advanced features
 * that require setting custom row CSS classes, for example.
 *
 * @package User Interface
 * @subpackage Data Grids
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @implements ArrayAccess<string,mixed>
 */
class UI_DataGrid_Entry implements ClassableInterface, ArrayAccess
{
	use ClassableTrait;

	public const ERROR_MISSING_PRIMARY_VALUE = 536001;

	public function getID(): string
	{
		/* ... */
	}


	public function getCheckboxID(): string
	{
		/* ... */
	}


	/**
	 * Gets the helper class used to access client-side commands
	 * related to this data grid.
	 *
	 * @return EntryClientCommands
	 */
	public function clientCommands(): EntryClientCommands
	{
		/* ... */
	}


	public function renderCheckboxLabel(string $label): string
	{
		/* ... */
	}


	/**
	 * Retrieves the data record for this entry.
	 * @return array<int|string,mixed>
	 */
	public function getData(): array
	{
		/* ... */
	}


	/**
	 * Merges the specified data set with the existing entry data.
	 * @param array<int|string,mixed> $data
	 * @return $this
	 */
	public function setData(array $data): self
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param mixed $value
	 * @return $this
	 */
	public function setColumnValue(string $name, mixed $value): self
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeNonCountable(): self
	{
		/* ... */
	}


	/**
	 * Whether this entry can be included in the entries' total.
	 *
	 * @return bool
	 * @see UI_DataGrid::countEntries()
	 */
	public function isCountable(): bool
	{
		/* ... */
	}


	/**
	 * Styles the row as a warning entry.
	 * @return $this
	 */
	public function makeWarning(): self
	{
		/* ... */
	}


	/**
	 * Styles the row as a success entry.
	 * @return $this
	 */
	public function makeSuccess(): self
	{
		/* ... */
	}


	/**
	 * Avoids this row from being reordered. Note that this is only
	 * relevant if the data grid's entries sorting feature has been
	 * enabled. Otherwise, it is simply ignored.
	 *
	 * Additional note: this only disables the dragging of the row.
	 * You have to implement any logic beyond this in your clientside
	 * handler class, as it does not prevent the user from moving other
	 * rows above or below a non-sortable row, effectively moving it
	 * anyway even if indirectly.
	 *
	 * @return $this
	 */
	public function makeNonSortable(): self
	{
		/* ... */
	}


	/**
	 * Selects the entry, so it will be pre-selected in the list
	 * if the data grid supports multiple selection.
	 *
	 * @param bool $select
	 * @return $this
	 */
	public function select(bool $select = true): self
	{
		/* ... */
	}


	public function isSelected(): bool
	{
		/* ... */
	}


	/**
	 * @return mixed|null
	 */
	public function getPrimaryValue(): mixed
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @return mixed|null
	 */
	public function getValue(string $name): mixed
	{
		/* ... */
	}


	public function getValueForColumn(UI_DataGrid_Column $column): string
	{
		/* ... */
	}


	public function var2cellText($value): string
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	/**
	 * If a primary field is present in the grid, adds the
	 * `data-refid` attribute to the row, containing the value
	 * of the primary field for the entry. This is used on the
	 * client side to access the primary value.
	 *
	 * @return string
	 * @throws UI_DataGrid_Exception
	 */
	public function getReferenceID(): string
	{
		/* ... */
	}


	/**
	 * @param string $offset
	 * @return bool
	 */
	public function offsetExists($offset): bool
	{
		/* ... */
	}


	/**
	 * @param string $offset
	 * @return mixed|null
	 */
	public function offsetGet($offset): mixed
	{
		/* ... */
	}


	/**
	 * @param string $offset
	 * @param mixed $value
	 * @return void
	 */
	public function offsetSet($offset, mixed $value): void
	{
		/* ... */
	}


	/**
	 * @param string $offset
	 * @return void
	 */
	public function offsetUnset($offset): void
	{
		/* ... */
	}


	/**
	 * Sets an attribute for the row element.
	 *
	 * @param string $attribute
	 * @param string $value
	 * @return $this
	 */
	public function attr(string $attribute, string $value): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/DataGrid/Entry/Heading.php`

```php
namespace ;

use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\OutputBuffering as OutputBuffering;

class UI_DataGrid_Entry_Heading extends UI_DataGrid_Entry
{
	public function isCountable(): bool
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	public function setSubline(string|StringableInterface|null $subline): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/DataGrid/Entry/Heading.php`

```php
namespace ;

use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\OutputBuffering as OutputBuffering;

class UI_DataGrid_Entry_Heading extends UI_DataGrid_Entry
{
	public function isCountable(): bool
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	public function setSubline(string|StringableInterface|null $subline): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/DataGrid/Entry/Merged.php`

```php
namespace ;

class UI_DataGrid_Entry_Merged extends UI_DataGrid_Entry
{
	public function render(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/DataGrid/Entry/Merged.php`

```php
namespace ;

class UI_DataGrid_Entry_Merged extends UI_DataGrid_Entry
{
	public function render(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/DataGrid/EntryClientCommands.php`

```php
namespace UI\DataGrid;

use UI_DataGrid_Entry as UI_DataGrid_Entry;

/**
 * Helper class used to generate clientside JavaScript
 * statements for functions of the data grid entry.
 *
 * # Usage
 *
 * Get an instance using the entry's {@see UI_DataGrid_Entry::clientCommands()}
 * method.
 *
 * @package User Interface
 * @subpackage Data Grids
 */
class EntryClientCommands
{
	/**
	 * JS command to select the entry's checkbox.
	 * @return string
	 */
	public function select(): string
	{
		/* ... */
	}


	/**
	 * JS command to deselect the entry's checkbox.
	 * @return string
	 */
	public function deselect(): string
	{
		/* ... */
	}


	/**
	 * JS command to toggle the selected status of the entry's checkbox.
	 * @return string
	 */
	public function toggle(): string
	{
		/* ... */
	}


	/**
	 * JS command to scroll to this data grid row in the page.
	 * @return string
	 */
	public function scrollTo(): string
	{
		/* ... */
	}


	/**
	 * Gets the jQuery selector for the entry's checkbox element.
	 * @return string
	 */
	public function getCheckboxSelector(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/DataGrid/Exception.php`

```php
namespace ;

class UI_DataGrid_Exception extends UI_Exception
{
}


```
###  Path: `/src/classes/UI/DataGrid/GridClientCommands.php`

```php
namespace UI\DataGrid;

use UI_DataGrid as UI_DataGrid;

/**
 * Helper class used to generate clientside JavaScript
 * statements for functions of the data grid.
 *
 * # Usage
 *
 * Get an instance using the grid's {@see UI_DataGrid::clientCommands()}
 * method.
 *
 * @package User Interface
 * @subpackage Data Grids
 */
class GridClientCommands
{
	/**
	 * The name of the clientside variable referencing the grid object.
	 * @return string
	 */
	public function getObjectName(): string
	{
		/* ... */
	}


	/**
	 * @param string $actionName
	 * @return string
	 */
	public function submitAction(string $actionName): string
	{
		/* ... */
	}


	public function toggleSelection(): string
	{
		/* ... */
	}


	public function toggleSelectAll(): string
	{
		/* ... */
	}


	public function selectAll(): string
	{
		/* ... */
	}


	public function deselectAll(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/DataGrid/GridConfigurator.php`

```php
namespace UI\DataGrid;

use AppUtils\ConvertHelper\JSONConverter as JSONConverter;
use AppUtils\OutputBuffering as OutputBuffering;
use Application\AppFactory as AppFactory;
use Application_Request as Application_Request;
use DBHelper as DBHelper;
use UI as UI;
use UI_DataGrid as UI_DataGrid;
use UI_DataGrid_Exception as UI_DataGrid_Exception;
use UI_Renderable_Interface as UI_Renderable_Interface;
use UI_Traits_RenderableGeneric as UI_Traits_RenderableGeneric;

/**
 * Handles the configuration UI for a data grid.
 *
 * When enabled, it replaces the data grid in situ
 * with a configuration interface that allows the user
 * to reorder columns and toggle their visibility.
 *
 * @package User Interface
 * @subpackage Data Grids
 */
class GridConfigurator implements UI_Renderable_Interface
{
	use UI_Traits_RenderableGeneric;

	public const ERROR_MISSING_INVALID_COLUMNS = 162801;
	public const REQUEST_PARAM_SAVE = 'save-configuration';
	public const REQUEST_PARAM_COLUMNS = 'columns';
	public const REQUEST_PARAM_VISIBILITY = 'visibility';
	public const REQUEST_PARAM_RESET_GRID = 'reset_data_grid';

	public function getUI(): UI
	{
		/* ... */
	}


	public function getSectionID(): string
	{
		/* ... */
	}


	public function process(): bool
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/DataGrid/ListBuilder/ListBuilderScreenInterface.php`

```php
namespace UI\DataGrid\ListBuilder;

use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use UI\Interfaces\ListBuilderInterface as ListBuilderInterface;

/**
 * Interface for admin screens that use a {@see ListBuilderInterface}
 * instance to generate a data grid of items.
 *
 * @package User Interface
 * @subpackage List Builder
 * @see ListBuilderScreenTrait
 */
interface ListBuilderScreenInterface extends AdminScreenInterface
{
	/**
	 * Creates an instance of the list builder to use.
	 * @return ListBuilderInterface
	 */
	public function createListBuilder(): ListBuilderInterface;


	/**
	 * Gets the ID of the list to be displayed, which can
	 * be used to share its settings.
	 *
	 * @return string
	 */
	public function getListID(): string;


	/**
	 * Gets the fully configured ListBuilder instance.
	 * @return ListBuilderInterface
	 */
	public function getBuilder(): ListBuilderInterface;
}


```
###  Path: `/src/classes/UI/DataGrid/ListBuilder/ListBuilderScreenInterface.php`

```php
namespace UI\DataGrid\ListBuilder;

use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use UI\Interfaces\ListBuilderInterface as ListBuilderInterface;

/**
 * Interface for admin screens that use a {@see ListBuilderInterface}
 * instance to generate a data grid of items.
 *
 * @package User Interface
 * @subpackage List Builder
 * @see ListBuilderScreenTrait
 */
interface ListBuilderScreenInterface extends AdminScreenInterface
{
	/**
	 * Creates an instance of the list builder to use.
	 * @return ListBuilderInterface
	 */
	public function createListBuilder(): ListBuilderInterface;


	/**
	 * Gets the ID of the list to be displayed, which can
	 * be used to share its settings.
	 *
	 * @return string
	 */
	public function getListID(): string;


	/**
	 * Gets the fully configured ListBuilder instance.
	 * @return ListBuilderInterface
	 */
	public function getBuilder(): ListBuilderInterface;
}


```
###  Path: `/src/classes/UI/DataGrid/ListBuilder/ListBuilderScreenTrait.php`

```php
namespace UI\DataGrid\ListBuilder;

use UI\Interfaces\ListBuilderInterface as ListBuilderInterface;
use UI_Themes_Theme_ContentRenderer as UI_Themes_Theme_ContentRenderer;

/**
 * Trait used to help implement the {@see ListBuilderScreenInterface}.
 *
 * @package User Interface
 * @subpackage List Builder
 * @see ListBuilderScreenInterface
 */
trait ListBuilderScreenTrait
{
	public function getBuilder(): ListBuilderInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/DataGrid/ListBuilder/ListBuilderScreenTrait.php`

```php
namespace UI\DataGrid\ListBuilder;

use UI\Interfaces\ListBuilderInterface as ListBuilderInterface;
use UI_Themes_Theme_ContentRenderer as UI_Themes_Theme_ContentRenderer;

/**
 * Trait used to help implement the {@see ListBuilderScreenInterface}.
 *
 * @package User Interface
 * @subpackage List Builder
 * @see ListBuilderScreenInterface
 */
trait ListBuilderScreenTrait
{
	public function getBuilder(): ListBuilderInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/DataGrid/RedirectMessage.php`

```php
namespace ;

use Application\Application as Application;
use Application\Driver\DriverException as DriverException;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use DBHelper\BaseCollection\DBHelperCollectionInterface as DBHelperCollectionInterface;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

/**
 * Class used to handle messages after a datagrid action:
 * Chooses the right message to display according to the
 * amount of records that were affected by the operation,
 * and redirects to the target URL, either with a success
 * styled message, or an info styled message if none were
 * affected.
 *
 * @package UI
 * @subpackage DataGrid
 */
class UI_DataGrid_RedirectMessage
{
	/**
	 * Sets a callback to use to check whether a record should be
	 * included or not when using the `processDeleteDBRecords`
	 * method. The method should return `true` if it can be deleted,
	 * and `false` otherwise.
	 *
	 * Gets the record as a single parameter.
	 *
	 * @param callable $callback
	 * @return $this
	 * @throws Application_Exception
	 */
	public function setDeletableCallback($callback): UI_DataGrid_RedirectMessage
	{
		/* ... */
	}


	/**
	 * Sets the message text to use when no records
	 * were affected.
	 *
	 * Possible placeholders to use in the text:
	 * - $amount
	 * - $time
	 *
	 * @param string $message
	 * @return UI_DataGrid_RedirectMessage
	 */
	public function none(string $message): UI_DataGrid_RedirectMessage
	{
		/* ... */
	}


	/**
	 * Sets the message text to use when a single
	 * record was affected.
	 *
	 * Possible placeholders to use in the text:
	 *
	 * - $amount
	 * - $time
	 * - $label
	 *
	 * @param string $message
	 * @return UI_DataGrid_RedirectMessage
	 */
	public function single(string $message): UI_DataGrid_RedirectMessage
	{
		/* ... */
	}


	/**
	 * Sets the message text to use when several
	 * record were affected.
	 *
	 * Possible placeholders to use in the text:
	 * - $amount
	 * - $time
	 *
	 * @param string $message
	 * @return UI_DataGrid_RedirectMessage
	 */
	public function multiple(string $message): UI_DataGrid_RedirectMessage
	{
		/* ... */
	}


	public function countAffected(): int
	{
		/* ... */
	}


	public function addAffected(string $label): UI_DataGrid_RedirectMessage
	{
		/* ... */
	}


	public function redirect()
	{
		/* ... */
	}


	public function getPlaceholders(): array
	{
		/* ... */
	}


	/**
	 * Automates deleting selected DBHelper records, when working with
	 * the DBHelper_Collection. Goes through the selected IDs, and deletes
	 * the relevant records withing a transaction.
	 *
	 * @param DBHelperCollectionInterface $collection
	 * @return UI_DataGrid_RedirectMessage
	 */
	public function processDeleteDBRecords(DBHelperCollectionInterface $collection): UI_DataGrid_RedirectMessage
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/DataGrid/Row.php`

```php
namespace ;

abstract class UI_DataGrid_Row
{
	abstract public function getEntry(): UI_DataGrid_Entry;
}


```
###  Path: `/src/classes/UI/DataGrid/Row/Sums.php`

```php
namespace ;

class UI_DataGrid_Row_Sums extends UI_DataGrid_Row
{
	/**
	 * Defines a column sum to be generated via a callback function.
	 *
	 * @param string|UI_DataGrid_Column $colNameOrInstance
	 * @param callable $callback
	 * @param mixed[] $args Any additional arguments for the callback.
	 * @return UI_DataGrid_Row_Sums
	 */
	public function makeCallback($colNameOrInstance, $callback, array $args = []): UI_DataGrid_Row_Sums
	{
		/* ... */
	}


	public function getEntry(): UI_DataGrid_Entry
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/DataGrid/Row/Sums.php`

```php
namespace ;

class UI_DataGrid_Row_Sums extends UI_DataGrid_Row
{
	/**
	 * Defines a column sum to be generated via a callback function.
	 *
	 * @param string|UI_DataGrid_Column $colNameOrInstance
	 * @param callable $callback
	 * @param mixed[] $args Any additional arguments for the callback.
	 * @return UI_DataGrid_Row_Sums
	 */
	public function makeCallback($colNameOrInstance, $callback, array $args = []): UI_DataGrid_Row_Sums
	{
		/* ... */
	}


	public function getEntry(): UI_DataGrid_Entry
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/DataGrid/Row/Sums/ColumnDef.php`

```php
namespace ;

use AppUtils\Interfaces\OptionableInterface as OptionableInterface;
use AppUtils\Traits\OptionableTrait as OptionableTrait;

abstract class UI_DataGrid_Row_Sums_ColumnDef implements OptionableInterface
{
	use OptionableTrait;

	public function getDataKey(): string
	{
		/* ... */
	}


	abstract public function resolveContent(): string;
}


```
###  Path: `/src/classes/UI/DataGrid/Row/Sums/ColumnDef.php`

```php
namespace ;

use AppUtils\Interfaces\OptionableInterface as OptionableInterface;
use AppUtils\Traits\OptionableTrait as OptionableTrait;

abstract class UI_DataGrid_Row_Sums_ColumnDef implements OptionableInterface
{
	use OptionableTrait;

	public function getDataKey(): string
	{
		/* ... */
	}


	abstract public function resolveContent(): string;
}


```
###  Path: `/src/classes/UI/DataGrid/Row/Sums/ColumnDef/Callback.php`

```php
namespace ;

use Application\Application as Application;

class UI_DataGrid_Row_Sums_ColumnDef_Callback extends UI_DataGrid_Row_Sums_ColumnDef
{
	public function getDefaultOptions(): array
	{
		/* ... */
	}


	public function resolveContent(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/DataGrid/Row/Sums/ColumnDef/Callback.php`

```php
namespace ;

use Application\Application as Application;

class UI_DataGrid_Row_Sums_ColumnDef_Callback extends UI_DataGrid_Row_Sums_ColumnDef
{
	public function getDefaultOptions(): array
	{
		/* ... */
	}


	public function resolveContent(): string
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 66.61 KB
- **Lines**: 3599
File: `modules/ui/datagrid/architecture.md`
