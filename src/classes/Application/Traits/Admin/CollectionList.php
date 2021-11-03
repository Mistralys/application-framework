<?php
/**
 * File containing the {@see Application_Traits_Admin_CollectionList} trait.
 *
 * @package Application
 * @subpackage Admin
 * @see Application_Traits_Admin_CollectionList
 */

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
 * @property UI $ui
 * @property UI_Page_Sidebar $sidebar
 * @property Application_Request $request
 * @property Application_Driver $driver
 * @property Application_User $user
 * @property UI_Page $page
 * @property UI_Page_Breadcrumb $breadcrumb
 * @property Application_Session $session
 * @property Application_LockManager $lockManager
 * @property UI_Themes_Theme_ContentRenderer $renderer 
 * @property bool $adminMode
 * @property string $instanceID
 * @method Application_Admin_Skeleton getParent()
 * @method void createFormableForm($defaultData, $jsid)
 * @method void startTransaction()
 * @method void endTransaction()
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
    protected $gridName;
    
    /**
     * @var UI_DataGrid
     */
    protected $grid;
    
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
    protected $filtersAdded = false;
    
    public function getURLName() : string
    {
        return 'list';
    }
    
    protected function _handleActions() : bool
    {
        $this->collection = $this->createCollection();
        $this->gridName = $this->collection->getDataGridName();
        $this->filterSettings = $this->collection->getFilterSettings();
        $this->filters = $this->collection->getFilterCriteria();
        
        $this->validateRequest();
        
        $this->createDataGrid();

        return true;
    }
    
    protected function validateRequest() : void
    {
        
    }
    
    protected function configureFilters() : void
    {
        
    }
    
   /**
    * @return DBHelper_BaseCollection
    */
    abstract protected function createCollection() : DBHelper_BaseCollection;
    
    abstract protected function getEntryData(DBHelper_BaseRecord $record, DBHelper_BaseFilterCriteria_Record $entry);
    
    abstract protected function configureColumns() : void;
    
    abstract protected function configureActions() : void;
    
    abstract public function getBackOrCancelURL();
    
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
        $grid->enableLimitOptions(UI_DataGrid::DEFAULT_LIMIT_CHOICES);
        
        $names = $this->driver->getURLParamNames();
        foreach($names as $name)
        {
            $value = $this->request->getParam($name);
            if(!empty($value))
            {
                $grid->addHiddenVar($name, $value);
            }
        }
        
        $this->configureGrid($grid);
        
        $grid->executeCallbacks();
    }
    
    protected function configureGrid(UI_DataGrid $grid) : void
    {
        $this->configureColumns();
        $this->configureActions();
        $this->configureFilters();
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
