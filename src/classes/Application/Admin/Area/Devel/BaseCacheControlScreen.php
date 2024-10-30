<?php
/**
 * @package Application
 * @subpackage CacheControl
 */

declare(strict_types=1);

namespace Application\Admin\Area\Devel;

use Application\AppFactory;
use Application\CacheControl\CacheManager;
use Application_Admin_Area_Mode;
use AppUtils\ConvertHelper;
use Closure;
use UI;
use UI_DataGrid;
use UI_DataGrid_Action;
use UI_Themes_Theme_ContentRenderer;

/**
 * Base class for the cache control screen, where the available
 * cache locations of the application can be managed.
 *
 * @package Application
 * @subpackage CacheControl
 */
class BaseCacheControlScreen extends Application_Admin_Area_Mode
{
    public const URL_NAME = 'cache-control';
    public const COL_ID = 'id';
    public const COL_LABEL = 'label';
    public const COL_SIZE = 'size';
    public const GRID_NAME = 'dev-cache-control';
    public const COL_SIZE_BYTES = 'size_bytes';

    private CacheManager $manager;
    private UI_DataGrid $grid;

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function isUserAllowed(): bool
    {
        return $this->user->isDeveloper();
    }

    public function getNavigationTitle(): string
    {
        return t('Cache control');
    }

    public function getTitle(): string
    {
        return t('Cache control');
    }

    public function getDefaultSubmode(): string
    {
        return '';
    }

    protected function _handleActions(): bool
    {
        $this->manager = AppFactory::createCacheManager();
        $this->grid = $this->createDataGrid();

        return true;
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem($this->getNavigationTitle())
            ->makeLinked($this->getURL());
    }

    protected function _handleHelp(): void
    {
        $this->renderer
            ->getTitle()
            ->setIcon(UI::icon()->cache())
            ->setText($this->getTitle());

        $this->renderer->setAbstract(sb()
            ->t('These are all cache locations used in %1$s.', $this->driver->getAppNameShort())
            ->t('Use the list actions to choose among the possible maintenance tasks for the selected locations.')
        );
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer
            ->appendDataGrid($this->grid, $this->collectEntries())
            ->makeWithoutSidebar();
    }

    private function collectEntries() : array
    {
        $entries = array();

        foreach($this->manager->getAll() as $location) {
            $entry = $this->grid->createEntry(array(
                self::COL_ID => $location->getID(),
                self::COL_SIZE => ConvertHelper::bytes2readable($location->getByteSize()),
                self::COL_SIZE_BYTES => $location->getByteSize()
            ));

            $entry->setColumnValue(self::COL_LABEL, $entry->renderCheckboxLabel($location->getLabel()));

            $entries[] = $entry;
        }

        return $entries;
    }

    private function createDataGrid() : UI_DataGrid
    {
        $grid = $this->ui->createDataGrid(self::GRID_NAME);
        $grid->addHiddenScreenVars();

        $grid->addColumn(self::COL_LABEL, t('Label'))
            ->setSortingString();

        $grid->addColumn(self::COL_SIZE, t('Size'))
            ->setSortingNumeric(self::COL_SIZE_BYTES);

        $grid->enableMultiSelect(self::COL_ID);

        $grid->addAction(
            'clear',
            t('Clear cache')
        )
            ->setIcon(UI::icon()->delete())
            ->makeConfirm(t('Do you really want to clear the cache of the selected locations?'))
            ->makeDangerous()
            ->setCallback(Closure::fromCallable(array($this, 'handle_multiClearCache')));

        return $grid;
    }

    private function handle_multiClearCache(UI_DataGrid_Action $action) : void
    {
        $msg = $action->createRedirectMessage($this->getURL())
            ->single(t('The %1$s cache location has been cleared.', '$label'))
            ->multiple(t('%1$s cache locations have been cleared.', '$amount'))
            ->none(t('No cache locations selected that could be cleared.'));

        foreach($action->getSelectedValues() as $locationID)
        {
            $locationID = (string)$locationID;
            if(!$this->manager->idExists($locationID)) {
                continue;
            }

            $location = $this->manager->getByID($locationID);
            $location->clear();

            $msg->addAffected($location->getLabel());
        }

        $msg->redirect();
    }
}
