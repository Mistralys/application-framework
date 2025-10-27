<?php

declare(strict_types=1);

namespace Application\API\Admin;

use Application\API\User\APIRightsInterface;

class APIScreenRights
{
    public const string SCREEN_CLIENTS_AREA = APIRightsInterface::RIGHT_VIEW_API_CLIENTS;
    public const string SCREEN_CLIENTS_LIST = APIRightsInterface::RIGHT_VIEW_API_CLIENTS;
    public const string SCREEN_CLIENTS_LIST_MULTI_DELETE = APIRightsInterface::RIGHT_DELETE_API_CLIENTS;
    public const string SCREEN_CLIENTS_CREATE = APIRightsInterface::RIGHT_CREATE_API_CLIENTS;
    public const string SCREEN_CLIENTS_VIEW = APIRightsInterface::RIGHT_VIEW_API_CLIENTS;
    public const string SCREEN_CLIENTS_VIEW_STATUS = APIRightsInterface::RIGHT_VIEW_API_CLIENTS;
    public const string SCREEN_CLIENTS_SETTINGS = APIRightsInterface::RIGHT_VIEW_API_CLIENTS;
    public const string SCREEN_CLIENTS_SETTINGS_EDIT = APIRightsInterface::RIGHT_EDIT_API_CLIENTS;
    public const string SCREEN_API_KEYS = APIRightsInterface::RIGHT_VIEW_API_CLIENTS;
    public const string SCREEN_API_KEYS_CREATE = APIRightsInterface::RIGHT_CREATE_API_CLIENTS;
    public const string SCREEN_API_KEYS_STATUS = APIRightsInterface::RIGHT_VIEW_API_CLIENTS;
}
