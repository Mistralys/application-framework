<?php

declare(strict_types=1);

namespace Application\ErrorLog\Admin;

use Application_User;

class ErrorLogScreenRights
{
    public const string SCREEN_MAIN = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_LIST = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_VIEW = Application_User::RIGHT_DEVELOPER;
}
