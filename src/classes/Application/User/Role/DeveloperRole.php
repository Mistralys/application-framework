<?php

declare(strict_types=1);

namespace Application\User\Role;

use Application\User\Roles\BaseRole;
use Application_User;

class DeveloperRole extends BaseRole
{
    public const string ROLE_ID = 'Developer';

    public function getID(): string
    {
        return self::ROLE_ID;
    }

    public function getLabel(): string
    {
        return t('Developer');
    }

    public function getRights(): array
    {
        return array(
            Application_User::RIGHT_LOGIN,
            Application_User::RIGHT_DEVELOPER
        );
    }
}
