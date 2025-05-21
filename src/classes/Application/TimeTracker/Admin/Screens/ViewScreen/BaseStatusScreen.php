<?php

declare(strict_types=1);

namespace Application\Admin\Area\TimeTracker\ViewScreen;

use Application\AppFactory;
use Application\TimeTracker\TimeEntry;
use Application\TimeTracker\TimeTrackerCollection;
use Application_Admin_Area_Mode_Submode_CollectionRecord;
use DBHelper_BaseCollection;
use UI_PropertiesGrid;
use UI_Themes_Theme_ContentRenderer;

/**
 * @property TimeEntry $record
 */
abstract class BaseStatusScreen extends Application_Admin_Area_Mode_Submode_CollectionRecord
{
    public const URL_NAME = 'status';

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

    public function getDefaultAction(): string
    {
        return '';
    }

    /**
     * @return TimeTrackerCollection
     */
    protected function createCollection(): DBHelper_BaseCollection
    {
        return AppFactory::createTimeTracker();
    }

    public function getRecordMissingURL(): string
    {
        return (string)AppFactory::createTimeTracker()->adminURL()->list();
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
