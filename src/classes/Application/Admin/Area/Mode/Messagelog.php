<?php

abstract class Application_Admin_Area_Mode_Messagelog extends Application_Admin_Area_Mode
{
    public function getDefaultSubmode() : string
    {
        return '';
    }
    
    public function getNavigationTitle() : string
    {
        return t('Messagelog');
    }
    
    public function isUserAllowed() : bool
    {
        return $this->user->isDeveloper();
    }
    
    public function getURLName() : string
    {
        return 'messagelog';
    }
    
    public function getTitle() : string
    {
        return t('Application messagelog');
    }
    
   /**
    * @var Application_Messagelogs
    */
    protected $collection;
    
   /**
    * @var Application_Messagelogs_FilterSettings
    */
    protected $filterSettings;
    
   /**
    * @var Application_Messagelogs_FilterCriteria
    */
    protected $filters;
    
    protected function _handleActions() : bool
    {
        $this->collection = Application::getMessageLog();
        $this->filterSettings = $this->collection->getFilterSettings();
        $this->filters = $this->collection->getFilterCriteria();
        
        $this->createDataGrid();

        return true;
    }
    
    protected function _handleSidebar() : void
    {
        $this->sidebar->addFilterSettings($this->filterSettings);
    }
    
    protected function _renderContent()
    {
        $items = $this->filters->getItemsObjects();
        $entries = array();
        
        foreach($items as $item) 
        {
            $entries[] = array(
                'id' => $item->getID(),
                'date' => AppUtils\ConvertHelper::date2listLabel($item->getDate(), true, true),
                'type' => $item->getType(),
                'category' => $item->getCategory(),
                'user' => $item->getUser()->getName(),
                'message' => $item->getMessage()
            );
        }
        
        return $this->renderer
        ->setTitle($this->getTitle())
        ->appendDataGrid($this->grid, $entries)
        ->makeWithSidebar();
    }
    
   /**
    * @var UI_DataGrid
    */
    protected $grid;
    
    protected function createDataGrid() : void
    {
        $grid = $this->ui->createDataGrid('app_messagelog');
        $grid->addColumn('id', t('ID'))->setSortable()->setCompact();
        $grid->addColumn('date', t('Date'))->setSortable(true)->setNowrap();
        $grid->addColumn('type', t('Type'))->setSortable()->setNowrap();
        $grid->addColumn('category', t('Category'))->setSortable()->setNowrap();
        $grid->addColumn('user', t('User'))->setNowrap();
        $grid->addColumn('message', t('Message'));
        
        $grid->enableLimitOptions(UI_DataGrid::DEFAULT_LIMIT_CHOICES);
        
        $grid->configure($this->filterSettings, $this->filters);
        
        $this->grid = $grid;
    }
}
