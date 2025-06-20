<?php
/**
 * @package Time Tracker
 * @subpackage Entries
 */

declare(strict_types=1);

namespace Application\TimeTracker;

use Application;
use Application\MarkdownRenderer;
use Application\TimeTracker\Admin\EntryAdminURLs;
use Application\TimeTracker\Types\TimeEntryType;
use Application\TimeTracker\Types\TimeEntryTypes;
use Application_User;
use AppUtils\ConvertHelper;
use AppUtils\DateTimeHelper\DaytimeStringInfo;
use AppUtils\DateTimeHelper\DurationStringInfo;
use AppUtils\Microtime;
use DBHelper_BaseRecord;
use function AppUtils\parseDurationString;

/**
 * @package Time Tracker
 * @subpackage Entries
 */
class TimeEntry extends DBHelper_BaseRecord
{
    public static function duration2hoursDec(DurationStringInfo $duration) : string
    {
        return sprintf(
            '%s h',
            $duration->getTotalHoursDec()
        );
    }

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

    public function getUserID() : int
    {
        return $this->getRecordIntKey(TimeTrackerCollection::COL_USER_ID);
    }

    public function getUser() : Application_User
    {
        return Application::createUser($this->getUserID());
    }

    public function getDate() : Microtime
    {
        return Microtime::createFromDate($this->getRecordDateKey(TimeTrackerCollection::COL_DATE));
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

    public function isProcessed() : bool
    {
        return $this->getRecordBooleanKey(TimeTrackerCollection::COL_PROCESSED);
    }

    public function setProcessed(bool $processed) : self
    {
        $this->setRecordBooleanKey(TimeTrackerCollection::COL_PROCESSED, $processed);
        return $this;
    }

    public function getTypeID() : string
    {
        return $this->getRecordStringKey(TimeTrackerCollection::COL_TYPE);
    }

    public function getType() : TimeEntryType
    {
        return TimeEntryTypes::getInstance()->getByID($this->getTypeID());
    }

    public function getTicketID() : string
    {
        return $this->getRecordStringKey(TimeTrackerCollection::COL_TICKET);
    }

    public function getTicketURL() : string
    {
        return $this->getRecordStringKey(TimeTrackerCollection::COL_TICKET_URL);
    }

    public function renderTicket() : string
    {
        $url = $this->getTicketURL();
        if(!empty($url)) {
            return (string)sb()->link($this->getTicketID(), $url, true);
        }

        return $this->getTicketID();
    }

    public function getComments() : string
    {
        return $this->getRecordStringKey(TimeTrackerCollection::COL_COMMENTS);
    }

    public function renderComments() : string
    {
        return MarkdownRenderer::create()->render($this->getComments());
    }

    public function getDayName() : string
    {
        $date = $this->getDate();
        return sprintf(
            '%02d. %s',
            $date->format('d'),
            ConvertHelper::date2dayName($date)
        );
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
