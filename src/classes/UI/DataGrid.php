<?php
/**
 * File containing the {@link UI_DataGrid} class.
 *
 * @package Application
 * @subpackage UserInterface
 * @see UI_DataGrid
 */

use Application\Driver\DriverException;
use Application\Interfaces\FilterCriteriaInterface;
use Application\Interfaces\HiddenVariablesInterface;
use Application\Traits\HiddenVariablesTrait;
use AppUtils\Interfaces\StringableInterface;
use function AppLocalize\tex;

/**
 * Handles displaying data in a tabular grid, with extended functionality
 * like applying custom actions to entries, allowing the user to reorder
 * list items, and more.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_DataGrid implements HiddenVariablesInterface
{
    use HiddenVariablesTrait;

    public const ERROR_MISSING_PRIMARY_KEY_NAME = 599901;
    public const ERROR_ALLSELECTED_FILTER_CRITERIA_MISSING = 599903;
    public const ERROR_ALLSELECTED_PRIMARY_KEYNAME_MISSING = 599904;
    public const ERROR_DUPLICATE_DATAGRID_ID = 599905;
    public const ERROR_UNKNOWN_OPTION = 5999007;

    protected string $id;
    protected UI $ui;
    protected bool $oddRow = false;
    protected bool $multiSelect = false;
    protected bool $multiSelectForced = false;
    protected string $primaryKeyName = '';

    /**
     * @var string[]
     */
    protected array $persistRequestVars = array(
        'page',
        'mode',
        'datagrid_perpage',
        'datagrid_page',
        'datagrid_orderby',
        'datagrid_orderdir'
    );

    protected Application_Request $request;

    /**
     * @var array<string,string|bool>
     */
    protected array $options = array(
        'table-class' => 'table',
        'disable-header' => false,
        'disable-footer' => false,
        'compact' => false,
        'hover' => true,
        'border' => true,
        'margins' => true,
        'multiselect-dropup' => false,
        'form-enabled' => true,
        'row-separator' => false,
        'fit-content' => false,
        'mini' => false,
    );

    protected string $emptyMessage;

    /**
     * @var string[]
     */
    protected static array $ids = array();

    protected bool $filterHint = false;
    protected ?UI_DataGrid_Row_Sums $sumsRow = null;
    protected bool $rendering = false;

    /**
     * @var UI_DataGrid_Entry[]
     */
    protected array $entries = array();
    private string $dispatcher = '';
    private string $footerCountText = '';

    /**
     * @param UI $ui
     * @param string|int $id
     * @param bool $allowDuplicateID
     * @throws Application_Exception
     */
    public function __construct(UI $ui, $id, bool $allowDuplicateID=false)
    {
        $id = strtolower(str_replace(array(' ', '.', '-'), '_', (string)$id));

    	if(!$allowDuplicateID && in_array($id, self::$ids, true)) {
    		throw new Application_Exception(
    			'Duplicate datagrid ID',
    			sprintf('A datagrid with the ID [%s] has already been added. Duplicates are not allowed (can be overridden).', $id),
    		    self::ERROR_DUPLICATE_DATAGRID_ID
    		);
    	}

    	self::$ids[] = $id;

        $this->ui = $ui;
        $this->id = $id;
        $this->request = Application_Request::getInstance();
        $this->emptyMessage = t('No entries found.');

        // Automatically add the screen's hidden variables
        // when the UI is enabled.
        if(Application::isUIEnabled() && Application_Driver::getInstance()->isUIFrameworkConfigured())
        {
            $this->addHiddenScreenVars();
        }
    }

    /**
     * @param string|number|UI_Renderable_Interface|NULL $message
     * @return $this
     */
    public function setEmptyMessage($message) : UI_DataGrid
    {
        $this->emptyMessage = toString($message);

        return $this;
    }

    /**
     * @return string
     */
    public function getID() : string
    {
        return $this->id;
    }

   /**
    * @return UI
    */
    public function getUI() : UI
    {
        return $this->ui;
    }

    // region: Handling columns

    /**
     * @var UI_DataGrid_Column[]
     */
    protected array $columns = array();

    /**
     * @var array<string,bool>
     */
    protected array $columnsEnabled = array();

    /**
     * @var int
     */
    protected int $columnCount = 0;

    /**
     * @var int
     */
    protected int $columnCounter = 0;

    protected bool $columnControls = false;

    protected int $maxColumnsShown = 0;

    protected ?UI_DataGrid_Column $orderColumn = null;

    /**
     * Retrieves the currently selected sorting column.
     *
     * @return UI_DataGrid_Column|NULL
     */
    public function getOrderColumn() : ?UI_DataGrid_Column
    {
        if(isset($this->orderColumn)) {
            return $this->orderColumn;
        }

        $found = false;

        // let's see if we can use a previously set column
        $columnName = $this->getSetting('datagrid_orderby');
        if(!empty($columnName)) {
            $column = $this->getColumnByOrderKey($columnName);
            if($column) {
                $found = $column;
            }
        }

        // otherwise, use the one that was set as default
        if(!$found && isset($this->defaultSortColumn)) {
            $found = $this->defaultSortColumn;
        }

        // or as a last recourse, use the first sortable column we find
        if(!$found) {
            foreach ($this->columns as $column) {
                if(!$column->isValid()) {
                    continue;
                }
                if($column->isSortable()) {
                    $found = $column;
                    break;
                }
            }
        }

        if(!$found) {
            return null;
        }

        // store the choice for later
        $this->setCookie('datagrid_orderby', $found->getOrderKey());

        $this->orderColumn = $found;

        return $found;
    }

    /**
     * Enables clientside controls to adjust the number of columns that get
     * displayed, and to navigate between them.
     *
     * @param int $maxColumns The maximum number of columns to display; Any above will be hidden.
     * @return $this
     */
    public function enableColumnControls(int $maxColumns=5) : self
    {
        $this->columnControls = true;
        $this->maxColumnsShown = $maxColumns;
        return $this;
    }

    /**
     * Retrieves a column by its data key name.
     * @param string $dataKeyName
     * @return UI_DataGrid_Column|NULL
     */
    public function getColumnByName(string $dataKeyName) : ?UI_DataGrid_Column
    {
        foreach ($this->columns as $column) {
            if($column->getDataKey() === $dataKeyName) {
                return $column;
            }
        }

        return null;
    }

    /**
     * Retrieves a column by its order key name.
     * @param string $orderKeyName
     * @return UI_DataGrid_Column|NULL
     */
    public function getColumnByOrderKey(string $orderKeyName) : ?UI_DataGrid_Column
    {
        foreach ($this->columns as $column) {
            if($column->getOrderKey() === $orderKeyName) {
                return $column;
            }
        }

        return null;
    }

    protected function addColumnObject(UI_DataGrid_Column $column) : void
    {
        $this->columns[] = $column;
        $this->columnCount++;
    }

    protected function prependColumnObject(UI_DataGrid_Column $column) : void
    {
        array_unshift($this->columns, $column);
        $this->columnCount++;
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
    public function makeEvenColumnWidths(bool $overwriteExisting = false) : self
    {
        $leftover = 100;
        $distributeColumns = $this->columnCount;
        if (!$overwriteExisting) {
            for ($i = 0; $i < $this->columnCount; $i++) {
                $column = $this->columns[$i];
                if (!$column->hasWidth() || !$column->isValid()) {
                    continue;
                }

                // this only works with percentage widths, so
                // if a column has a pixel width set, we reset
                // its width, so it can be set automatically.
                if ($column->getWidthType() !== 'percent') {
                    $column->resetWidth();
                    continue;
                }

                // remove the width from the available percentage
                $leftover -= $column->getWidth();
                $distributeColumns--;
            }
        } else {
            for ($i = 0; $i < $this->columnCount; $i++) {
                $column = $this->columns[$i];
                if ($column->hasWidth() && $column->isValid()) {
                    $column->resetWidth();
                }
            }
        }

        if ($leftover < 0) {
            $leftover = 100;
        }

        $width = (int)floor($leftover / $distributeColumns);

        for ($i = 0; $i < $this->columnCount; $i++) {
            $column = $this->columns[$i];
            if ($column->hasWidth() || !$column->isValid()) {
                continue;
            }

            $this->columns[$i]->setWidthPercent($width);
        }

        return $this;
    }

    /**
     * Moves the specified column to the desired position, starting at
     * 1 for the first column in the grid.
     *
     * @param UI_DataGrid_Column $column
     * @param int|string $position
     * @return boolean Whether the column was moved
     */
    public function moveColumn(UI_DataGrid_Column $column, $position) : bool
    {
        $position = (int)$position;

        $moved = false;
        $total = count($this->columns);
        $shuffled = array();
        for ($i = 1; $i <= $total; $i++) {
            if ($position === $i) {
                $shuffled[] = $column;
                $moved = true;
            }

            if ($this->columns[($i - 1)] === $column) {
                continue;
            }

            $shuffled[] = $this->columns[($i - 1)];
        }

        $this->columns = $shuffled;

        return $moved;
    }

    public function resetColumnWidths() : self
    {
        foreach ($this->columns as $column) {
            $column->resetWidth();
        }

        return $this;
    }

    /**
     * Retrieves the column by its column number (starting at 1).
     * @param integer $number
     * @return UI_DataGrid_Column|NULL
     */
    public function getColumn(int $number) : ?UI_DataGrid_Column
    {
        $idx = $number - 1;
        if (isset($this->columns[$idx])) {
            return $this->columns[$idx];
        }

        return null;
    }

    public function getLastColumn() : ?UI_DataGrid_Column
    {
        return $this->getColumn($this->countColumns());
    }

    /**
     * Counts all columns, excluding hidden columns.
     * @return int
     */
    public function countColumns() : int
    {
        $count = 0;
        foreach ($this->columns as $column) {
            if ($column->isValid() && !$column->isHidden()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param string $dataKey
     * @param string|number|UI_Renderable_Interface $title
     * @param array<string,mixed> $options
     * @return UI_DataGrid_Column
     */
    protected function createColumn(string $dataKey, $title, array $options = array()) : UI_DataGrid_Column
    {
    	$this->columnCounter++;

        return new UI_DataGrid_Column(
        	$this,
        	$this->columnCounter,
        	$dataKey,
        	$title,
        	$options
    	);
    }

    /**
     * @param string $dataKey
     * @param string|number|UI_Renderable_Interface $title
     * @param array<string,mixed> $options
     * @return UI_DataGrid_Column
     */
    public function addColumn(string $dataKey, $title, array $options = array()) : UI_DataGrid_Column
    {
        $column = $this->createColumn($dataKey, $title, $options);
        $this->addColumnObject($column);

        return $column;
    }

    public function setColumnEnabled(string $keyName, bool $enabled) : self
    {
        $this->columnsEnabled[$keyName] = $enabled;
        return $this;
    }

    /**
     * @param string $keyName
     * @return false|UI_DataGrid_Column
     */
    public function hasColumn(string $keyName)
    {
        for($i=0; $i < $this->columnCount; $i++) {
            if($this->columns[$i]->getDataKey() === $keyName) {
                return $this->columns[$i];
            }
        }

        return false;
    }

    // endregion

    /**
     * Adds a row with sums of values in columns.
     *
     * @return UI_DataGrid_Row_Sums
     */
    public function addSumsRow() : UI_DataGrid_Row_Sums
    {
        $this->sumsRow = new UI_DataGrid_Row_Sums($this);
        return $this->sumsRow;
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
    public function enableMultiSelect(string $primaryKeyName='', bool $forced=false) : UI_DataGrid
    {
        if(!$this->isFormEnabled())
        {
            return $this;
        }

        if (!empty($primaryKeyName)) {
            $this->setPrimaryName($primaryKeyName);
        }

        $this->multiSelect = true;
        $this->multiSelectForced = $forced;

        return $this;
    }

   /**
    * Makes the multiselect menu open towards the top of
    * the page instead of towards the bottom. Only used
    * if the multiselect is enabled.
    *
    * @return UI_DataGrid
    */
    public function setMultiSelectDropUp() : UI_DataGrid
    {
        return $this->setOption('multiselect-dropup', true);
    }

    public function setPrimaryName(string $keyName) : UI_DataGrid
    {
        $this->primaryKeyName = $keyName;

        return $this;
    }

    /**
     * Disables the multiselect functionality, even
     * if it was enabled prior to calling this.
     */
    public function disableMultiSelect() : UI_DataGrid
    {
        $this->multiSelect = false;

        return $this;
    }

    public function optionExists(string $name) : bool
    {
        return array_key_exists($name, $this->options);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     * @throws Exception
     */
    public function setOption(string $name, $value) : UI_DataGrid
    {
        if (!$this->optionExists($name)) {
            throw new UI_DataGrid_Exception(
                'Unknown data grid option.',
                sprintf('The option [%s] does not exist.', $name),
                self::ERROR_UNKNOWN_OPTION
            );
        }

        $this->options[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws UI_DataGrid_Exception
     */
    public function getOption(string $name)
    {
        if (!$this->optionExists($name)) {
            throw new UI_DataGrid_Exception(
                'Unknown data grid option.',
                sprintf('The option [%s] does not exist.', $name),
                self::ERROR_UNKNOWN_OPTION
            );
        }

        return $this->options[$name];
    }

    protected bool $initDone = false;

    protected function init() : void
    {
        if ($this->initDone) {
            return;
        }

        $actions = $this->getValidActions();

        // if there are no multiselect actions present,
        // no sense in showing the checkboxes.
        if($this->multiSelect === true && empty($actions) && !$this->multiSelectForced) {
            $this->multiSelect = false;
        }

        self::addClientSupport();

        $objName = $this->getClientObjectName();

        $this->ui->addJavascriptHeadStatement(
            sprintf("var %s = new UI_Datagrid", $objName),
        	$this->id,
            $objName
        );

        if ($this->multiSelect === true) {
            $column = new UI_DataGrid_Column_MultiSelect($this);
            $this->prependColumnObject($column);
        }

        $props = array();

        if($this->columnControls) {
        	$this->ui->addJavascriptHead(sprintf(
        		'%s.EnableColumnControls(%d)',
        		$objName,
        		$this->maxColumnsShown
        	));

        	$props['fullViewTitle'] = $this->fullViewTitle;
        }

        foreach($this->columns as $column) {
            if($column->isValid()) {
                $column->injectJavascript($this->ui, $objName);
            }
        }

        if($this->entriesSortable) {
            $this->requirePrimaryName();
            $this->ui->addJavascriptHead(sprintf(
                "%s.MakeSortable(%s)",
                $objName,
                $this->sortableHandler
            ));
        }

        if($this->entriesDroppable) {
            $this->ui->addJavascriptHead(sprintf(
                "%s.MakeDroppable(%s)",
                $objName,
                $this->droppableHandler
            ));
        }

        $props['BaseURL'] =  $this->buildURL(array('datagrid_page' => '_PGNR_'));
        $props['TotalEntries'] = $this->getTotal();
        $props['TotalEntriesUnfiltered'] = $this->getTotalUnfiltered();

        if(!empty($this->primaryKeyName)) {
            $this->ui->addJavascriptHeadStatement(
                sprintf(
                    '%s.SetPrimaryName',
                    $objName
                ),
                $this->primaryKeyName
            );
        }

        foreach($props as $name => $value) {
            $this->ui->addJavascriptHeadVariable(
                sprintf(
                    '%s.%s',
                    $objName,
                    $name
                ),
                $value
            );
        }

        $this->ui->addJavascriptOnload(sprintf(
        	'%s.Start()',
        	$objName
        ));
    }

    public function getClientObjectName() : string
    {
    	return 'grid'.str_replace('-', '_', $this->id);
    }

    public function getClientSubmitStatement(string $actionName) : string
    {
        return sprintf(
            "%s.Submit('%s')",
            $this->getClientObjectName(),
            $actionName
        );
    }

    public function getClientToggleSelectionStatement() : string
    {
        return sprintf(
            "%s.ToggleSelection()",
            $this->getClientObjectName()
        );
    }

   /**
    * @var UI_DataGrid_Action[]
    */
    protected array $actions = array();

    /**
     * Adds an action to the grid that can be run for the selected
     * elements (works only if multi select is enabled).
     * @param string $name
     * @param string|number|UI_Renderable_Interface|NULL $label
     * @return UI_DataGrid_Action_Default
     */
    public function addAction(string $name, $label) : UI_DataGrid_Action_Default
    {
        $action = new UI_DataGrid_Action_Default($this, $name, $label);
        $this->actions[] = $action;

        return $action;
    }

    /**
     * Adds a separator between multiselect actions.
     * @see addConfirmAction()
     * @see addAction()
     */
    public function addSeparatorAction() : void
    {
        $this->actions[] = new UI_DataGrid_Action_Separator($this);
    }

    /**
     * Adds an action to the grid that can be run for the selected
     * elements, but which will display a confirmation dialog before
     * starting the action. Only works if multi select is enabled.
     * @param string $name
     * @param string|number|UI_Renderable_Interface|NULL $label
     * @param string|number|UI_Renderable_Interface|NULL $confirmMessage
     * @return UI_DataGrid_Action_Confirm
     * @throws UI_Exception
     */
    public function addConfirmAction(string $name, $label, $confirmMessage) : UI_DataGrid_Action_Confirm
    {
        $action = new UI_DataGrid_Action_Confirm($this, $name, $label, $confirmMessage);
        $this->actions[] = $action;

        return $action;
    }

   /**
    * Checks whether any actions have been added to the grid.
    * @return boolean
    */
    public function hasActions() : bool
    {
        if(!$this->isFormEnabled())
        {
            return false;
        }

        return !empty($this->actions);
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
    public function addJSAction(string $name, string $label, string $function) : UI_DataGrid_Action_Javascript
    {
        $action = new UI_DataGrid_Action_Javascript($this, $name, $label, $function);
        $this->actions[] = $action;

        return $action;
    }

    public function disableRowSeparator() : UI_DataGrid
    {
        return $this->setOption('row-separator', true);
    }

    public function enableRowSeparator() : UI_DataGrid
    {
        return $this->setOption('row-separator', false);
    }

    public function disableBorder() : UI_DataGrid
    {
        return $this->setOption('border', false);
    }

    public function enableBorder() : UI_DataGrid
    {
        return $this->setOption('border', true);
    }

    public function disableMargins() : UI_DataGrid
    {
        return $this->setOption('margins', false);
    }

    public function enableMargins() : UI_DataGrid
    {
        return $this->setOption('margins', true);
    }

    /**
     * Makes the list more compact by reducing cell padding.
     * Alias for setting the "compact" option to true.
     */
    public function enableCompactMode() : UI_DataGrid
    {
        return $this->setOption('compact', true);
    }

    /**
     * Makes a mini table of the list by removing table borders and reducing padding/margin.
     * Alias for setting the "mini" option to true.
     */
    public function enableMiniMode() : UI_DataGrid
    {
        return $this->setOption('mini', true);
    }

    /**
     * Reduces the size of the columns to fit the content inside
     * Alias for setting the "fit-content" option to true.
     */
    public function enableFitContent() : UI_DataGrid
    {
        return $this->setOption('fit-content', true);
    }

   /**
    * If disabled, the datagrid will be rendered without an
    * enclosing form tag. In this case, actions and the like
    * which depend on the form will be disabled as well.
    *
    * @return UI_DataGrid
    */
    public function disableForm() : UI_DataGrid
    {
        return $this->setOption('form-enabled', false);
    }

    public function isFormEnabled() : bool
    {
        return $this->getOption('form-enabled');
    }

    public function disableCompactMode() : UI_DataGrid
    {
        return $this->setOption('compact', false);
    }

    public function enableHover() : UI_DataGrid
    {
        return $this->setOption('hover', true);
    }

    public function disableHover() : UI_DataGrid
    {
        return $this->setOption('hover', false);
    }

    protected bool $limitOptions = false;

    /**
     * @var int[]
     */
    protected array $limitChoices = array();

    protected int $limitCurrent = 0;

    public const DEFAULT_LIMIT_CHOICES = array(10, 20, 40, 60, 120);

    /**
     * Enables the multiple choice selector for choosing the
     * number of items to display per page. If no current
     * choice is set, the first item in the selector is used.
     * The choices have to be an indexed array of numeric values.
     *
     * @param int[] $choices
     * @return UI_DataGrid
     */
    public function enableLimitOptions(array $choices) : UI_DataGrid
    {
        if(!$this->isFormEnabled())
        {
            return $this;
        }

        $currentChoice = (int)$this->getSetting('datagrid_perpage');
        if (empty($currentChoice) || !in_array($currentChoice, $choices)) {
            $currentChoice = $choices[0];
        }

        $this->setCookie('datagrid_perpage', $currentChoice);

        $this->limitOptions = true;
        $this->limitChoices = $choices;
        $this->limitCurrent = $currentChoice;

        return $this;
    }

    public function enableLimitOptionsDefault() : UI_DataGrid
    {
        return $this->enableLimitOptions(self::DEFAULT_LIMIT_CHOICES);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    protected function setCookie(string $name, $value) : bool
    {
        if(headers_sent()) {
            return false;
        }

        $cookieName = $this->getCookieName($name);
        return setcookie($cookieName, (string)$value, time() + 60 * 60 * 24 * 360);
    }

    /**
     * @param string $name
     * @param null|string $default
     * @return string|NULL
     */
    protected function getCookie(string $name, ?string $default = null) : ?string
    {
        $cookieName = $this->getCookieName($name);

        if (isset($_COOKIE[$cookieName]))
        {
            return (string)$_COOKIE[$cookieName];
        }

        return $default;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getCookieName(string $name) : string
    {
        return $this->getID() . '_' . $name;
    }

    /**
     * Disables the multiple choice selector for choosing the
     * amount of items to show per page (only useful if it
     * has been enabled prior to calling this, as it is
     * disabled by default).
     */
    public function disableLimitOptions() : UI_DataGrid
    {
        $this->limitOptions = false;

        return $this;
    }

   /**
    * Disables the hint message "X items are hidden by filter settings"
    * that is displayed when no entries are found.
    *
    * @return UI_DataGrid
    */
    public function disableFilterHint() : UI_DataGrid
    {
        $this->filterHint = false;

        return $this;
    }

    public function enableFilterHint() : UI_DataGrid
    {
        $this->filterHint = true;

        return $this;
    }

    public function getOffset() : int
    {
        $page = $this->getPage() - 1;
        if ($page < 0) {
            $page = 0;
        }

        return $page * $this->limitCurrent;
    }

   /**
    * Retrieves the current items per page limit.
    * @return int
    */
    public function getLimit() : int
    {
        return $this->limitCurrent;
    }

   /**
    * Retrieves the name of the primary field. The value
    * has to be set in the data records, even if it is not
    * shown in a column.
    *
    * @return string
    */
    public function getPrimaryField() : string
    {
        return $this->primaryKeyName;
    }

   /**
    * Checks whether the primary field name has been set.
    * @return boolean
    */
    public function hasPrimaryField() : bool
    {
        return !empty($this->primaryKeyName);
    }

    /**
     * @var string[]
     */
    protected array $tableClasses = array();

   /**
    * Adds a class name that will be added to the data grid's main table HTML element.
    * @param string $class
    * @return $this
    */
    public function addTableClass(string $class) : self
    {
        if(!in_array($class, $this->tableClasses, true)) {
            $this->tableClasses[] = $class;
        }

        return $this;
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
    public function render(array $entries) : string
    {
        $this->init();

        $this->rendering = true;

        $this->executeCallbacks();

        $emptyDisplay = 'block';
        $tableDisplay = 'none';
        $dropperDisplay = 'block';
        if (!empty($entries)) {
            $this->entries = $this->filterAndSortEntries($entries);
            $emptyDisplay = 'none';
            $tableDisplay = 'table';
            $dropperDisplay = 'none';
        }

        if(empty($entries) && !$this->entriesDroppable) {
            $dropperDisplay = 'none';
        }

        $this->addTableClass($this->getOption('table-class'));

        if ($this->getOption('compact')) {
            $this->addTableClass('table-condensed');
        }

        if (!$this->isFooterEnabled()) {
            $this->addTableClass('table-footer-disabled');
        }

        if (!$this->isHeaderEnabled()) {
            $this->addTableClass('table-header-disabled');
        }

        if ($this->getOption('hover')) {
            $this->addTableClass('table-hover');
        }

        if($this->getOption('border')) {
            $this->addTableClass('table-bordered');
        }

        if(!$this->getOption('margins')) {
            $this->addTableClass('table-nomargins');
        }

        if ($this->getOption('row-separator')) {
            $this->addTableClass('table-remove-row-separator');
        }

        if ($this->getOption('fit-content')) {
            $this->addTableClass('table-fit-content');
        }

        if ($this->getOption('mini')) {
            $this->addTableClass('table-mini');
        }

        $id = $this->getID();

        $html =
        $this->renderTitle() .
        '<div class="datagrid" id="datagrid-' . $id . '-wrapper">' .
            $this->renderFilterMessages();

            if($this->isFormEnabled())
            {
                $this->ui->addJavascriptOnload(sprintf(
                    "$('#%s').submit(function(e) { %s.Handle_Submit(e); });",
                    $this->getFormID(),
                    $this->getClientObjectName()
                ));

                $html .=
                '<form id="' . $this->getFormID() . '" method="post" class="form-inline" action="' . APP_URL.'/'.$this->dispatcher . '">' .
                    $this->renderHiddenVars();
            }

            $html .=
            '<div id="' . $this->getFormID('empty') . '" style="display:' . $emptyDisplay . '">' .
                $this->renderEmptyMessage().
            '</div>' .
            '<table class="' . implode(' ', $this->tableClasses) . '" id="' . $this->getFormID('table') . '" style="display:' . $tableDisplay . '">' .
                $this->renderHeader() .
                $this->renderBody() .
                $this->renderFooter() .
            '</table>' .
            '<div id="' . $this->getFormID('dropper') . '" class="dropper" style="display:' . $dropperDisplay . '">'.
                t('Drop elements here').
            '</div>';

            if($this->isFormEnabled())
            {
                        $html .=
                    UI_Form::renderDummySubmit().
                '</form>';
            }

            $html .=
        '</div>';

        return $html;
    }

   /**
    * Renders a JS statement that can be used to submit the grid's form.
    * @return string
    */
    public function renderJSSubmitHandler(bool $simulate=false) : string
    {
        if(!$this->isFormEnabled())
        {
            return '';
        }

        return UI_Form::renderJSSubmitHandler($this, $simulate);
    }

    /**
     * Sorts list entries manually, but only if the currently selected
     * sorting column has a sorting callback. Otherwise, no sorting is
     * made at all.
     *
     * @param UI_DataGrid_Entry[] $entries
     * @return UI_DataGrid_Entry[]
     */
    protected function sortEntries(array $entries) : array
    {
        $sortColumn = $this->getOrderColumn();
        if($sortColumn && $sortColumn->hasSortingCallback()) {
            usort($entries, array($sortColumn, 'callback_sortEntries'));
        }

        return $entries;
    }

    protected function renderHiddenVars() : string
    {
        // actions can have parameters that get submitted with the
        // list, so they are passed on.
        foreach($this->actions as $action)
        {
            if(!$action instanceof UI_DataGrid_Action) {
                continue;
            }

            $params = $action->getParams();
            $actionName = $action->getName();
            foreach($params as $name => $value) {
                $this->addPrivateHiddenVar('action_'.$actionName.'['.$name.']', $value);
            }
        }

        $id = $this->getID();

        $this->addPrivateHiddenVar('datagrid_orderby', $this->getOrderBy(), $this->getFormID('orderby'));
        $this->addPrivateHiddenVar('datagrid_orderdir', $this->getOrderDir(), $this->getFormID('orderdir'));
        $this->addPrivateHiddenVar('datagrid_action', $this->getPage(), $this->getFormID('action'));
        $this->addPrivateHiddenVar('datagrid_submitted', $id);

        return $this->renderHiddenInputs(array('datagrid-hiddenvars'));
    }

    protected function renderFilterMessages() : string
    {
        if(!isset($this->filterCriteria) || !$this->filterCriteria->hasMessages()) {
            return '';
        }

        $messages = $this->filterCriteria->getMessages();
        $texts = array();
        foreach($messages as $msg) {
            $texts[] = $msg['message'];
        }

        return $this->ui->getPage()->renderWarningMessage(
            UI::icon()->warning() . ' ' .
            '<b>'.t('Please review your filter settings:').'</b> '.
            '<ul>'.
                '<li>'.implode('</li><li>', $texts).'</li>'.
            '</ul>'
        );
    }

    protected function renderEmptyMessage() : string
    {
        $message =
        UI::icon()->information() . ' ' .
        '<b>' . $this->emptyMessage . '</b>';

        $total = $this->getTotal();

        if(isset($this->totalUnfiltered) && $this->totalUnfiltered !== $total && $this->filterHint)
        {
            $diff = $this->totalUnfiltered - $total;

            if($diff===1) {
                $diffMessage = t('%1$s item is hidden by the current filter settings.', '<b>1</b>');
            } else {
                $diffMessage = t(
                    '%1$s items are hidden by the current filter settings.',
                    '<b>'.$diff.'</b>'
                );
            }

            $message .=
            '<br/><br/>' .
            t('Note:') . ' ' .$diffMessage;

            if(isset($this->filterSettings)) {
                $message .= ' ' .
                '<a href="javascript:void(0)" onclick="'.$this->filterSettings->getJSName().'.Reset()">' .
                    t('Show all') .
                '</a>';
            }
        }

        return $this->ui->getPage()->renderInfoMessage(
            $message,
            array(
                'dismissable' => false
            )
        );
    }

    protected ?FilterCriteriaInterface $filterCriteria = null;
    protected ?Application_FilterSettings $filterSettings = null;

    /**
     * Configures the data grid using the specified filter settings and filter criteria.
     *
     * @param Application_FilterSettings $settings
     * @param FilterCriteriaInterface $criteria
     * @return UI_DataGrid
     */
    public function configure(Application_FilterSettings $settings, FilterCriteriaInterface $criteria) : UI_DataGrid
    {
        $this->filterSettings = $settings;
        $settings->addHiddenVars($this->getHiddenVars());
        $this->addHiddenVars($settings->getHiddenVars());

        $settings->configureFilters($criteria);

        $this->configureFromFilters($criteria);

        return $this;
    }

   /**
    * Parses the specified set of entries and converts
    * all array data sets to entry objects. Entries that
    * are already entry objects are not modified.
    *
    * @param array<int,array<string,mixed>|UI_DataGrid_Entry> $entries
    * @return UI_DataGrid_Entry[]
    */
    public function filterAndSortEntries(array $entries) : array
    {
        $result = array();

        foreach ($entries as $entry) {
            if(!$entry instanceof UI_DataGrid_Entry) {
                $entry = $this->createEntry($entry);
            }

            $result[] = $entry;
        }

        return $this->sortEntries($result);
    }

   /**
    * Creates an entry object for the grid: these are used internally
    * to handle individual rows in the table.
    *
    * @param array $data Associative array with key => value pairs for columns in the row.
    * @return UI_DataGrid_Entry
    */
    public function createEntry(array $data) : UI_DataGrid_Entry
    {
        return new UI_DataGrid_Entry($this, $data);
    }

   /**
    * Creates a heading entry that can be used to create subtitles in a grid.
    *
    * @param string $title
    * @return UI_DataGrid_Entry_Heading
    */
    public function createHeadingEntry(string $title) : UI_DataGrid_Entry_Heading
    {
        return new UI_DataGrid_Entry_Heading($this, $title);
    }

   /**
    * Creates a merged entry that spans the whole columns.
    *
    * @param string $title
    * @return UI_DataGrid_Entry_Merged
    */
    public function createMergedEntry(string $title) : UI_DataGrid_Entry_Merged
    {
        return new UI_DataGrid_Entry_Merged($this, $title);
    }

    public function getFormID(string $part = '') : string
    {
        $id = 'datagrid-' . $this->getID();
        if (!empty($part)) {
            $id .= '-' . $part;
        }

        return $id;
    }

    public function getAction() : string
    {
        $name = $this->request->getParam('datagrid_action');
        if($this->isBatchComplete()) {
            $name = $this->request->getParam('datagrid_batch_complete');
        }

        if(!empty($name)) {
            return $name;
        }

        return '';
    }

    /**
     * @return string[]
     */
    public function getSelected() : array
    {
        $items = $this->request->getParam('datagrid_items');
        if (is_array($items)) {
            return $items;
        }

        return array();
    }

    public function isSubmitted() : bool
    {
        if(!$this->isFormEnabled())
        {
            return false;
        }

        if ($this->request->getParam('datagrid_submitted') === $this->getID()) {
            return true;
        }

        return false;
    }

    protected int $duplicateHeadersThreshold = 8;

    /**
     * @return $this
     * @deprecated
     * @see UI_DataGrid::disableFooter()
     */
    public function hideFooter() : self
    {
        return $this->disableFooter();
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function disableFooter() : UI_DataGrid
    {
        return $this->setOption('disable-footer', true);
    }

    public function enableFooter() : UI_DataGrid
    {
        return $this->setOption('disable-footer', false);
    }

    public function isFooterEnabled() : bool
    {
        return $this->getOption('disable-footer') === false;
    }

    protected function renderFooter() : string
    {
        if (!$this->isFooterEnabled()) {
            return '';
        }

        $html =
        '<tfoot>';
            // show the duplicate headers only if there are enough entries
            if($this->countEntries() >= $this->duplicateHeadersThreshold) {
                $html .=
                '<tr class="column-headers duplicate-headers">';
                    for ($i = 0; $i < $this->columnCount; $i++) {
                        if($this->columns[$i]->isValid()) {
                            $html .= $this->columns[$i]->renderHeaderCell(true);
                        }
                    }
                    $html .=
                '</tr>';
            }

            if(isset($this->sumsRow))
            {
                // Not using $this->renderRow(), because we do not want to
                // use the odd/even styling for the row containing the sums.
                $html .= $this->renderCells($this->sumsRow->getEntry(), false);
            }

            if($this->multiSelect && $this->countPages() > 1)
            {
                $toggleStatement = $this->getClientObjectName().'.ToggleSelectAll()';

                $html .=
                '<tr class="actions actions-selectall">' .
                    '<td>'.
                        '<input type="checkbox" name="datagrid_selectall" value="yes" onclick="'.$toggleStatement.'" class="selectall-checkbox"/>'.
                    '</td>'.
                    '<td colspan="' . ($this->countColumns() - 1) . '">' .
                        '<div class="selectall-active" style="display:none;">'.
                            t('All %1$s entries selected.', $this->total).' '.
                            UI::button(t('Deselect'))
                            ->makeMini()
                            ->setIcon(UI::icon()->cancel())
                            ->click($toggleStatement).
                        '</div>'.
                        '<div class="selectall-inactive">'.
                            '<a href="javascript:void(0)" onclick="'.$toggleStatement.'" class="selectall-link">'.
                                t('Select all %1$s entries', $this->formatAmount($this->total)).
                            '</a>'.
                        '</div>'.
                    '</td>' .
                '</tr>';
            }

        $html .=
            '<tr class="actions actions-navigator">' .
                '<td colspan="' . $this->countColumns() . '">' .
                    $this->renderFooter_limitOptions() .
                    $this->renderFooter_actions() .
                '</td>' .
            '</tr>';

        $html .=
            '</tfoot>';

        return $html;
    }

   /**
    * Ensures that the primary key name has been set.
    * Throws an exception otherwise.
    *
    * @throws Application_Exception
    */
    public function requirePrimaryName() : void
    {
        if($this->hasPrimaryField()) {
            return;
        }

        throw new Application_Exception(
            'Missing primary key name',
            'Setting the name of the primary key in the records is required. '.
            'This can be done with the [setPrimaryName] method.',
            self::ERROR_MISSING_PRIMARY_KEY_NAME
        );
    }

    public function getValidActions() : array
    {
        $result = array();

        foreach($this->actions as $action)
        {
            if($action->isValid())
            {
                $result[] = $action;
            }
        }

        return $result;
    }

    protected function renderFooter_actions() : string
    {
        $actions = $this->getValidActions();

        if (!$this->multiSelect || empty($actions)) {
            return '';
        }

        $this->requirePrimaryName();

        $dropup = '';
        if($this->getOption('multiselect-dropup') === true) {
            $dropup = " dropup";
        }

        $html =
        '<div class="btn-group pull-left'.$dropup.'">' .
            '<a class="btn btn-small dropdown-toggle" data-toggle="dropdown" href="#">' .
                UI::icon()->selected() . ' ' . t('With selected') . ' ' .
                '<span class="caret"></span>' .
            '</a>' .
            '<ul class="dropdown-menu">';
                foreach ($actions as $action)
                {
                    $html .= $action->render();
                    }
                $html .=
            '</ul>' .
        '</div>';

        return $html;
    }

    public function getTotal() : int
    {
        if($this->totalSet) {
            return $this->total;
        }

        return $this->countEntries();
    }

    public function getTotalUnfiltered() : ?int
    {
        return $this->totalUnfiltered;
    }

    /**
     * Counts the amount of items that have been added to the
     * grid. Note that this does not necessarily match the actual
     * amount of rows, since these can be excluded from the count.
     *
     * @return int
     * @see UI_DataGrid_Entry::isCountable()
     */
    public function countEntries() : int
    {
        $count = 0;

        foreach ($this->entries as $entry)
        {
            if($entry->isCountable()) {
                $count++;
            }
        }

        return $count;
    }

    protected function renderFooter_limitOptions() : string
    {
        $total = $this->getTotal();

        if (!$this->limitOptions)
        {
            if($total===1) {
                $label = t('1 entry.');
            } else {
                $label = t('%1$s entries total.', $total);
            }

            return
            '<div class="pull-right">' .
                '<span class="muted">' .
                    $label .
                '</span> '.
            '</div>';
        }

        $from = $this->getOffset();
        $to = $this->getOffset() + $this->countEntries();

        if($from===0) {
            $from = 1;
        }

        $html =
        '<div class="pull-right">' .
            '<span class="muted">' .
                $this->getFooterCountText(
                    $from,
                    $to,
                    $total
                ) . '<span class="noprint"> | </span>' .
            '</span> ' .
            '<label class="noprint" for="'.UI_Form::ID_PREFIX.'datagrid_perpage">' . t('Show per page:') . '</label> ' .
            '<select name="datagrid_perpage" id="'.UI_Form::ID_PREFIX.'datagrid_perpage" class="input-small noprint" onchange="'.$this->getClientObjectName().'.ChangePerPage()">';
                foreach ($this->limitChoices as $choice) {
                    $selected = null;
                    if ($choice === $this->limitCurrent) {
                        $selected = ' selected="selected"';
                    }

                    $html .=
                    '<option value="' . $choice . '"' . $selected . '>' . $choice . '</option>';
                }
                $html .=
            '</select> ';
            $pages = $this->countPages();
            if ($pages > 1) {
                $html .=
                '<div class="btn-group">' .
                    $this->renderPrevLink() .
                    $this->renderNextLink() .
                    $this->renderPageSelector() .
                '</div>';
            }
            $html .=
            '</div>';

        return $html;
    }

    protected int $amountAdjacents = 3;

    protected function renderPageSelector() : string
    {
        $currentPage = $this->getPage();
        $totalPages = $this->countPages();
        $maxToShow = ($this->amountAdjacents*2)+1;

        $this->ui->addJavascriptHeadVariable(sprintf(
            '%s.TotalPages',
            $this->getClientObjectName()),
            $totalPages
        );

        $html =
        '<a class="btn btn-small dropdown-toggle" data-toggle="dropdown" href="#">' .
            t('Page %1$s', $currentPage) . ' ' .
            '<span class="caret"></span>' .
        '</a>' .
        '<ul class="dropdown-menu">';

            if ($totalPages > $maxToShow) {
                if($currentPage > 1) {
                    $html .=
                    '<li>' .
                        '<a href="' . $this->buildURL(array('datagrid_page' => 1)) . '">'.
                            UI::icon()->first() . ' ' .
                            t('First page').
                        '</a>'.
                    '</li>'.
                    '<li class="divider"></li>';
                }

                $padLeft = $this->amountAdjacents;
        		$padRight = $this->amountAdjacents;

        		for($i=1; $i <= $this->amountAdjacents; $i++) {
        			$targetPage = $currentPage-$i;
        			if($targetPage <= 0) {
        				$padRight++;
        				$padLeft--;
        			}
        		}

        		for($i=1; $i <= $this->amountAdjacents; $i++) {
        		    $targetPage = $currentPage+$i;
        		    if($targetPage > $totalPages) {
        		        $padLeft++;
        		        $padRight--;
        		    }
        		}

        		$start = $currentPage-$padLeft;
        		$end = $currentPage+$padRight;

                for ($i = $start; $i <= $end; $i++) {
                    $active = '';
                    if ($i === $currentPage) {
                        $active = ' class="active"';
                    }

                    $url = $this->buildURL(array('datagrid_page' => $i));

                    $html .=
                    '<li' . $active . '>'.
                        '<a href="' . $url . '">' .
                            $i .
                        '</a>'.
                    '</li>';
                }

                if($currentPage < $totalPages) {
                    $html .=
                    '<li class="divider"></li>'.
                    '<li>'.
                        '<a href="' . $this->buildURL(array('datagrid_page' => $totalPages)) . '">'.
                            UI::icon()->last() . ' ' .
                            t('Last page'). ' ' .
                            '<span class="muted">' .
                                '('.$this->formatAmount($totalPages).')'.
                            '</span>'.
                        '</a>'.
                    '</li>';
                }

                $elID = 'datagrid-'.$this->id.'-custompage';

                $this->ui->addJavascriptOnload(sprintf(
                    "$('#%s').keydown(function(e) { return %s.CheckJumpToCustom(e); })",
                    $elID,
                    $this->getClientObjectName()
                ));

                $html .=
                '<li class="divider"></li>'.
                '<li class="dropdown-form" onclick="event.stopPropagation()">' .
                    '<input type="number" value="1" min="1" max="'.$totalPages.'" id="'.$elID.'" style="width:70px;"/> '.
                    UI::button()
                        ->setIcon(UI::icon()->ok())
                        ->setTooltipText(t('Jump to this page number'))
                        ->click(sprintf('%s.JumpToCustomPage()', $this->getClientObjectName())).
                '</li>';
            } else {
                for ($i = 1; $i <= $totalPages; $i++) {
                    $active = '';
                    if ($i === $currentPage) {
                        $active = ' class="active"';
                    }

                    $url = $this->buildURL(array('datagrid_page' => $i));

                    $html .=
                        '<li' . $active . '><a href="' . $url . '">' . $i . '</a></li>';
                }
            }
            $html .=
        '</ul>';

        return $html;
    }

    protected function renderPrevLink() : string
    {
        $prevPage = $this->getPage() - 1;
        $href = '#';
        $disabled = ' disabled';
        if ($prevPage > 0) {
            $disabled = '';
            $href = $this->buildURL(array('datagrid_page' => $prevPage));
        }

        return
        '<a class="btn btn-small' . $disabled . '" href="' . $href . '">' .
            UI::icon()->previous()->setTooltip(t('Back')) .
        '</a>';
    }

    protected function renderNextLink() : string
    {
        $nextPage = $this->getPage() + 1;
        $href = '#';
        $disabled = ' disabled';
        if ($nextPage <= $this->countPages()) {
            $disabled = '';
            $href = $this->buildURL(array('datagrid_page' => $nextPage));
        }

        return
        '<a class="btn btn-small' . $disabled . '" href="' . $href . '">' .
            UI::icon()->next()->setTooltip(t('Next')) .
        '</a>';
    }

    /**
     * Builds an internal URL used to persist data grid settings
     * via request parameters.
     *
     * @param array $params
     * @return string
     */
    protected function buildURL(array $params = array()) : string
    {
        foreach ($this->persistRequestVars as $name) {
            $value = $this->getSetting($name);
            if (!empty($value) && !isset($params[$name])) {
                $params[$name] = $value;
            }
        }

        foreach ($this->hiddenVars as $name => $value) {
            $params[$name] = $value;
        }

        return $this->request->buildURL($params, $this->dispatcher);
    }

    protected function getSetting(string $name) : ?string
    {
        $value = $this->getCookie($name);

        $requestValue = (string)$this->request->getParam($name);
        if ($requestValue !== '')
        {
            $value = $requestValue;
        }

        if (is_null($value))
        {
            $value = $this->getRequestDefault($name);
        }

        return $value;
    }

    /**
     * Retrieves default values for data grid settings that can
     * be set via the request.
     *
     * @param string $name
     * @return string|NULL
     */
    protected function getRequestDefault(string $name) : ?string
    {
        switch ($name)
        {
            case 'datagrid_perpage':
                return (string)$this->limitCurrent;
        }

        return null;
    }

    /**
     * @return int
     */
    public function countPages() : int
    {
        if ($this->limitCurrent < 1) {
            return 0;
        }

        return (int)ceil($this->getTotal() / $this->limitCurrent);
    }

    public function getPage() : int
    {
        $page = $this->request->getParam('datagrid_page', 1);
        $totalPages = $this->countPages();
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        if ($page < 1) {
            $page = 1;
        }

        return $page;
    }

    protected int $total = 0;

    protected bool $totalSet = false;

    public function setTotal(int $total) : UI_DataGrid
    {
        $this->total = $total;
        $this->totalSet = true;
        return $this;
    }

    protected ?int $totalUnfiltered = null;

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
    public function setTotalUnfiltered(int $total) : UI_DataGrid
    {
        $this->totalUnfiltered = $total;
        return $this;
    }

    public function configureFromFilters(FilterCriteriaInterface $criteria) : UI_DataGrid
    {
        $this->filterCriteria = $criteria;

        $this->setTotal($criteria->countItems());
        $this->setTotalUnfiltered($criteria->countUnfiltered());

        $criteria->configure($this);

        if($this->isAllSelected()) {
            $this->processAllSelected($this->getActiveAction());
        }

        return $this;
    }

    /**
     * @deprecated
     * @return $this
     * @throws Exception
     * @see UI_DataGrid::disableHeader()
     */
    public function hideHeader() : UI_DataGrid
    {
        return $this->setOption('hide-header', true);
    }

    public function disableHeader() : UI_DataGrid
    {
        return $this->setOption('disable-header', true);
    }

    public function enableHeader() : UI_DataGrid
    {
        return $this->setOption('disable-header', false);
    }

    public function isHeaderEnabled() : bool
    {
        return $this->getOption('disable-header') === false;
    }

    protected function renderHeader() : string
    {
        if (!$this->isHeaderEnabled()) {
            return '';
        }

        $html =
        '<thead>' .
            '<tr class="column-headers">';
                for ($i = 0; $i < $this->columnCount; $i++) {
                    if($this->columns[$i]->isValid()) {
                        $html .= $this->columns[$i]->renderHeaderCell();
                    }
                }
                $html .=
            '</tr>' .
        '</thead>';

        return $html;
    }

    protected function renderBody() : string
    {
        $html =
        '<tbody>';
            foreach ($this->entries as $entry) {
                $html .= $this->renderRow($entry);
            }
        $html .=
        '</tbody>';

        return $html;
    }

    protected function renderRow(UI_DataGrid_Entry $entry) : string
    {
        if ($this->oddRow) {
            $entry->addClass('odd');
            $this->oddRow = false;
        } else {
            $entry->addClass('even');
            $this->oddRow = true;
        }

        if ($this->entriesSortable) {
            $entry->addClass('row-sortable');
        }

        return $entry->render();
    }

    public function renderCells(UI_DataGrid_Entry $cell, bool $register=true) : string
    {
        $clientData = $cell->getData();
        $html = '';
        for ($i = 0; $i < $this->columnCount; $i++) {
            $column = $this->columns[$i];

            // remove action column data from the client data set.
            if($column->isAction()) {
                unset($clientData[$column->getDataKey()]);
            }

            if($column->isValid()) {
                $html .= $column->renderCell($cell);
            }
        }

        if($register) {
            $this->ui->addJavascriptHeadStatement(
                sprintf('%s.RegisterEntry', $this->getClientObjectName()),
                $clientData
            );
        }

        return $html;
    }

    protected bool $callbacksExecuted = false;

    /**
     * Executes the action callbacks if the data grid has been
     * submitted, an action has been selected, and any action
     * callbacks have been defined. Use this to automate the
     * handling of actions.
     */
    public function executeCallbacks() : UI_DataGrid
    {
        if(!$this->isFormEnabled())
        {
            return $this;
        }

        if($this->callbacksExecuted) {
            return $this;
        }

        $action = $this->getActiveAction();
        if(!$action)
        {
            return $this;
        }

        if($this->isAllSelected() && $action->isSelectAllEnabled())
        {
            if(!$this->rendering) {
                return $this;
            }

            if(!isset($this->filterCriteria)) {
                throw new Application_Exception(
                    'No filter criteria set',
                    sprintf(
                        'To be able to execute callbacks on all available entries for the [%s] data grid, the filter criteria must be set.',
                        $this->getID()
                    ),
                    self::ERROR_ALLSELECTED_FILTER_CRITERIA_MISSING
                );
            }

            if(empty($this->primaryKeyName)) {
                throw new Application_Exception(
                    'No primary key set',
                    sprintf(
                        'To be able to execute callbacks on all available entries for the [%s] data grid, the primary key name must be set.',
                        $this->getID()
                    ),
                    self::ERROR_ALLSELECTED_PRIMARY_KEYNAME_MISSING
                );
            }

            $this->processAllSelected($action);
        }

        $action->executeCallback();

        $this->callbacksExecuted = true;

        return $this;
    }

   /**
    * Checks whether the grid is currently in batch processing mode.
    * This is different from the list being in AJAX mode:
    * The list can be in batch processing mode but not in AJAX mode.
    *
    * @return boolean
    */
    public function isBatchProcessing() : bool
    {
        if(!$this->isFormEnabled())
        {
            return false;
        }

        return $this->request->getBool('datagrid_batch_processing');
    }

    public function isBatchComplete() : bool
    {
        return
            $this->isBatchProcessing()
            &&
            $this->request->hasParam('datagrid_batch_complete');
    }

   /**
    * Retrieves the currently selected action, if any.
    * @return UI_DataGrid_Action|NULL
    */
    public function getActiveAction() : ?UI_DataGrid_Action
    {
        if(!$this->isSubmitted() && !$this->isBatchProcessing()) {
            return null;
        }

        $total = count($this->actions);

        if($total === 0) {
            return null;
        }

        $actionName = $this->getAction();
        foreach ($this->actions as $action) {
            if($action=='__separator') {
                continue;
            }
            if ($action->getName() === $actionName) {
                return $action;
            }
        }

        return null;
    }

    public function isAllSelected() : bool
    {
        if(!$this->isFormEnabled())
        {
            return false;
        }

        return $this->request->getBool('datagrid_selectall');
    }

    protected int $selectAllBatchSize = 60;

    /**
     * @param UI_DataGrid_Action $action
     * @return never
     *
     * @throws Application_Exception
     * @throws DriverException
     * @throws UI_Exception
     * @throws UI_Themes_Exception
     */
    protected function processAllSelected(UI_DataGrid_Action $action)
    {
        if(!isset($this->filterCriteria)) {
            throw new UI_Exception(
                'No filter criteria instance available.',
                '',
                self::ERROR_ALLSELECTED_FILTER_CRITERIA_MISSING
            );
        }

        $primary = $this->getPrimaryField();
        $this->filterCriteria->setLimit(0, 0);
        $entries = $this->filterCriteria->getItems();

        $ids = array();
        foreach($entries as $entry) {
            $ids[] = $entry[$primary];
        }

        $page = $this->ui->getPage();
        $varName = 'gproc'.nextJSID();
        $total = $this->getTotal();

        $ajaxParams = $this->request->getRefreshParams(
            array(
                'datagrid_ajax' => 'yes'
            ),
            array(
                'datagrid_selectall',
                'datagrid_items'
            )
        );

        // adjust the batch size somewhat to keep smaller amounts realistic
        $batchSize = ceil($total / 10);
        if($batchSize < 1) {
            $batchSize = 1;
        } else if($batchSize > $this->selectAllBatchSize) {
            $batchSize = $this->selectAllBatchSize;
        }

        $this->ui->addProgressBar();

        self::addClientSupport();
        $this->ui->addJavascript('ui/datagrid/batch-processor.js');

        $this->ui->addJavascriptHead(sprintf('var %s = new UI_DataGrid_BatchProcessor()', $varName));
        $this->ui->addJavascriptHeadStatement(sprintf('%s.SetIDs', $varName), $ids);
        $this->ui->addJavascriptHeadStatement(sprintf('%s.SetParams', $varName), $ajaxParams);
        $this->ui->addJavascriptHeadStatement(sprintf('%s.SetBatchSize', $varName), $batchSize);
        $this->ui->addJavascriptOnload(sprintf('%s.Start()', $varName));

        $content = $page->renderTemplate(
            'content.datagrid.process-batches',
            array(
                'grid' => $this,
                'total' => $total,
                'batch-size' => $batchSize
            )
        );

        $page->setContent(
            $page->getRenderer()
                ->setTitle(t('Processing selected entries'))
                ->setContent($content)
                ->render()
        );

        echo $page->render();

        Application::exit();
    }

   /**
    * Whether the grid is currently in AJAX mode.
    * @return boolean
    */
    public function isAjax() : bool
    {
        return $this->isSubmitted() && $this->request->getBool('datagrid_ajax');
    }

    protected ?string $title = null;

    /**
     * Sets the optional title for the grid.
     *
     * @param string|number|UI_Renderable_Interface|NULL $title
     * @throws UI_Exception
     */
    public function setTitle($title) : self
    {
        $this->title = toString($title);
        return $this;
    }

    /**
     * Renders the HTML code for the data grid's title, which is displayed
     * above the grid and within the container.
     *
     * @return string
     */
    protected function renderTitle() : string
    {
        if (!isset($this->title)) {
            return '';
        }

        $this->ui->addJavascriptHeadStatement(
            $this->getClientObjectName().'.SetTitle',
            $this->title
        );

        return
        '<div class="datagrid-title" id="datagrid-' . $this->getID() . '-title">' .
            $this->title .
        '</div>';
    }

    protected string $fullViewTitle = '';

   /**
    * Sets the title of the table when it is shown in the full view mode
    * (which is only available when the column controls are enabled).
    *
    * @param string|number|StringableInterface|NULL $title
    * @return $this
    */
    public function setFullViewTitle($title) : self
    {
        $this->fullViewTitle = toString($title);
        return $this;
    }

    protected bool $entriesSortable = false;

    protected string $sortableHandler = '';

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
    public function makeEntriesSortable($clientsideHandler, ?string $primaryKeyName=null) : self
    {
        if (!empty($primaryKeyName)) {
            $this->setPrimaryName($primaryKeyName);
        }

        if($this->entriesSortable) {
            return $this;
        }

        $this->ui->addJavascript('ui/datagrid/sortable.js');
        $this->ui->addJavascript('ui/datagrid/sortable/configuration.js');

        $this->entriesSortable = true;
        $this->sortableHandler = toString($clientsideHandler);

        return $this;
    }

    protected bool $entriesDroppable = false;
    protected string $droppableHandler = '';

   /**
    * Sets that elements may be dragged into the list to add new
    * entries.
    *
    * @param string|StringableInterface $clientsideHandler The name of a clientside variable holding the droppable events handler object
    * @return $this
    */
    public function makeEntriesDroppable($clientsideHandler, $primaryKeyName=null) : self
    {
        if (!empty($primaryKeyName)) {
            $this->setPrimaryName($primaryKeyName);
        }

        if($this->entriesDroppable) {
            return $this;
        }

        $this->ui->addJavascript('ui/datagrid/droppable.js');
        $this->ui->addJavascript('ui/datagrid/droppable/configuration.js');

        $this->entriesDroppable = true;
        $this->droppableHandler = toString($clientsideHandler);

        return $this;
    }

   /**
    * Checks whether the sortable entries feature is enabled.
    * @return boolean
    */
    public function isEntriesSortable() : bool
    {
        return $this->entriesSortable;
    }

   /**
    * @return string|NULL
    */
    public function getOrderBy() : ?string
    {
        $col = $this->getOrderColumn();
        if($col) {
            return $col->getOrderKey();
        }

        return null;
    }

    protected string $defaultOrderDir = 'asc';

   /**
    * Retrieves the selected direction in which to sort the grid.
    *
    * @return string asc|desc
    */
    public function getOrderDir()
    {
        $found = false;

        $dir = $this->getSetting('datagrid_orderdir');
        if(!empty($dir)) {
            $found = $dir;
        }

        if(!$found) {
            $found = $this->defaultOrderDir;
        }

        $this->setCookie('datagrid_orderdir', $found);

        return $found;
    }

    /**
     * @param string $dir
     * @return $this
     */
    public function setDefaultOrderDir(string $dir) : self
    {
        $this->defaultOrderDir = $dir;
        return $this;
    }

    protected ?UI_DataGrid_Column $defaultSortColumn = null;

   /**
    * Sets the column to use as default sorting column.
    * @param UI_DataGrid_Column $column
    * @return $this
    */
    public function setDefaultSortColumn(UI_DataGrid_Column $column, string $dir='ASC') : self
    {
        $this->defaultSortColumn = $column;
        $this->setDefaultOrderDir($dir);
        return $this;
    }

   /**
    * Adds the java scripts and stylesheets required to use the
    * data grid support clientside to build grids with the API.
    */
    public static function addClientSupport() : void
    {
        $ui = UI::getInstance();

        $ui->addStylesheet('ui-datagrid.css');
        $ui->addJavascript('ui/datagrid.js');
        $ui->addJavascript('ui/datagrid/column.js');
        $ui->addJavascript('ui/datagrid/entry.js');
    }

    /**
     * Configures the data grid for the administration screen,
     * by setting all required hidden variables to stay on the
     * current page when using the pager.
     *
     * @param Application_Admin_ScreenInterface $screen
     * @return $this
     * @throws Application_Exception
     */
    public function configureForScreen(Application_Admin_ScreenInterface $screen) : self
    {
        $page = Application_Admin_ScreenInterface::REQUEST_PARAM_PAGE;
        $mode = Application_Admin_ScreenInterface::REQUEST_PARAM_MODE;
        $submode = Application_Admin_ScreenInterface::REQUEST_PARAM_SUBMODE;
        $action = Application_Admin_ScreenInterface::REQUEST_PARAM_ACTION;

        if($screen instanceof Application_Admin_Area)
        {
            $this->addHiddenVar($page, $screen->getURLName());
        }
        else if($screen instanceof Application_Admin_Area_Mode)
        {
            $this->addHiddenVar($page, $screen->getArea()->getURLName());
            $this->addHiddenVar($mode, $screen->getURLName());
        }
        else if($screen instanceof Application_Admin_Area_Mode_Submode)
        {
            $this->addHiddenVar($page, $screen->getArea()->getURLName());
            $this->addHiddenVar($mode, $screen->getMode()->getURLName());
            $this->addHiddenVar($submode, $screen->getURLName());
        }
        else if($screen instanceof Application_Admin_Area_Mode_Submode_Action)
        {
            $this->addHiddenVar($page, $screen->getArea()->getURLName());
            $this->addHiddenVar($mode, $screen->getMode()->getURLName());
            $this->addHiddenVar($submode, $screen->getSubmode()->getURLName());
            $this->addHiddenVar($action, $screen->getURLName());
        }

        return $this;
    }

    /**
     * @param string $footerText
     */
    public function setFooterCountText(string $footerText): void
    {
        $this->footerCountText = $footerText;
    }

    /**
     * @param int $from
     * @param int $to
     * @param int $total
     * @return string
     */
    public function getFooterCountText(int $from, int $to, int $total): string
    {
        $text = $this->footerCountText;

        if(empty($text)) {
            $text = tex(
                'Showing entries %1$s to %2$s, %3$s total.',
                'Placeholders are entry counts: From / To / Total.',
                '[FROM]',
                '[TO]',
                '[TOTAL]'
            );
        }

        return str_replace(
            array('[FROM]', '[TO]', '[TOTAL]'),
            array($this->formatAmount($from), $this->formatAmount($to), $this->formatAmount($total)),
            $text
        );
    }

    private function formatAmount(int $amount) : string
    {
        return number_format($amount, 0, '.', ' ');
    }

    /**
     * Sets the `action` parameter of the data grid's form.
     *
     * @param string $dispatcher
     * @return $this
     */
    public function setDispatcher(string $dispatcher) : UI_DataGrid
    {
        $this->dispatcher = $dispatcher;
        return $this;
    }
}
