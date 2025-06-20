<?php

declare(strict_types=1);

namespace Application\TimeTracker\TimeSpans;

use Application;
use Application\TimeTracker\Admin\TimeSpansAdminURLs;
use DBHelper_BaseCollection;

/**
 * @method TimeSpanFilterCriteria getFilterCriteria()
 * @method TimeSpanFilterSettings getFilterSettings()
 * @method TimeSpanRecord[] getAll()
 * @method TimeSpanRecord[] getByID($record_id)
 */
class TimeSpanCollection extends DBHelper_BaseCollection
{
    public const TABLE_NAME = 'time_tracker_time_spans';
    public const PRIMARY_NAME = 'time_span_id';

    public function getRecordClassName(): string
    {
        return TimeSpanRecord::class;
    }

    public function getRecordFiltersClassName(): string
    {
        return TimeSpanFilterCriteria::class;
    }

    public function getRecordFilterSettingsClassName(): string
    {
        return TimeSpanFilterSettings::class;
    }

    public function getRecordDefaultSortKey(): string
    {
        return TimeSpanRecord::COL_DATE_START;
    }

    public function getRecordSearchableColumns(): array
    {
        return array(
            TimeSpanRecord::COL_COMMENTS => t('Comments')
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

    public function getRecordTypeName(): string
    {
        return 'time_span';
    }

    public function getCollectionLabel(): string
    {
        return t('Time Spans');
    }

    public function getRecordLabel(): string
    {
        return t('Time Span');
    }

    public function getRecordProperties(): array
    {
        return array();
    }

    private ?TimeSpansAdminURLs $adminURLs = null;

    public function adminURL() : TimeSpansAdminURLs
    {
        if(!isset($this->adminURLs)) {
            $this->adminURLs = new TimeSpansAdminURLs();
        }

        return $this->adminURLs;
    }

    protected function _registerKeys(): void
    {
        $this->keys->register(TimeSpanRecord::COL_USER_ID)
            ->setDefault((string)Application::getUser()->getID());

        $this->keys->register(TimeSpanRecord::COL_COMMENTS)
            ->setDefault('');
    }
}
