<?php

/**
 * @see Application_Interfaces_Admin_RevisionableList
 * 
 * @property string $recordTypeName
 * @property Application_RevisionableCollection $collection
 */
trait Application_Traits_Admin_RevisionableList
{
    protected $gridName;
    
    /**
     * @var UI_DataGrid
     */
    protected $grid;
    
    /**
     * @var Application_RevisionableCollection_FilterSettings
     */
    protected $filterSettings;
    
    public function getURLName()
    {
        return 'list';
    }
    
    protected function _handleActions()
    {
        $this->gridName = $this->recordTypeName.'-list';
        $this->filterSettings = $this->collection->getFilterSettings();
        
        $this->createDataGrid();
    }
    
    abstract protected function getEntryData(Application_RevisionableCollection_DBRevisionable $revisionable);
    
    protected function _renderContent()
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
    
    protected function createDataGrid()
    {
        $grid = $this->ui->createDataGrid($this->gridName);
        $this->grid = $grid;
        
        $grid->setFullViewTitle($this->getTitle());
        $grid->enableMultiSelect($this->collection->getPrimaryKeyName());
        $grid->enableLimitOptions(UI_DataGrid::DEFAULT_LIMIT_CHOICES);
        
        $this->configureGrid($grid);
        
        $grid->executeCallbacks();
    }
    
    protected function configureGrid(UI_DataGrid $grid)
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
    protected function addStateColumn()
    {
        return $this->grid->addColumn('state', t('State'))->setSortable();
    }
    
    protected function addLastModifiedColumn()
    {
        return $this->grid->addColumn('last_modified', t('Last modified'))->setSortable();
    }
    
    abstract public function getBackOrCancelURL();
    
    protected function _handleSidebar()
    {
        $this->addFilterSettings();
    }
    
    protected $filtersAdded = false;
    
    protected function addFilterSettings()
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
    public function addMultiAction($className, $label, $redirectURL, $confirm=false)
    {
        $action = $this->collection->createListMultiAction($className, $this, $this->grid, $label, $redirectURL, $confirm);
        return $action;
    }
}
