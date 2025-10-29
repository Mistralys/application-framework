<?php

declare(strict_types=1);

namespace Application\Users\Admin;

use Application\Users\Rights\UserAdminRightsInterface;

class UserAdminScreenRights
{
    public const string SCREEN_AREA = UserAdminRightsInterface::RIGHT_VIEW_USERS;
    public const string SCREEN_LIST = UserAdminRightsInterface::RIGHT_VIEW_USERS;
    public const string SCREEN_CREATE = UserAdminRightsInterface::RIGHT_CREATE_USERS;
    public const string SCREEN_VIEW = UserAdminRightsInterface::RIGHT_VIEW_USERS;
    public const string SCREEN_VIEW_STATUS = UserAdminRightsInterface::RIGHT_VIEW_USERS;
    public const string SCREEN_LIST_CREATE = self::SCREEN_CREATE;
    public const string SCREEN_VIEW_SETTINGS = UserAdminRightsInterface::RIGHT_EDIT_USERS;
}
