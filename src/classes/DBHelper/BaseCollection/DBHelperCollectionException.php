<?php

declare(strict_types=1);

namespace DBHelper\BaseCollection;

use DBHelper_Exception;

class DBHelperCollectionException extends DBHelper_Exception
{
    public const int ERROR_NO_PARENT_RECORD_BOUND = 16505;
    public const int ERROR_IDTABLE_SAME_TABLE_NAME = 16501;
    public const int ERROR_CANNOT_START_TWICE = 16506;
    public const int ERROR_CANNOT_DELETE_OTHER_COLLECTION_RECORD = 16507;
    public const int ERROR_FILTER_SETTINGS_CLASS_NOT_FOUND = 16512;
    public const int ERROR_FILTER_CRITERIA_CLASS_NOT_FOUND = 16511;
    public const int ERROR_MISSING_REQUIRED_KEYS = 16510;
    public const int ERROR_COLLECTION_ALREADY_HAS_PARENT = 16504;
    public const int ERROR_CREATE_RECORD_CANCELLED = 16509;
}
