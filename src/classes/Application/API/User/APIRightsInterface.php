<?php

declare(strict_types=1);

namespace Application\API\User;

interface APIRightsInterface
{
    public const string GROUP_API = 'API';

    public const string RIGHT_VIEW_API_CLIENTS = 'ViewAPIClients';
    public const string RIGHT_EDIT_API_CLIENTS = 'EditAPIClients';
    public const string RIGHT_DELETE_API_CLIENTS = 'DeleteAPIClients';
    public const string RIGHT_CREATE_API_CLIENTS = 'CreateAPIClients';
}
