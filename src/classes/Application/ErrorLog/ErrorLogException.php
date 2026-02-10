<?php

declare(strict_types=1);

namespace Application\ErrorLog;

use Application\Exception\ApplicationException;

class ErrorLogException extends ApplicationException
{
    public const int ERROR_UNKNOWN_LOG = 42901;
    public const int ERROR_TEST_EXCEPTION = 42904;
    public const int ERROR_TEST_PREVIOUS_EXCEPTION = 42902;
}
