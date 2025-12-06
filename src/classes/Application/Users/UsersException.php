<?php

declare(strict_types=1);

namespace Application\Users;

use Application_Exception;

class UsersException extends Application_Exception
{
    public const int ERROR_MISSING_EMAIL_FOR_HASH = 185801;
}
