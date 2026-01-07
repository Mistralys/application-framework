<?php

declare(strict_types=1);

namespace Application\Driver;

use Application\Exception\ApplicationException;

class DriverException extends ApplicationException
{
    public const int ERROR_UNKNOWN_ADMIN_AREA_CLASS = 188701;
}
