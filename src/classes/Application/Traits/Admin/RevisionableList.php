<?php

declare(strict_types=1);

use Application\Revisionable\Collection\BaseRevisionableDataGridMultiAction;
use Application\Revisionable\Collection\RevisionableCollectionInterface;
use Application\Revisionable\Collection\RevisionableFilterSettingsInterface;
use Application\Revisionable\RevisionableInterface;
use AppUtils\ConvertHelper;

/**
 * @see Application_Interfaces_Admin_RevisionableList
 * 
 * @property string $recordTypeName
 * @property RevisionableCollectionInterface $collection
 */
trait Application_Traits_Admin_RevisionableList
{
    protected string $gridName = '';
    protected UI_DataGrid $grid;
    protected RevisionableFilterSettingsInterface $filterSettings;
    protected bool $filtersAdded = false;

    public function getURLName() : string
    {
        return 'list';
    }
    
    protected function _handleActions() : bool
    {
        $this->collection = $this->getCollection();
        $this->gridName = $this->collection->getRecordTypeName().'-list';
        $this->filterSettings = $this->collection->getFilterSettings();
        
        $this->createDataGrid();

        return true;
    }

    abstract protected function getEntryData(RevisionableInterface $revisionable) : array;
    
    protected function _renderContent() : string
    {
        $filters = $this->collection->getFilterCriteria();
        
        $this->grid->configure($this->filterSettings, $filters);
        
        $items = $filters->getItemsObjects();
        
        $total = count($items);
        $primaryKey = $this->collection->getRecordPrimaryName();
        $entries = array();
        $hasState = $this->grid->hasColumn('state');
        $hasLastModified = $this->grid->hasColumn('last_modified');
        for($i=0; $i < $total; $i++)
        {
            $item = $items[$i];
            $entry = $this->getEntryData($item);
            $entry[$primaryKey] = $item->getID();
            
            if($hasState) {
                $entry['state'] = $item->requireState()->getPrettyLabel();
            }
            
            if($hasLastModified) {
                $entry['last_modified'] = ConvertHelper::date2listLabel($item->getLastModifiedDate());
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
        $grid->enableMultiSelect($this->collection->getRecordPrimaryName());
        $grid->enableLimitOptionsDefault();
        
        $this->configureGrid();
        
        $grid->executeCallbacks();
    }
    
    protected function configureGrid() : void
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
     * @return BaseRevisionableDataGridMultiAction
     */
    public function addMultiAction(string $className, string $label, string $redirectURL, bool $confirm=false) : BaseRevisionableDataGridMultiAction
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
