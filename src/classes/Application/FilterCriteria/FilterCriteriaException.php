<?php

declare(strict_types=1);

namespace Application\FilterCriteria;

use Application_Exception;

class FilterCriteriaException extends Application_Exception
{

    public const int ERROR_MISSING_SELECT_KEYWORD = 710004;
    public const int ERROR_JOIN_ALREADY_REGISTERED = 710007;
    public const int ERROR_CANNOT_USE_WILDCARD_AND_DISTINCT = 710009;
    public const int ERROR_JOIN_ALREADY_ADDED = 710008;
    public const int ERROR_INVALID_WHERE_STATEMENT = 710001;
    public const int ERROR_CUSTOM_COLUMN_NOT_REGISTERED = 710005;
    public const int ERROR_JOIN_ID_NOT_FOUND = 710006;
    public const int ERROR_EMPTY_SELECT_FIELDS_LIST = 710002;
    public const int ERROR_MAX_BUILD_ITERATIONS_REACHED = 90502;
    public const int ERROR_MAX_REPLACE_ITERATIONS_REACHED = 90503;
    public const int ERROR_CANNOT_REGISTER_COLUMN_AGAIN = 90501;
}
