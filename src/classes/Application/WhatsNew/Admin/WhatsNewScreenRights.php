<?php

declare(strict_types=1);

namespace Application\WhatsNew\Admin;

use Application_User;

class WhatsNewScreenRights
{
    public const string SCREEN_MAIN = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_CREATE = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_EDIT = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_LIST = Application_User::RIGHT_DEVELOPER;
}
