<?php
/**
 * @package Time Tracker
 * @subpackage Entries
 */

declare(strict_types=1);

namespace Application\TimeTracker;

use Application\AppFactory;
use Application\TimeTracker\TimeSpans\TimeSpanCollection;
use Application\TimeTracker\Types\TimeEntryType;
use AppUtils\DateTimeHelper\DaytimeStringInfo;
use AppUtils\Microtime;
use DBHelper_BaseCollection;
use Application\TimeTracker\Admin\TrackerAdminURLs;

/**
 * @package Time Tracker
 * @subpackage Entries
 *
 * @method TimeEntry[] getAll()
 * @method TimeEntry getByID(int $record_id)
 * @method TimeFilterCriteria getFilterCriteria()
 * @method TimeFilterSettings getFilterSettings()
 * @method TimeEntry createNewRecord(array $data = array(), bool $silent = false, array $options = array())
 */
class TimeTrackerCollection extends DBHelper_BaseCollection
{
    public const TABLE_NAME = 'time_tracker_entries';
    public const PRIMARY_NAME = 'time_entry_id';
    public const COL_LABEL = 'label';
    public const COL_DATE = 'date';
    public const COL_TIME_START = 'time_start';
    public const COL_TIME_END = 'time_end';
    public const COL_DURATION = 'duration';
    public const COL_TYPE = 'type';
    public const COL_TICKET = 'ticket';
    public const COL_TICKET_URL = 'ticket_url';
    public const COL_PROCESSED = 'processed';
    public const COL_COMMENTS = 'comments';
    public const COL_USER_ID = 'user_id';
    public const REQUEST_PARAM_ENTRY = 'time-entry';
    public const DATE_FORMAT = 'Y-m-d';

    public function getRecordTypeName(): string
    {
        return 'time_entry';
    }

    public function getRecordRequestPrimaryName(): string
    {
        return self::REQUEST_PARAM_ENTRY;
    }

    public function getCollectionLabel(): string
    {
        return t('Time entries');
    }

    public function getRecordLabel(): string
    {
        return t('Time entry');
    }

    private ?TrackerAdminURLs $adminURLs = null;

    public function adminURL() : TrackerAdminURLs
    {
        if(!isset($this->adminURLs)) {
            $this->adminURLs = new TrackerAdminURLs();
        }

        return $this->adminURLs;
    }

    public function getRecordClassName(): string
    {
        return TimeEntry::class;
    }

    public function getRecordFiltersClassName(): string
    {
        return TimeFilterCriteria::class;
    }

    public function getRecordFilterSettingsClassName(): string
    {
        return TimeFilterSettings::class;
    }

    public function getRecordDefaultSortKey(): string
    {
        return self::COL_DATE;
    }

    public function getRecordSearchableColumns(): array
    {
        return array(
            self::COL_TICKET => t('Ticket'),
            self::COL_COMMENTS => t('Comments')
        );
    }

    public function getRecordTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function getRecordPrimaryName(): string
    {
        return self::PRIMARY_NAME;
    }

    public function getRecordProperties(): array
    {
        return array();
    }

    private ?TimeSpanCollection $timeSpans = null;

    public function createTimeSpans() : TimeSpanCollection
    {
        if (!isset($this->timeSpans)) {
            $this->timeSpans = new TimeSpanCollection();
        }

        return $this->timeSpans;
    }

    public function createNewEntryByDuration(Microtime $date, DaytimeStringInfo $timeStart, int $duration, TimeEntryType $type) : TimeEntry
    {
        return $this->createNewRecord(array(
            self::COL_DATE => $date->format(self::DATE_FORMAT),
            self::COL_TIME_START => $timeStart->getNormalized(),
            self::COL_DURATION => $duration,
            self::COL_TYPE => $type->getID(),
            self::COL_TICKET => '',
            self::COL_TICKET_URL => '',
            self::COL_COMMENTS => '',
            self::COL_USER_ID => AppFactory::createUser()->getID(),
        ));
    }

    public function createNewEntryByTime(Microtime $date, DaytimeStringInfo $timeStart, DaytimeStringInfo $timeEnd, TimeEntryType $type) : TimeEntry
    {
        return $this->createNewRecord(array(
            self::COL_DATE => $date->format(self::DATE_FORMAT),
            self::COL_TIME_START => $timeStart->getNormalized(),
            self::COL_TIME_END => $timeEnd->getNormalized(),
            self::COL_DURATION => $timeEnd->getTotalSeconds() - $timeStart->getTotalSeconds(),
            self::COL_TYPE => $type->getID(),
            self::COL_TICKET => '',
            self::COL_TICKET_URL => '',
            self::COL_COMMENTS => '',
            self::COL_USER_ID => AppFactory::createUser()->getID(),
        ));
    }
}
