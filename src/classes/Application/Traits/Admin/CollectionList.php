<?php
/**
 * File containing the {@see Application_Traits_Admin_CollectionList} trait.
 *
 * @package Application
 * @subpackage Admin
 * @see Application_Traits_Admin_CollectionList
 */

use Application\Interfaces\Admin\CollectionListInterface;
use AppUtils\Interfaces\StringableInterface;

/**
 * Trait used for simplify displaying lists of DBHelper records:
 * handles all the required configuration, and offers a standardized
 * interface of overloadable methods to set it up.
 * 
 * Usage:
 * 
 * # Implement the abstract methods
 * # Overload <code>validateRequest</code> as needed (called directly after _handleActions)
 * # Overload <code>configureFilters</code> to customize the filter criteria as needed
 * # Overload <code>_handleSidebar</code> if needed, taking care to call the parent method
 * # Overload <code>getURLName()</code> if different from "list"
 * 
 * @package Application
 * @subpackage Admin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see CollectionListInterface
 */
trait Application_Traits_Admin_CollectionList
{
   /**
    * @var DBHelper_BaseCollection
    */
    protected $collection;
    
   /**
    * @var string
    */
    protected string $gridName;
    
    /**
     * @var UI_DataGrid
     */
    protected UI_DataGrid $grid;
    
    /**
     * @var DBHelper_BaseFilterSettings
     */
    protected $filterSettings;
    
   /**
    * @var DBHelper_BaseFilterCriteria
    */
    protected $filters;
    
   /**
    * @var bool
    */
    protected bool $filtersAdded = false;
    
    public function getURLName() : string
    {
        return CollectionListInterface::URL_NAME_DEFAULT;
    }
    
    protected function _handleActions() : bool
    {
        $this->collection = $this->createCollection();
        $this->gridName = $this->getGridName();
        $this->filterSettings = $this->collection->getFilterSettings();
        $this->filters = $this->collection->getFilterCriteria();

        $this->filterSettings->setID($this->gridName);

        $this->validateRequest();
        
        $this->createDataGrid();

        return true;
    }

    public function getGridName() : string
    {
        return $this->collection->getDataGridName();
    }
    
    protected function validateRequest() : void
    {
        
    }
    
    protected function configureFilters() : void
    {
        
    }

    protected function configureFilterSettings() : void
    {

    }
    
   /**
    * @return DBHelper_BaseCollection
    */
    abstract protected function createCollection() : DBHelper_BaseCollection;

    /**
     * @param DBHelper_BaseRecord $record
     * @param DBHelper_BaseFilterCriteria_Record $entry
     * @return array<string,string|number|bool|StringableInterface>|UI_DataGrid_Entry
     */
    abstract protected function getEntryData(DBHelper_BaseRecord $record, DBHelper_BaseFilterCriteria_Record $entry);
    
    abstract protected function configureColumns() : void;
    
    abstract protected function configureActions() : void;
    
    abstract public function getBackOrCancelURL() : string;
    
    protected function _handleBreadcrumb() : void
    {
        $this->breadcrumb->appendItem($this->getNavigationTitle());
    }
    
    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        $this->grid->configure($this->filterSettings, $this->filters);
        
        $items = $this->filters->getItemsDetailed();
        
        $total = count($items);
        $primaryKey = $this->collection->getRecordPrimaryName();
        $entries = array();
        for($i=0; $i < $total; $i++)
        {
            $item = $items[$i];
            $entry = $this->getEntryData($item->getRecord(), $item);
            $entry[$primaryKey] = $item->getID();
            $entries[] = $entry;
        }

        if(!$this->renderer->getTitle()->hasText())
        {
            $this->renderer->setTitle($this->getTitle());
        }

        return $this->renderer
        ->makeWithSidebar()
        ->appendDataGrid($this->grid, $entries);
    }

    protected function createDataGrid() : void
    {
        $grid = $this->ui->createDataGrid($this->gridName);
        $this->grid = $grid;
        
        $grid->setFullViewTitle($this->getTitle());
        $grid->enableMultiSelect($this->collection->getRecordPrimaryName());
        $grid->enableLimitOptionsDefault();
        
        $names = $this->driver->getURLParamNames();
        foreach($names as $name)
        {
            $value = $this->request->getParam($name);
            if(!empty($value))
            {
                $grid->addHiddenVar($name, $value);
            }
        }
        
        $this->configureGrid();
        
        $grid->executeCallbacks();
    }
    
    protected function configureGrid() : void
    {
        $this->configureColumns();
        $this->configureActions();
        $this->configureFilters();
        $this->configureFilterSettings();

        $vars = $this->getPersistVars();

        foreach($vars as $name => $value) {
            $this->grid->addHiddenVar($name, $value);
            $this->filterSettings->addHiddenVar($name, $value);
        }
    }

    public function getPersistVars() : array
    {
        return array();
    }
    
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
        
        $total = $this->filters->countUnfiltered();
        if($total > 0) {
            $this->sidebar->addSeparator();
            $this->sidebar->addFilterSettings($this->filterSettings);
        }
    }
}
