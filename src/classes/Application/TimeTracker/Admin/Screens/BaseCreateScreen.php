<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin\Screens;

use Application\AppFactory;
use Application\Traits\AllowableMigrationTrait;
use Application_Admin_Area_Mode_CollectionCreate;
use DBHelper_BaseRecord;
use Application\TimeTracker\Admin\TimeTrackerScreenRights;
use Application\TimeTracker\TimeSettingsManager;
use Application\TimeTracker\TimeTrackerCollection;

abstract class BaseCreateScreen extends Application_Admin_Area_Mode_CollectionCreate
{
    use AllowableMigrationTrait;

    public const URL_NAME = 'create';

    public function getTitle(): string
    {
        return t('Create a time entry');
    }

    public function createCollection() : TimeTrackerCollection
    {
        return AppFactory::createTimeTracker();
    }

    public function getSettingsManager() : TimeSettingsManager
    {
        return new TimeSettingsManager($this);
    }

    public function getSuccessMessage(DBHelper_BaseRecord $record): string
    {
        return t(
            'The time entry has been added successfully at %1$s.',
            sb()->time()
        );
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendArea($this->area);

        $this->breadcrumb->appendItem($this->getNavigationTitle())
            ->makeLinked($this->createCollection()->adminURL()->list());
    }

    public function getBackOrCancelURL(): string
    {
        return (string)AppFactory::createTimeTracker()->adminURL()->list();
    }

    public function getRequiredRight(): string
    {
        return TimeTrackerScreenRights::SCREEN_CREATE_ENTRY;
    }
}
