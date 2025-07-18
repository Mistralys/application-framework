<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin\Screens;

use Application\AppFactory;
use Application\TimeTracker\Admin\TimeUIManager;
use Application\TimeTracker\TimeSpans\TimeSpanCollection;
use Application\TimeTracker\TimeSpans\TimeSpanSettingsManager;
use Application\Traits\AllowableMigrationTrait;
use Application_Admin_Area_Mode_CollectionCreate;
use DBHelper_BaseRecord;
use Application\TimeTracker\Admin\TimeTrackerScreenRights;
use Application\TimeTracker\TimeSettingsManager;
use Application\TimeTracker\TimeTrackerCollection;

abstract class BaseCreateTimeSpanScreen extends Application_Admin_Area_Mode_CollectionCreate
{
    use AllowableMigrationTrait;

    public const URL_NAME = 'create-time-span';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getTitle(): string
    {
        return t('Create a time span');
    }

    public function createCollection() : TimeSpanCollection
    {
        return AppFactory::createTimeTracker()->createTimeSpans();
    }

    public function getSettingsManager() : TimeSpanSettingsManager
    {
        return new TimeSpanSettingsManager($this);
    }

    public function getSuccessMessage(DBHelper_BaseRecord $record): string
    {
        return t(
            'The time span has been added successfully at %1$s.',
            sb()->time()
        );
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendArea($this->area);

        $this->breadcrumb->appendItem($this->getNavigationTitle())
            ->makeLinked($this->createCollection()->adminURL()->list());
    }

    public function getSuccessURL(DBHelper_BaseRecord $record): string
    {
        return $this->getBackOrCancelURL();
    }

    public function getBackOrCancelURL(): string
    {
        return (string)$this->createCollection()->adminURL()->list();
    }

    public function getRequiredRight(): string
    {
        return TimeTrackerScreenRights::SCREEN_TIME_SPANS_CREATE;
    }
}
