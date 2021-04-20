<?php

class UI_DataGrid_Column
{
    const ERROR_SORT_DATA_COLUMN_MISSING = 17903;
    
   /**
    * @var UI_DataGrid
    */
    protected $grid;

    protected $dataKey;

    protected $title;
    
    protected $number;

   /**
    * @var UI
    */
    protected $ui;
    
    protected $options = array(
        'align' => 'left',
        'sortable' => false,
        'nowrap' => false,
        'hidden' => false,
        'width' => null,
        'width-type' => 'percent',
        'tooltip' => ''
    );

    public function __construct(UI_DataGrid $grid, $number, $dataKey, $title, $options = array())
    {
        $this->grid = $grid;
        $this->dataKey = $dataKey;
        $this->title = $title;
        $this->number = $number;
        $this->ui = $grid->getUI();

        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }
    }
    
    public function getType()
    {
        return 'Regular';
    }

    public function getDataKey()
    {
        return $this->dataKey;
    }

    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Forces contents of the column's cells not to break to a new line.
     * @return UI_DataGrid_Column
     */
    public function setNowrap()
    {
        return $this->setOption('nowrap', true);
    }

    /**
     * Aligns the contents of the column's cells to the center.
     * @return UI_DataGrid_Column
     */
    public function alignCenter()
    {
        return $this->setOption('align', 'center');
    }
    
    protected $classes = array();
    
   /**
    * Adds a class that will be added to all cells in this column.
    * 
    * @param string $class
    * @return UI_DataGrid_Column
    */
    public function addClass($class)
    {
        if(!in_array($class, $this->classes)) {
            $this->classes[] = $class;
        }
        
        return $this;
    }

    /**
     * Aligns the contents of the column's cells to the right.
     * @return UI_DataGrid_Column
     */
    public function alignRight()
    {
        return $this->setOption('align', 'right');
    }
    
   /**
    * Sets the tooltip text for the column: this will add the tooltip
    * to the column header.
    * 
    * @param string $text
    * @return UI_DataGrid_Column
    */
    public function setTooltip($text)
    {
        return $this->setOption('tooltip', $text);
    }

    /**
     * Makes the column as compact as possible. To avoid line
     * breaks in texts, combine this with {@setNowrap()}.
     * @return UI_DataGrid_Column
     */
    public function setCompact()
    {
        $this->setNowrap();
        return $this->setWidthPercent(1);
    }

    /**
     * Sets the column width as a percentage value.
     * @param int $width
     * @return $this
     */
    public function setWidthPercent(int $width)
    {
        $this->setOption('width', $width);
        $this->setOption('width-type', 'percent');

        return $this;
    }

    /**
     * Sets the column width as a fixed pixel value.
     * @param int $width
     * @return $this
     */
    public function setWidthPixels(int $width)
    {
        $this->setOption('width', $width);
        $this->setOption('width-type', 'pixels');
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
        return strval($this->options['width-type']);
    }

    public function getWidth() : int
    {
        return intval($this->options['width']);
    }

    public function resetWidth() : void
    {
        $this->setWidthPercent(0);
    }

    /**
     * Sets this column as sortable, which will allow the user to
     * sort by the contents of the column using clientside controls.
     *
     * @param string $dataKeyName The name of the data key to sort: use this if it is not the same as the column name.
     * @return UI_DataGrid_Column
     */
    public function setSortable($default=false, $dataKeyName=null)
    {
        if(empty($dataKeyName)) {
            $dataKeyName = $this->getDataKey();
        }
        
        $this->options['sortable'] = true;
        $this->options['sortKey'] = $dataKeyName;

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
    * @param string $dataColumn The name of the column in the data set to use as value for the sorting. Defaults to this column, but a different one can be specified as needed.
    * @return UI_DataGrid_Column
    */
    public function setSortingCallback($callback, $dataColumn=null): UI_DataGrid_Column
    {
        $this->setSortable();
        
        $this->options['sortCallback'] = $callback;
        $this->options['sortDataColumn'] = $dataColumn;
        
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
        if(isset($this->options['sortDataColumn'])) {
            $key = $this->options['sortDataColumn'];
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
        
        $result = call_user_func($this->options['sortCallback'], $dataA[$key], $dataB[$key]);
        
        if($this->grid->getOrderDir() == 'desc') {
            $result = $result * -1;
        }
        
        return $result;
    }

    /**
     * Sets the column as hidden: use this to hide ID columns for example.
     *
     * @return UI_DataGrid_Column
     */
    public function setHidden()
    {
        $this->options['hidden'] = true;

        return $this;
    }

    /**
     * Sets a column option. Alsoe see the dedicated methods that
     * allow setting the options without having to know the option's
     * name.
     *
     * @return UI_DataGrid_Column
     * @see setSortable()
     * @see setHidden()
     * @see setNowrap()
     * @see alignRight()
     * @see alignCenter()
     */
    public function setOption($name, $value)
    {
        if (!array_key_exists($name, $this->options)) {
            throw new Exception('Unknown option.');
        }

        $this->options[$name] = $value;

        return $this;
    }

    /**
     * Checks whether this column is hidden.
     * @return boolean
     */
    public function isHidden()
    {
        return $this->options['hidden'];
    }

    public function renderCell(UI_DataGrid_Entry $entry)
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

    public function renderHeaderCell()
    {
        if ($this->isHidden()) {
            return '';
        }
        
        $title = sb()->add($this->title);
        
        $icons = array();
        
        if($this->isSortable()) 
        {
            $this->addSortIcons($title);
        }
        
        if(!empty($this->options['tooltip'])) {
            $icons[] = UI::icon()->information()
                ->makeInformation()
                ->addClass('help')
                ->setTooltip($this->options['tooltip'])
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

    private function addSortIcons(\AppUtils\StringBuilder $text) : void
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
    public function isSorted()
    {
        $orderBy = $this->grid->getOrderBy();
         
        if(!empty($orderBy) && $orderBy == $this->getOrderKey()) {
            return true;
        }
        
        return false;
    }
    
    protected function renderAttributes($isHeader, $value=null, UI_DataGrid_Entry $entry=null)
    {
        $objectName = $this->getObjectName();
        
        $classes = $this->classes;
        $classes[] = 'align-' . $this->options['align'];
        $classes[] = 'role-' . $this->role;
        $classes[] = 'column-' . $this->number;

        if($this->isSorted()) {
            $classes[] = 'sorted';
            $classes[] = 'sorted-'.strtolower($this->grid->getOrderDir());
        }
        
        $styles = array();
        $attributes = array();

        if ($this->options['nowrap']) {
            $classes[] = 'nowrap';
        }

        if (!empty($this->options['width'])) {
            switch ($this->options['width-type']) {
                case 'percent':
                    $styles['width'] = $this->options['width'] . '%';
                    break;

                case 'pixels':
                    $styles['width'] = $this->options['width'] . 'px';
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
     * @since 3.3.7
     * @param integer $position
     * @return UI_DataGrid_Column
     */
    public function moveTo($position)
    {
        $this->grid->moveColumn($this, $position);

        return $this;
    }
    
    protected $role = 'cell';
    
    public function roleHeading()
    {
    	return $this->setRole('heading');
    }
    
    public function roleActions()
    {
    	return $this->setRole('actions');
    }
    
    protected function setRole($role)
    {
    	$this->role = $role;
    	return $this;
    }
    
    public function getNumber()
    {
    	return $this->number;
    }
    
    public function getRole()
    {
    	return $this->role;
    }
    
    protected $cachedObjectName;
    
    public function getObjectName()
    {
        if(!isset($this->cachedObjectName)) {
            $this->cachedObjectName = 'col'.nextJSID();
        }
        
        return $this->cachedObjectName; 
    }
    
    public function injectJavascript(UI $ui, $gridName)
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
        
        if($this->options['align'] == 'center') {
            $ui->addJavascriptHeadStatement(sprintf(
                '%s.AlignCenter',
                $colName        
            ));
        }

        if($this->options['align'] == 'right') {
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
    
    protected $editable = false;
    
    protected $editableClientClass;
    
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
    public function setEditable($clientClassName)
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
    public function isEditable()
    {
        return $this->editable;
    }
    
   /**
    * Checks whether the role of this column is for
    * actions.
    * 
    * @return boolean
    */
    public function isAction()
    {
        if($this->role=='actions') {
            return true;
        }
        
        return false;
    }
    
   /**
    * Checks whether the column is sortable.
    * @return boolean
    */
    public function isSortable()
    {
        return $this->options['sortable'];
    }
    
   /**
    * Retrieves the name of the data key by which this column should be sorted.
    * @return string|NULL
    * @see setSortable()
    */
    public function getOrderKey()
    {
        if($this->isSortable()) {
            return $this->options['sortKey'];
        }
        
        return null;
    }
    
    public function hasSortingCallback()
    {
        return $this->isSortable() && isset($this->options['sortCallback']);
    }
}