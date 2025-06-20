<?php

declare(strict_types=1);

namespace Application\TimeTracker\TimeSpans;

use Application;
use Application\TimeTracker\TimeSpans\SpanTypes\TimeSpanTypeInterface;
use Application\TimeTracker\TimeSpans\SpanTypes\TimeSpanTypes;
use Application_User;
use AppUtils\ConvertHelper;
use AppUtils\DateTimeHelper\DurationStringInfo;
use DateTime;
use DBHelper_BaseRecord;

class TimeSpanRecord extends DBHelper_BaseRecord
{
    public const COL_USER_ID = 'user_id';
    public const COL_DATE_START = 'date_start';
    public const COL_DATE_END = 'date_end';
    public const COL_COMMENTS = 'comments';
    public const COL_TYPE = 'type';
    public const COL_PROCESSED = 'processed';
    public const COL_DAYS = 'days';

    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue) : void
    {
    }

    public function getLabel(): string
    {
        $start = $this->getDateStart();
        $end = $this->getDateEnd();
        $label = sb()->t('%1$s:', $this->getType()->getLabel());

        if ($start->format('Y') !== $end->format('Y')) {
            $label
                ->add($this->getDateStart()->format('d.m.Y'))
                ->add(' - ')
                ->add($this->getDateEnd()->format('d.m.Y'));
        }
        else if($start->format('m') === $end->format('m'))
        {
            $label
                ->sf(
                    '%2$s-%3$s %1$s',
                    ConvertHelper::month2string($start->format('m')),
                    $start->format('d'),
                    $end->format('d')
                );
        }
        else {
            $label
                ->add($this->getDateStart()->format('d.m.'))
                ->add(' - ')
                ->add($this->getDateEnd()->format('d.m.'));
        }

        return (string)$label;
    }

    public function getLabelLinked() : string
    {
        return $this->getLabel();
    }

    public function getTypeID() : string
    {
        return $this->getRecordStringKey(self::COL_TYPE);
    }

    public function getType() : TimeSpanTypeInterface
    {
        return TimeSpanTypes::getInstance()->getByID($this->getTypeID());
    }

    public function getUserID() : int
    {
        return $this->getRecordIntKey(self::COL_USER_ID);
    }

    public function getUser() : Application_User
    {
        return Application::createUser($this->getUserID());
    }

    public function getDateStart() : DateTime
    {
        return $this->getRecordDateKey(self::COL_DATE_START);
    }

    public function getDateEnd() : DateTime
    {
        return $this->getRecordDateKey(self::COL_DATE_END);
    }

    public function getDuration() : DurationStringInfo
    {
        return DurationStringInfo::fromString($this->getDays().'d');
    }

    public function getDurationString() : string
    {
        return ConvertHelper::interval2string($this->getDuration()->getInterval()->getInterval());
    }

    public function getDays() : int
    {
        $start = $this->getDateStart();
        $end = $this->getDateEnd();
        $interval = $start->diff($end);
        $days = (int)$interval->format('%a');

        if($days < 1) {
            $days = 1; // Ensure at least one day
        }

        return $days;
    }

    public function getComments() : string
    {
        return $this->getRecordStringKey(self::COL_COMMENTS);
    }

    public function isProcessed() : bool
    {
        return $this->getRecordBooleanKey(self::COL_PROCESSED);
    }
}
