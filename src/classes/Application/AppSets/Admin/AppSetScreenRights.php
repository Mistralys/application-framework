<?php

declare(strict_types=1);

namespace Application\Sets\Admin;

use Application_User;

class AppSetScreenRights
{
    public const string SCREEN_APP_SETS = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_APP_SETS_CREATE = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_DELETE_SET = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_EDIT_SET = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_LIST = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_VIEW_STATUS = Application_User::RIGHT_DEVELOPER;
    public const string SCREEN_VIEW = Application_User::RIGHT_DEVELOPER;
}
