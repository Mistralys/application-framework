<?php

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

    /**
    * @var UI_DataGrid
    */
    protected $grid;

    /**
     * @var string
     */
    protected $dataKey;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var int
     */
    protected $number;

   /**
    * @var UI
    */
    protected $ui;

    /**
     * @var UI_DataGrid_Column_UserSettings|NULL
     */
    protected $userSettings;

    /**
     * @var array{sortable:bool,sortKey:string|null,sortCallback:callable|null,sortDataColumn:string|NULL,align:string,nowrap:bool,hidden:bool,width:null|int,width-type:string,tooltip:string}
     */
    protected $options = array(
        self::OPTION_ALIGN => 'left',
        self::OPTION_SORTABLE => false,
        self::OPTION_SORT_KEY => null,
        self::OPTION_SORT_CALLBACK => null,
        self::OPTION_SORT_DATA_COLUMN => null,
        self::OPTION_NOWRAP => false,
        self::OPTION_HIDDEN => false,
        self::OPTION_WIDTH => null,
        self::OPTION_WIDTH_TYPE => 'percent',
        self::OPTION_TOOLTIP => ''
    );

    /**
     * @param UI_DataGrid $grid
     * @param int $number
     * @param string $dataKey
     * @param string|number|UI_Renderable_Interface $title
     * @param array<string,mixed> $options
     * @throws Exception
     */
    public function __construct(UI_DataGrid $grid, int $number, string $dataKey, $title, array $options = array())
    {
        $this->grid = $grid;
        $this->dataKey = $dataKey;
        $this->title = toString($title);
        $this->number = $number;
        $this->ui = $grid->getUI();

        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }
    }

    /**
     * @return UI_DataGrid
     */
    public function getDataGrid() : UI_DataGrid
    {
        return $this->grid;
    }

    public function getType() : string
    {
        return 'Regular';
    }

    public function getDataKey() : string
    {
        return $this->dataKey;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * Forces contents of the column's cells not to break to a new line.
     * @return $this
     */
    public function setNowrap() : UI_DataGrid_Column
    {
        return $this->setOption(self::OPTION_NOWRAP, true);
    }

    /**
     * Aligns the contents of the column's cells to the center.
     * @return $this
     */
    public function alignCenter() : UI_DataGrid_Column
    {
        return $this->setOption(self::OPTION_ALIGN, 'center');
    }

    /**
     * @var string[]
     */
    protected $classes = array();
    
   /**
    * Adds a class that will be added to all cells in this column.
    * 
    * @param string $class
    * @return $this
    */
    public function addClass(string $class) : UI_DataGrid_Column
    {
        if(!in_array($class, $this->classes)) {
            $this->classes[] = $class;
        }
        
        return $this;
    }

    /**
     * Aligns the contents of the column's cells to the right.
     * @return $this
     */
    public function alignRight() : UI_DataGrid_Column
    {
        return $this->setOption(self::OPTION_ALIGN, 'right');
    }
    
   /**
    * Sets the tooltip text for the column: this will add the tooltip
    * to the column header.
    * 
    * @param string|number|UI_Renderable_Interface $text
    * @return $this
    */
    public function setTooltip($text) : UI_DataGrid_Column
    {
        return $this->setOption(self::OPTION_TOOLTIP, toString($text));
    }

    /**
     * Makes the column as compact as possible. To avoid line
     * breaks in texts, combine this with {@setNowrap()}.
     * @return $this
     */
    public function setCompact() : UI_DataGrid_Column
    {
        $this->setNowrap();
        return $this->setWidthPercent(1);
    }

    /**
     * Sets the column width as a percentage value.
     * @param int $width
     * @return $this
     */
    public function setWidthPercent(int $width) : UI_DataGrid_Column
    {
        $this->setOption(self::OPTION_WIDTH, $width);
        $this->setOption(self::OPTION_WIDTH_TYPE, 'percent');

        return $this;
    }

    /**
     * Sets the column width as a fixed pixel value.
     * @param int $width
     * @return $this
     */
    public function setWidthPixels(int $width) : UI_DataGrid_Column
    {
        $this->setOption(self::OPTION_WIDTH, $width);
        $this->setOption(self::OPTION_WIDTH_TYPE, 'pixels');
        $this->addClass('force-ellipsis');

        return $this;
    }

    /**
     * Checks whether a width has been set for the column
     * @return boolean
     */
    public function hasWidth() : bool
    {
        return $this->getWidth() > 0;
    }

    /**
     * Retrieves the type of width set for the column.
     * This can be either "percent" or "pixels"
     *
     * @return string
     */
    public function getWidthType() : string
    {
        return (string)$this->options[self::OPTION_WIDTH_TYPE];
    }

    public function getWidth() : int
    {
        return (int)$this->options[self::OPTION_WIDTH];
    }

    public function resetWidth() : UI_DataGrid_Column
    {
        return $this->setWidthPercent(0);
    }

    /**
     * Sets this column as sortable, which will allow the user to
     * sort by the contents of the column using clientside controls.
     *
     * @param string|NULL $dataKeyName The name of the data key to sort: use this if it is not the same as the column name.
     * @return UI_DataGrid_Column
     */
    public function setSortable(bool $default=false, string $dataKeyName=null) : UI_DataGrid_Column
    {
        if(empty($dataKeyName)) {
            $dataKeyName = $this->getDataKey();
        }
        
        $this->options[self::OPTION_SORTABLE] = true;
        $this->options[self::OPTION_SORT_KEY] = $dataKeyName;

        if($default) {
            $this->grid->setDefaultSortColumn($this);
        }
        
        return $this;
    }
    
   /**
    * Sets a callback function to use to sort this column.
    * Enables sorting the column.
    * 
    * @param callable $callback
    * @param string|NULL $dataColumn The name of the column in the data set to use as value for the sorting. Defaults to this column, but a different one can be specified as needed.
    * @return $this
    */
    public function setSortingCallback(callable $callback, ?string $dataColumn=null): UI_DataGrid_Column
    {
        $this->setSortable();
        
        $this->options[self::OPTION_SORT_CALLBACK] = $callback;
        $this->options[self::OPTION_SORT_DATA_COLUMN] = $dataColumn;
        
        return $this;
    }
    
    public function setSortingNumeric(?string $dataColumn=null) : UI_DataGrid_Column
    {
        return $this->setSortingCallback(
            function($a, $b) 
            {
                return $b-$a;
            },
            $dataColumn
        );
    }
    
    public function setSortingString(?string $dataColumn=null) : UI_DataGrid_Column
    {
        return $this->setSortingCallback(
            function($a, $b)
            {
                return strnatcasecmp($a, $b);
            },
            $dataColumn
        );
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
        $key = $this->getDataKey();
        if(isset($this->options[self::OPTION_SORT_DATA_COLUMN])) {
            $key = $this->options[self::OPTION_SORT_DATA_COLUMN];
        }
        
        $dataA = $entryA->getData();
        $dataB = $entryB->getData();
         
        if(!array_key_exists($key, $dataA)) {
            throw new Application_Exception(
                'Sort column data missing.',
                sprintf(
                    'The grid [%s] column [%s] is set to be sorted manually by the data key [%s], but that does not exist in the specified entries collection.',
                    $this->grid->getID(),
                    $this->getDataKey(),
                    $key
                ),
                self::ERROR_SORT_DATA_COLUMN_MISSING
            );
        }
        
        $result = call_user_func($this->options[self::OPTION_SORT_CALLBACK], $dataA[$key], $dataB[$key]);
        
        if($this->grid->getOrderDir() === 'desc') {
            $result = $result * -1;
        }
        
        return $result;
    }

    /**
     * Sets the column as hidden: use this to hide ID columns for example.
     *
     * @return $this
     */
    public function setHidden() : UI_DataGrid_Column
    {
        $this->options[self::OPTION_HIDDEN] = true;

        return $this;
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
    public function setOption(string $name, $value) : UI_DataGrid_Column
    {
        $this->requireValidOption($name);

        $this->options[$name] = $value;

        return $this;
    }

    /**
     * Checks whether this column is hidden.
     * @return boolean
     */
    public function isHidden() : bool
    {
        return $this->isHiddenForUser() || $this->options[self::OPTION_HIDDEN];
    }

    public function renderCell(UI_DataGrid_Entry $entry) : string
    {
        if ($this->isHidden()) {
            return '';
        }
        
        $value = $entry->getValueForColumn($this);
        
        if($this->editable) {
            $value = 
            '<div id="'.$entry->getPrimaryValue().'_display">' .
                $value . 
            '</div>';
        }
        
        return '<td' . $this->renderAttributes(false, $value, $entry) . '>' . $value . '</td>';
    }

    public function renderHeaderCell(bool $duplicate=false) : string
    {
        if ($this->isHidden()) {
            return '';
        }
        
        $title = sb();
        
        $icons = array();
        
        if(!$duplicate && $this->isSortable())
        {
            $this->addSortIcons($title);
        }

        $title->add($this->title);
        
        if(!empty($this->options[self::OPTION_TOOLTIP])) {
            $icons[] = UI::icon()->information()
                ->makeInformation()
                ->addClass('help')
                ->setTooltip($this->options[self::OPTION_TOOLTIP])
                ->render();
        }
        
        if($this->isEditable()) {
            $icons[] = UI::icon()->edit()
                ->makeInformation()
                ->addClass('help')
                ->setTooltip(t('Editable:') . ' ' . t('Click on a cell in this column to edit its contents.'))
                ->render();
        }
        
        if(!empty($icons)) {
            $title = $title . 
            '<div class="pull-right cell-icons">'.
                implode(' ', $icons).
            '</div>';
        }
        
        return '<th' . $this->renderAttributes(true) . '>' . $title . '</th>';
    }

    private function addSortIcons(UI_StringBuilder $text) : void
    {
        $clientName = $this->grid->getClientObjectName();

        $text
            ->add('<span class="sort-controls">')
            ->icon(
                UI::icon()->sortAsc()
                    ->addClass('icon-sort')
                    ->addClass('icon-sort-asc')
                    ->setTooltip(t('Sort by %1$s ascending', $this->title))
                    ->makeClickable(sprintf(
                        "%s.SetOrderBy(%s, %s)",
                        $clientName,
                        AppUtils\ConvertHelper::string2attributeJS($this->getOrderKey()),
                        AppUtils\ConvertHelper::string2attributeJS('asc')
                    ))
            )
            ->icon(
                UI::icon()->sortDesc()
                    ->addClass('icon-sort')
                    ->addClass('icon-sort-desc')
                    ->setTooltip(t('Sort by %1$s descending', $this->title))
                    ->makeClickable(sprintf(
                        "%s.SetOrderBy(%s, %s)",
                        $clientName,
                        AppUtils\ConvertHelper::string2attributeJS($this->getOrderKey()),
                        AppUtils\ConvertHelper::string2attributeJS('desc')
                    ))
            )
            ->add('</span>');
    }

   /**
    * Checks whether this column is the one the list is currently 
    * being ordered by.
    * 
    * @return boolean
    */
    public function isSorted() : bool
    {
        $orderBy = $this->grid->getOrderBy();

        return !empty($orderBy) && $orderBy === $this->getOrderKey();
    }
    
    protected function renderAttributes($isHeader, $value=null, UI_DataGrid_Entry $entry=null)
    {
        $objectName = $this->getObjectName();
        
        $classes = $this->classes;
        $classes[] = 'align-' . $this->options[self::OPTION_ALIGN];
        $classes[] = 'role-' . $this->role;
        $classes[] = 'column-' . $this->number;

        if($this->isSorted()) {
            $classes[] = 'sorted';
            $classes[] = 'sorted-'.strtolower($this->grid->getOrderDir());
        }
        
        $styles = array();
        $attributes = array();

        if ($this->options[self::OPTION_NOWRAP]) {
            $classes[] = self::OPTION_NOWRAP;
        }

        if (!empty($this->options[self::OPTION_WIDTH])) {
            switch ($this->options[self::OPTION_WIDTH_TYPE]) {
                case 'percent':
                    $styles[self::OPTION_WIDTH] = $this->options[self::OPTION_WIDTH] . '%';
                    break;

                case 'pixels':
                    $styles[self::OPTION_WIDTH] = $this->options[self::OPTION_WIDTH] . 'px';
                    break;
            }
        }
        
        if($this->editable && !$isHeader) {
            $classes[] = 'editable';
            $attributes['onclick'] = $objectName . ".Handle_Click(this)";
        }
        
        $classes = array_unique($classes);

        $attributes['class'] = implode(' ', $classes);
        $attributes['id'] = $this->resolveID($isHeader, $entry);
        
        if (!empty($styles)) {
            $attributes['style'] = compileStyles($styles);
        }

        return ' ' . compileAttributes($attributes);
    }
    
    protected function resolveID(bool $isHeader, UI_DataGrid_Entry $entry=null) : string
    {
        $objectName = $this->getObjectName();
        
        $idSuffix = '';
        if($isHeader)
        {
            $idSuffix .= '-header';
        }
        else if($entry !== null)
        {
            $idSuffix .= '-row-'.$entry->getID();
        }
        
        return $objectName.$idSuffix;
    }
    
    /**
     * Reshuffles the grid's columns to insert this column at
     * the desired column number, starting at 1.
     *
     * @param integer $position
     * @return $this
     */
    public function moveTo(int $position) : UI_DataGrid_Column
    {
        $this->grid->moveColumn($this, $position);

        return $this;
    }

    /**
     * @var string
     */
    protected $role = 'cell';

    /**
     * @return $this
     */
    public function roleHeading() : UI_DataGrid_Column
    {
    	return $this->setRole('heading');
    }

    /**
     * @return $this
     */
    public function roleActions() : UI_DataGrid_Column
    {
    	return $this->setRole('actions');
    }

    /**
     * @param string $role
     * @return $this
     */
    protected function setRole(string $role) : UI_DataGrid_Column
    {
    	$this->role = $role;
    	return $this;
    }
    
    public function getNumber() : int
    {
    	return $this->number;
    }
    
    public function getRole() : string
    {
    	return $this->role;
    }

    /**
     * @var string|NULL
     */
    protected $cachedObjectName = null;
    
    public function getObjectName() : string
    {
        if(!isset($this->cachedObjectName)) {
            $this->cachedObjectName = 'col'.nextJSID();
        }
        
        return $this->cachedObjectName; 
    }
    
    public function injectJavascript(UI $ui, string $gridName) : void
    {
        $colName = $this->getObjectName();
        
        $ui->addJavascriptHeadStatement(
            sprintf(
                "var %s = %s.AddColumn",
                $colName,
                $gridName
            ),
            $this->getDataKey(),
            $this->getTitle(),
            $colName,
            $this->getType(),
            (string)$this->getNumber(),
            $this->getRole()
        );
        
        if($this->options[self::OPTION_ALIGN] === 'center') {
            $ui->addJavascriptHeadStatement(sprintf(
                '%s.AlignCenter',
                $colName        
            ));
        }

        if($this->options[self::OPTION_ALIGN] === 'right') {
            $ui->addJavascriptHeadStatement(sprintf(
                '%s.AlignRight',
                $colName        
            ));
        }
        
        if($this->editable) {
            $ui->addJavascriptHeadStatement(
                sprintf('%s.SetEditable', $colName),
                $this->editableClientClass
            );
        }
    }

    /**
     * @var bool
     */
    protected $editable = false;

    /**
     * @var string
     */
    protected $editableClientClass = '';
    
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
    public function setEditable(string $clientClassName) : UI_DataGrid_Column
    {
        $this->grid->requirePrimaryName();
        
        $this->editable = true;
        $this->editableClientClass = $clientClassName;
        
        UI::getInstance()->addJavascript('ui/datagrid/column/editable.js');
        
        return $this;
    }
    
   /**
    * Whether the cells in this column are editable.
    * @return boolean
    */
    public function isEditable() : bool
    {
        return $this->editable;
    }
    
   /**
    * Checks whether the role of this column is for
    * actions.
    * 
    * @return boolean
    */
    public function isAction() : bool
    {
        if($this->role === 'actions') {
            return true;
        }
        
        return false;
    }
    
   /**
    * Checks whether the column is sortable.
    * @return boolean
    */
    public function isSortable() : bool
    {
        return $this->options[self::OPTION_SORTABLE];
    }
    
   /**
    * Retrieves the name of the data key by which this column should be sorted.
    * @return string|NULL
    * @see setSortable()
    */
    public function getOrderKey() : ?string
    {
        if($this->isSortable()) {
            return $this->options[self::OPTION_SORT_KEY];
        }
        
        return null;
    }
    
    public function hasSortingCallback() : bool
    {
        return $this->isSortable() && isset($this->options[self::OPTION_SORT_CALLBACK]);
    }

    public function setHiddenForUser(bool $hidden, ?Application_User $user=null) : UI_DataGrid_Column
    {
        $this->getUserSettings()->setHiddenForUser($hidden, $user);
        return $this;
    }

    public function isHiddenForUser(?Application_User $user=null) : bool
    {
        return $this->getUserSettings()->isHiddenForUser($user);
    }

    private function getUserSettings() : UI_DataGrid_Column_UserSettings
    {
        if(!isset($this->userSettings))
        {
            $this->userSettings = new UI_DataGrid_Column_UserSettings($this);
        }

        return $this->userSettings;
    }

    /**
     * @param string $name
     * @throws UI_DataGrid_Exception
     */
    private function requireValidOption(string $name) : void
    {
        if (array_key_exists($name, $this->options))
        {
            return;
        }

        throw new UI_DataGrid_Exception(
            'Unknown column option.',
            sprintf(
                'The column option [%s] is not known.',
                $name
            ),
            self::ERROR_UNKNOWN_OPTION_NAME
        );
    }
}
