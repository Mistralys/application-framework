<?php

declare(strict_types=1);

namespace Application\Renamer\Admin;

use Application_User;

class RenamerScreenRights
{
    public const string SCREEN_RENAMER_RESULTS = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_RENAMER_CONFIGURATION = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_RENAMER_EXPORT = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_RENAMER_SEARCH = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_RENAMER = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_RENAMER_REPLACE = Application_User::RIGHT_DEVELOPER;
}
