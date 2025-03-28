<?php
/**
 * @package Time Tracker
 * @subpackage Exceptions
 */

declare(strict_types=1);

namespace Application\TimeTracker\Export;

use Application\TimeTracker\TimeTrackerException;

/**
 * @package Time Tracker
 * @subpackage Exceptions
 */
class TimeExportException extends TimeTrackerException
{
    public const ERROR_MISSING_COLUMN_VALUE_CALLBACK = 176301;
    public const ERROR_PARSE_FILE_FAILED = 176302;
    public const ERROR_UNKNOWN_IMPORT_COLUMN = 176303;
}
