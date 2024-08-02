<?php
/**
 * @package Application
 * @subpackage Abstract Admin Screens
 */

declare(strict_types=1);

use Application\AppFactory;
use AppUtils\ConvertHelper;

/**
 * @package Application
 * @subpackage Abstract Admin Screens
 */
abstract class BaseMessageLogScreen extends Application_Admin_Area_Mode
{
    public const URL_NAME = 'messagelog';
    public const GRID_NAME = 'app_messagelog';

    public const COL_ID = 'id';
    public const COL_DATE = 'date';
    public const COL_TYPE = 'type';
    public const COL_CATEGORY = 'category';
    public const COL_USER = 'user';
    public const COL_MESSAGE = 'message';

    public const REQUEST_PARAM_GENERATE_ENTRIES = 'generate-entries';

    protected UI_DataGrid $grid;
    protected Application_Messagelogs $collection;
    protected Application_Messagelogs_FilterSettings $filterSettings;
    protected Application_Messagelogs_FilterCriteria $filters;

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
        return self::URL_NAME;
    }
    
    public function getTitle() : string
    {
        return t('Application messagelog');
    }
    
    protected function _handleActions() : bool
    {
        $this->collection = AppFactory::createMessageLog();
        $this->filterSettings = $this->collection->getFilterSettings();
        $this->filters = $this->collection->getFilterCriteria();

        if($this->request->getBool(self::REQUEST_PARAM_GENERATE_ENTRIES)) {
            $this->generateTestEntries();
        }

        $this->createDataGrid();

        return true;
    }
    
    protected function _handleSidebar() : void
    {
        $this->sidebar->addFilterSettings($this->filterSettings);

        $this->sidebar->addSeparator();

        $panel = $this->sidebar->addDeveloperPanel();
        $panel->addButton(UI::button(t('Generate test entries'))
            ->link($this->getURL(array(self::REQUEST_PARAM_GENERATE_ENTRIES => 'yes')))
            ->setIcon(UI::icon()->generate())
        );
    }
    
    protected function _renderContent()
    {
        $items = $this->filters->getItemsObjects();
        $entries = array();
        
        foreach($items as $item) 
        {
            $entries[] = array(
                self::COL_ID => $item->getID(),
                self::COL_DATE => ConvertHelper::date2listLabel($item->getDate(), true, true),
                self::COL_TYPE => $item->getType(),
                self::COL_CATEGORY => $item->getCategory(),
                self::COL_USER => $item->getUser()->getName(),
                self::COL_MESSAGE => $item->getMessage()
            );
        }
        
        return $this->renderer
        ->setTitle($this->getTitle())
        ->appendDataGrid($this->grid, $entries)
        ->makeWithSidebar();
    }
    
    protected function createDataGrid() : void
    {
        $grid = $this->ui->createDataGrid(self::GRID_NAME);
        $grid->addColumn(self::COL_ID, t('ID'))->setSortable()->setCompact();
        $grid->addColumn(self::COL_DATE, t('Date'))->setSortable(true)->setNowrap();
        $grid->addColumn(self::COL_TYPE, t('Type'))->setSortable()->setNowrap();
        $grid->addColumn(self::COL_CATEGORY, t('Category'))->setSortable()->setNowrap();
        $grid->addColumn(self::COL_USER, t('User'))->setNowrap();
        $grid->addColumn(self::COL_MESSAGE, t('Message'));
        
        $grid->enableLimitOptions(UI_DataGrid::DEFAULT_LIMIT_CHOICES);
        $grid->addHiddenScreenVars();
        
        $grid->configure($this->filterSettings, $this->filters);
        
        $this->grid = $grid;
    }

    private function generateTestEntries() : void
    {
        if(!$this->user->isDeveloper()) {
            return;
        }

        $this->startTransaction();
            $this->collection->generateDeveloperTestEntries();
        $this->endTransaction();

        $this->redirectWithSuccessMessage(
            t(
                'The test log entries have been generated successfully at %1$s.',
                sb()->time()
            ),
            $this->getURL()
        );
    }
}
