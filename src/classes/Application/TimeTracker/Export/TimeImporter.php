<?php
/**
 * @package Time Tracker
 * @subpackage Exports
 */

declare(strict_types=1);

namespace Application\TimeTracker\Export;

use Application\AppFactory;
use Application\TimeTracker\TimeTrackerCollection;
use Application_User;
use AppUtils\DateTimeHelper\DaytimeStringInfo;
use AppUtils\DateTimeHelper\DurationStringInfo;
use AppUtils\FileHelper\FileInfo;
use AppUtils\Microtime;
use Closure;
use DBHelper;
use Shuchkin\SimpleXLSX;

/**
 * This can import time entries from a spreadsheet file that
 * was previously exported using the exporter {@see TimeExporter}.
 *
 * @package Time Tracker
 * @subpackage Exports
 */
class TimeImporter
{
    public const string KEY_DB_COLUMN = 'dbColumn';
    public const string KEY_PARSE_CALLBACK = 'parseCallback';
    private FileInfo $file;
    private int $importedRows = 0;
    private TimeTrackerCollection $timeTracker;
    private array $columns = array();
    private int $userID;

    public function __construct(FileInfo $importFile, Application_User $user)
    {
        $this->file = $importFile;
        $this->userID = $user->getID();
        $this->timeTracker = AppFactory::createTimeTracker();

        $this->registerColumn(TimeExporter::COL_DATE, TimeTrackerCollection::COL_DATE, Closure::fromCallable(array($this, 'parseDate')));
        $this->registerColumn(TimeExporter::COL_START_TIME, TimeTrackerCollection::COL_TIME_START, Closure::fromCallable(array($this, 'parseTimeStart')));
        $this->registerColumn(TimeExporter::COL_END_TIME, TimeTrackerCollection::COL_TIME_END, Closure::fromCallable(array($this, 'parseTimeEnd')));
        $this->registerColumn(TimeExporter::COL_DURATION, TimeTrackerCollection::COL_DURATION, Closure::fromCallable(array($this, 'parseDuration')));
        $this->registerColumn(TimeExporter::COL_TYPE, TimeTrackerCollection::COL_TYPE, Closure::fromCallable(array($this, 'parseType')));
        $this->registerColumn(TimeExporter::COL_TICKET, TimeTrackerCollection::COL_TICKET, Closure::fromCallable(array($this, 'parseTicket')));
        $this->registerColumn(TimeExporter::COL_COMMENTS, TimeTrackerCollection::COL_COMMENTS, Closure::fromCallable(array($this, 'parseComments')));
    }

    // region: Value parse callbacks

    private function parseDate(string $value) : string
    {
        return Microtime::createFromString($value)->format(TimeTrackerCollection::DATE_FORMAT);
    }

    private function parseTimeStart(string $value) : string
    {
        return DaytimeStringInfo::fromString($value)->getNormalized();
    }

    private function parseTimeEnd(string $value) : string
    {
        return DaytimeStringInfo::fromString($value)->getNormalized();
    }

    private function parseDuration(string $value) : string
    {
        return (string)DurationStringInfo::fromSeconds((int)$value)->getTotalSeconds();
    }

    private function parseType(string $value) : string
    {
        return $value;
    }

    private function parseTicket(string $value) : string
    {
        return $value;
    }

    private function parseComments(string $value) : string
    {
        return $value;
    }

    // endregion

    private function registerColumn(string $exportColumn, string $dbColumn, Closure $parseCallback) : void
    {
        $this->columns[$exportColumn] = array(
            self::KEY_DB_COLUMN => $dbColumn,
            self::KEY_PARSE_CALLBACK => $parseCallback
        );
    }

    public function import() : self
    {
        DBHelper::requireTransaction('Import time entries');

        $xlsx = SimpleXLSX::parse($this->file->getPath());

        if( $xlsx === false ) {
            throw new TimeExportException(
                'Unable to parse the file.',
                sprintf('The parser said: [%s]', SimpleXLSX::parseError()),
                TimeExportException::ERROR_PARSE_FILE_FAILED
            );
        }

        $rows = $xlsx->rows();

        // Remove the header row
        array_shift($rows);

        foreach($rows as $row) {
            $this->importRow($row);
        }

        return $this;
    }

    /**
     * @param array<int,string> $row
     * @return void
     */
    private function importRow(array $row) : void
    {
        $data = array(
            TimeTrackerCollection::COL_USER_ID => $this->userID
        );

        $index = 0;
        foreach(TimeExporter::COLUMNS as $column) {
            $data[$this->getColumnName($column)] = $this->parseColumn($column, $row[$index]);
            $index++;
        }

        $this->timeTracker->createNewRecord($data);

        $this->importedRows++;
    }

    private function getColumnName(string $exportColumn) : string
    {
        $def = $this->getColumnDef($exportColumn);
        return $def[self::KEY_DB_COLUMN];
    }

    /**
     * @param string $exportColumn
     * @param string|int|float|bool $value
     * @return string
     * @throws TimeExportException
     */
    private function parseColumn(string $exportColumn, $value) : string
    {
        $def = $this->getColumnDef($exportColumn);
        return $def[self::KEY_PARSE_CALLBACK]((string)$value);
    }

    /**
     * @param string $name
     * @return array{dbColumn: string, parseCallback: Closure}
     */
    private function getColumnDef(string $name) : array
    {
        if(isset($this->columns[$name])) {
            return $this->columns[$name];
        }

        throw new TimeExportException(
            'Unknown import column',
            sprintf(
                'The column [%s] is not registered.',
                $name
            ),
            TimeExportException::ERROR_UNKNOWN_IMPORT_COLUMN
        );
    }

    public function countImportedRows() : int
    {
        return $this->importedRows;
    }
}
