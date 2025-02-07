<?php

declare(strict_types=1);

namespace Application\TimeTracker;

use Application_Exception;

class TimeTrackerException extends Application_Exception
{
    public const ERROR_INVALID_DURATION_DATA_SUBMITTED = 172001;
}
