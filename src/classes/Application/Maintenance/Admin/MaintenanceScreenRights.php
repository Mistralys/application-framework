<?php

declare(strict_types=1);

namespace Application\Maintenance\Admin;

use Application_User;

class MaintenanceScreenRights
{
    public const string SCREEN_MAIN = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_CREATE = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_LIST = Application_User::RIGHT_DEVELOPER;
}
