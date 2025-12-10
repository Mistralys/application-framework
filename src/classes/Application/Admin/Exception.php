<?php

declare(strict_types=1);

use Application\ApplicationException;

class Application_Admin_Exception extends ApplicationException
{
    public const int ERROR_SCREEN_SOURCE_NOT_FOUND = 188401;
    public const int ERROR_SCREEN_INDEX_NOT_FOUND = 188403;
    public const int ERROR_SCREEN_INDEX_INVALID = 188404;
}
