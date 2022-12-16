<?php

/**
 * @see Application_Interfaces_Admin_RevisionableList
 * 
 * @property string $recordTypeName
 * @property Application_RevisionableCollection $collection
 */
trait Application_Traits_Admin_RevisionableList
{
    /**
     * @var string
     */
    protected $gridName = '';
    
    /**
     * @var UI_DataGrid
     */
    protected $grid;
    
    /**
     * @var Application_RevisionableCollection_FilterSettings
     */
    protected $filterSettings;
    
    public function getURLName() : string
    {
        return 'list';
    }
    
    protected function _handleActions() : bool
    {
        $this->gridName = $this->recordTypeName.'-list';
        $this->filterSettings = $this->collection->getFilterSettings();
        
        $this->createDataGrid();

        return true;
    }
    
    abstract protected function getEntryData(Application_RevisionableCollection_DBRevisionable $revisionable);
    
    protected function _renderContent() : string
    {
        $filters = $this->collection->getFilterCriteria();
        
        $this->grid->configure($this->filterSettings, $filters);
        
        $items = $filters->getItemsObjects();
        
        $total = count($items);
        $primaryKey = $this->collection->getPrimaryKeyName();
        $entries = array();
        $hasState = $this->grid->hasColumn('state');
        $hasLastModified = $this->grid->hasColumn('last_modified');
        for($i=0; $i < $total; $i++)
        {
            $item = $items[$i];
            $entry = $this->getEntryData($item);
            $entry[$primaryKey] = $item->getID();
            
            if($hasState) {
                $entry['state'] = $item->getState()->getPrettyLabel();
            }
            
            if($hasLastModified) {
                $entry['last_modified'] = AppUtils\ConvertHelper::date2listLabel($item->getLastModifiedDate());
            }
            
            $entries[] = $entry;
        }
        
        return $this->renderDatagrid($this->getTitle(), $this->grid, $entries);
    }
    
    protected function createDataGrid() : void
    {
        $grid = $this->ui->createDataGrid($this->gridName);
        $this->grid = $grid;
        
        $grid->setFullViewTitle($this->getTitle());
        $grid->enableMultiSelect($this->collection->getPrimaryKeyName());
        $grid->enableLimitOptionsDefault();
        
        $this->configureGrid($grid);
        
        $grid->executeCallbacks();
    }
    
    protected function configureGrid(UI_DataGrid $grid) : void
    {
        $this->configureColumns();
        $this->configureActions();
    }
    
    abstract protected function configureColumns();
    
    abstract protected function configureActions();
    
    /**
     * Adds the revisionable's state to the datagrid in the <code>state</code> column.
     * @return UI_DataGrid_Column
     */
    protected function addStateColumn() : UI_DataGrid_Column
    {
        return $this->grid->addColumn('state', t('State'))->setSortable();
    }
    
    protected function addLastModifiedColumn() : UI_DataGrid_Column
    {
        return $this->grid->addColumn('last_modified', t('Last modified'))->setSortable();
    }
    
    abstract public function getBackOrCancelURL() : string;
    
    protected function _handleSidebar() : void
    {
        $this->addFilterSettings();
    }

    /**
     * @var bool
     */
    protected $filtersAdded = false;
    
    protected function addFilterSettings() : void
    {
        if($this->filtersAdded) {
            return;
        }
        
        $this->filtersAdded = true;
        
        $this->sidebar->addSeparator();
        $this->sidebar->addFilterSettings($this->filterSettings);
    }
    
    /**
     * @param string $className
     * @param string $label
     * @param string $redirectURL
     * @param boolean $confirm
     * @return Application_RevisionableCollection_DataGridMultiAction
     */
    public function addMultiAction(string $className, string $label, string $redirectURL, bool $confirm=false) : Application_RevisionableCollection_DataGridMultiAction
    {
        return $this->collection->createListMultiAction(
            $className,
            $this,
            $this->grid,
            $label,
            $redirectURL,
            $confirm
        );
    }
}
