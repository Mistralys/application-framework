<?php

declare(strict_types=1);

namespace DBHelper\BaseRecord;

use DBHelper_Exception;

class BaseRecordException extends DBHelper_Exception
{
    public const int ERROR_CANNOT_GENERATE_KEY_VALUE = 87601;
    public const int ERROR_RECORD_KEY_INVALID_MICROTIME = 87602;
}
