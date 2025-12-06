<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin\ListBuilder;

use Application\TimeTracker\TimeEntry;
use AppUtils\DateTimeHelper\DurationStringInfo;
use DateInterval;

class SummarizedTicket
{
    private int $totalSeconds = 0;
    private string $ticketID;
    private string $ticketURL = '';

    public function __construct(string $ticketID)
    {
        $this->ticketID = $ticketID;
    }

    public function getTicketID() : string
    {
        return $this->ticketID;
    }

    public function getTicketLinked() : string
    {
        if(!empty($this->ticketURL)) {
            return (string)sb()->link($this->ticketID, $this->ticketURL);
        }

        return $this->ticketID;
    }

    public function setTicketURL(string $url) : self
    {
        $this->ticketURL = $url;
        return $this;
    }

    public function getTotalDuration() : DateInterval
    {
        return new DateInterval('PT' . $this->totalSeconds . 'S');
    }

    public function getDurationString() : DurationStringInfo
    {
        return DurationStringInfo::fromSeconds($this->totalSeconds);
    }

    public function addTimeEntry(TimeEntry $entry) : self
    {
        $this->totalSeconds += $entry->getDuration()->getTotalSeconds();
        return $this;
    }
}
