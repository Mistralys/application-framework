<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin\Screens;

use Application\AppFactory;
use Application\TimeTracker\TimeSpans\TimeSpanCollection;
use Application\TimeTracker\TimeSpans\TimeSpanSettingsManager;
use DBHelper\Admin\Screens\Mode\BaseRecordCreateMode;
use DBHelper\Interfaces\DBHelperRecordInterface;
use Application\TimeTracker\Admin\TimeTrackerScreenRights;

abstract class BaseCreateTimeSpanScreen extends BaseRecordCreateMode
{
    public const string URL_NAME = 'create-time-span';

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

    public function getSuccessMessage(DBHelperRecordInterface $record): string
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

    public function getSuccessURL(DBHelperRecordInterface $record): string
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
