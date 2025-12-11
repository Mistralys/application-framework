<?php

declare(strict_types=1);

namespace Application\Development\Admin;

use Application_User;

class DevScreenRights
{
    public const string SCREEN_RIGHTS_OVERVIEW = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_SITEMAP = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_DEVEL = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_APP_INTERFACE = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_OVERVIEW = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_APP_SETTINGS = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_MESSAGE_LOG = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_APP_CONFIG = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_CACHE_CONTROL = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_CSS_GENERATOR = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_DEPLOYMENT_HISTORY = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_DATABASE_DUMPS = Application_User::RIGHT_DEVELOPER;
}
