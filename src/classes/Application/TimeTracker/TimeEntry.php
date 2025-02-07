<?php

declare(strict_types=1);

namespace Application\TimeTracker;

use Application\MarkdownRenderer;
use Application\TimeTracker\Admin\EntryAdminURLs;
use Application\TimeTracker\Types\TimeEntryType;
use Application\TimeTracker\Types\TimeEntryTypes;
use AppUtils\ConvertHelper;
use AppUtils\DateTimeHelper\DaytimeStringInfo;
use AppUtils\DateTimeHelper\DurationStringInfo;
use DateTime;
use DBHelper_BaseRecord;
use function AppUtils\parseDurationString;

class TimeEntry extends DBHelper_BaseRecord
{
    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue)
    {
    }

    public function getLabel(): string
    {
        $label = sb()->add(ConvertHelper::date2listLabel($this->getDate()));

        $start = $this->getStartTime();
        $end = $this->getEndTime();

        if(!$start->isEmpty() && !$end->isEmpty()) {
            $label->add($start->toReadable())->add('-')->add($end->toReadable());
        }

        return (string)$label;
    }

    public function getDate() : DateTime
    {
        return $this->getRecordDateKey(TimeTrackerCollection::COL_DATE);
    }

    public function getStartTime() : DaytimeStringInfo
    {
        return DaytimeStringInfo::fromString($this->getRecordStringKey(TimeTrackerCollection::COL_TIME_START));
    }

    public function getEndTime() : DaytimeStringInfo
    {
        return DaytimeStringInfo::fromString($this->getRecordStringKey(TimeTrackerCollection::COL_TIME_END));
    }

    public function getDuration() : DurationStringInfo
    {
        return parseDurationString($this->getRecordIntKey(TimeTrackerCollection::COL_DURATION));
    }

    public function getTypeID() : string
    {
        return $this->getRecordStringKey(TimeTrackerCollection::COL_TYPE);
    }

    public function getType() : TimeEntryType
    {
        return TimeEntryTypes::getInstance()->getByID($this->getTypeID());
    }

    public function getTicket() : string
    {
        return $this->getRecordStringKey(TimeTrackerCollection::COL_TICKET);
    }

    public function renderTicket() : string
    {
        return MarkdownRenderer::create()->render($this->getTicket());
    }

    public function getComments() : string
    {
        return $this->getRecordStringKey(TimeTrackerCollection::COL_COMMENTS);
    }

    public function renderComments() : string
    {
        return MarkdownRenderer::create()->render($this->getComments());
    }

    private ?EntryAdminURLs $adminURLs = null;

    public function adminURL() : EntryAdminURLs
    {
        if(!isset($this->adminURLs)) {
            $this->adminURLs = new EntryAdminURLs($this);
        }

        return $this->adminURLs;
    }
}
