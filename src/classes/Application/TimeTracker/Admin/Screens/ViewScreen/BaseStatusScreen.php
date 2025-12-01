<?php

declare(strict_types=1);

namespace Application\Admin\Area\TimeTracker\ViewScreen;

use Application\AppFactory;
use Application\TimeTracker\Admin\TimeTrackerScreenRights;
use Application\TimeTracker\TimeEntry;
use Application\TimeTracker\TimeTrackerCollection;
use DBHelper\Admin\Screens\Submode\BaseRecordSubmode;
use UI\AdminURLs\AdminURLInterface;
use UI_PropertiesGrid;
use UI_Themes_Theme_ContentRenderer;

/**
 * @property TimeEntry $record
 */
abstract class BaseStatusScreen extends BaseRecordSubmode
{
    public const string URL_NAME = 'status';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('Status');
    }

    public function getTitle(): string
    {
        return t('Status');
    }

    public function getRequiredRight(): string
    {
        return TimeTrackerScreenRights::SCREEN_VIEW_STATUS;
    }

    public function getDefaultAction(): string
    {
        return '';
    }

    /**
     * @return TimeTrackerCollection
     */
    protected function createCollection(): TimeTrackerCollection
    {
        return AppFactory::createTimeTracker();
    }

    public function getRecordMissingURL(): AdminURLInterface
    {
        return AppFactory::createTimeTracker()->adminURL()->list();
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer
            ->appendContent($this->createPropertyGrid())
            ->makeWithoutSidebar();
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem($this->getNavigationTitle())
            ->makeLinked($this->record->adminURL()->status());
    }

    private function createPropertyGrid() : UI_PropertiesGrid
    {
        $grid = $this->ui->createPropertiesGrid();

        $grid->add(t('Start time'), $this->record->getStartTime()->toReadable());
        $grid->add(t('End time'), $this->record->getEndTime()->toReadable());
        $grid->add(t('Duration'), $this->record->getDuration()->getNormalized());

        return $grid;
    }
}
