<?php

declare(strict_types=1);

namespace Application\Users\Rights;

interface UserAdminRightsInterface
{
    public const string RIGHT_VIEW_USERS = 'ViewUsers';
    public const string RIGHT_EDIT_USERS = 'EditUsers';
    public const string RIGHT_DELETE_USERS = 'DeleteUsers';
    public const string RIGHT_CREATE_USERS = 'CreateUsers';

    public function canViewUsers(): bool;
    public function canEditUsers(): bool;
    public function canDeleteUsers(): bool;
    public function canCreateUsers(): bool;
}
