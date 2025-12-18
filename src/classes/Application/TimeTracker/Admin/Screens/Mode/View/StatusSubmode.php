<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin\Screens\Mode\ViewScreen;

use Application\TimeTracker\Admin\TimeTrackerScreenRights;
use Application\TimeTracker\Admin\Traits\ViewSubmodeInterface;
use Application\TimeTracker\Admin\Traits\ViewSubmodeTrait;
use Application\TimeTracker\TimeEntry;
use DBHelper\Admin\Screens\Submode\BaseRecordStatusSubmode;
use DBHelper\Interfaces\DBHelperRecordInterface;
use UI\AdminURLs\AdminURLInterface;
use UI_PropertiesGrid;

/**
 * @property TimeEntry $record
 */
class StatusSubmode extends BaseRecordStatusSubmode implements ViewSubmodeInterface
{
    use ViewSubmodeTrait;

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

    public function getRecordStatusURL(): AdminURLInterface
    {
        return $this->record->adminURL()->status();
    }

    protected function _populateGrid(UI_PropertiesGrid $grid, DBHelperRecordInterface $record): void
    {
        $grid->add(t('Start time'), $this->record->getStartTime()->toReadable());
        $grid->add(t('End time'), $this->record->getEndTime()->toReadable());
        $grid->add(t('Duration'), $this->record->getDuration()->getNormalized());
    }
}
