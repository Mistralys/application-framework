<?php
/**
 * @package Time Tracker
 * @subpackage Exports
 */

declare(strict_types=1);

namespace Application\TimeTracker\Export;

use Application;
use Application\AppFactory;
use Application\TimeTracker\TimeEntry;
use Application\TimeTracker\TimeTrackerCollection;
use Closure;
use Shuchkin\SimpleXLSXGen;

/**
 * Specialized exporter for time entries, that exports
 * available time entries into a spreadsheet.
 *
 * @package Time Tracker
 * @subpackage Exports
 */
class TimeExporter
{
    public const COL_DATE = 'Date';
    public const COL_START_TIME = 'Start Time';
    public const COL_END_TIME = 'End Time';
    public const COL_DURATION = 'Duration';
    public const COL_TYPE = 'Type';
    public const COL_TICKET = 'Ticket';
    public const COL_COMMENTS = 'Comments';

    public const COLUMNS = array(
        self::COL_DATE,
        self::COL_START_TIME,
        self::COL_END_TIME,
        self::COL_DURATION,
        self::COL_TYPE,
        self::COL_TICKET,
        self::COL_COMMENTS
    );

    private TimeTrackerCollection $timeTracker;

    /**
     * @var array<string, Closure>
     */
    private array $columnCallbacks;

    public function __construct()
    {
        $this->timeTracker = AppFactory::createTimeTracker();

        $this->columnCallbacks = array(
            self::COL_DATE => Closure::fromCallable(array($this, 'getValueDate')),
            self::COL_START_TIME => Closure::fromCallable(array($this, 'getValueStartTime')),
            self::COL_END_TIME => Closure::fromCallable(array($this, 'getValueEndTime')),
            self::COL_DURATION => Closure::fromCallable(array($this, 'getValueDuration')),
            self::COL_TYPE => Closure::fromCallable(array($this, 'getValueType')),
            self::COL_TICKET => Closure::fromCallable(array($this, 'getValueTicket')),
            self::COL_COMMENTS => Closure::fromCallable(array($this, 'getValueComments'))
        );
    }

    public function export() : SimpleXLSXGen
    {
        $lines = array();

        $lines[] = self::COLUMNS;

        foreach($this->timeTracker->getAll() as $entry)
        {
            $lines[] = $this->collectRow($entry);
        }

        return SimpleXLSXGen::fromArray($lines);
    }

    public function getOutputFileName() : string
    {
        return sprintf(
            'time_entries_%s',
            date('Ymd_His')
        );
    }

    /**
     * @return never
     */
    public function sendFile()
    {
        $this->export()->downloadAs($this->getOutputFileName().'.xlsx');

        Application::exit();
    }

    private function getColumnValue(TimeEntry $entry, string $column) : string
    {
        if(isset($this->columnCallbacks[$column]))
        {
            return $this->columnCallbacks[$column]($entry);
        }

        throw new TimeExportException(
            'Missing export callback for column.',
            sprintf(
                'To callback ios registered for the column [%s].',
                $column
            ),
            TimeExportException::ERROR_MISSING_COLUMN_VALUE_CALLBACK
        );
    }

    private function collectRow(TimeEntry $entry) : array
    {
        $result = array();

        foreach(self::COLUMNS as $column) {
            $result[] = $this->getColumnValue($entry, $column);
        }

        return $result;
    }

    // region: Value methods

    private function getValueDate(TimeEntry $entry) : string
    {
        return $entry->getDate()->format(TimeTrackerCollection::DATE_FORMAT);
    }

    private function getValueStartTime(TimeEntry $entry) : string
    {
        return $entry->getStartTime()->getNormalized();
    }

    private function getValueEndTime(TimeEntry $entry) : string
    {
        return $entry->getEndTime()->getNormalized();
    }

    private function getValueDuration(TimeEntry $entry) : string
    {
        return (string)$entry->getDuration()->getTotalSeconds();
    }

    private function getValueType(TimeEntry $entry) : string
    {
        return $entry->getTypeID();
    }

    private function getValueTicket(TimeEntry $entry) : string
    {
        return $entry->getTicketID();
    }

    private function getValueComments(TimeEntry $entry) : string
    {
        return $entry->getComments();
    }

    // endregion
}
