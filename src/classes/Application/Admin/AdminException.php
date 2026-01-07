<?php

declare(strict_types=1);

use Application\Exception\ApplicationException;

class AdminException extends ApplicationException
{
    public const int ERROR_SCREEN_SOURCE_NOT_FOUND = 188401;
    public const int ERROR_SCREEN_INDEX_INVALID = 188404;
    public const int ERROR_SCREEN_SUBSCREEN_NOT_FOUND = 188405;
    public const int ERROR_INVALID_APP_SOURCE_FOLDER = 188406;
    public const int ERROR_ADMIN_AREA_NOT_FOUND = 188407;
}
