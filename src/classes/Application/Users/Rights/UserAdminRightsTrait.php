<?php

declare(strict_types=1);

namespace Application\Users\Rights;

use Application_User_Rights_Group;

/**
 * @see UserAdminRightsInterface
 */
trait UserAdminRightsTrait
{
    public function canViewUsers(): bool
    {
        return $this->can(UserAdminRightsInterface::RIGHT_VIEW_USERS);
    }

    public function canEditUsers(): bool
    {
        return $this->can(UserAdminRightsInterface::RIGHT_EDIT_USERS);
    }

    public function canDeleteUsers(): bool
    {
        return $this->can(UserAdminRightsInterface::RIGHT_DELETE_USERS);
    }

    public function canCreateUsers(): bool
    {
        return $this->can(UserAdminRightsInterface::RIGHT_CREATE_USERS);
    }

    protected function registerUserAdminRights(Application_User_Rights_Group $group) : void
    {
        $group->registerRight(UserAdminRightsInterface::RIGHT_DELETE_USERS, t('Delete users'))
            ->actionDelete()
            ->grantRight(UserAdminRightsInterface::RIGHT_CREATE_USERS);

        $group->registerRight(UserAdminRightsInterface::RIGHT_CREATE_USERS, t('Create users'))
            ->actionCreate()
            ->grantRight(UserAdminRightsInterface::RIGHT_EDIT_USERS);

        $group->registerRight(UserAdminRightsInterface::RIGHT_EDIT_USERS, t('Edit users'))
            ->actionEdit()
            ->grantRight(UserAdminRightsInterface::RIGHT_VIEW_USERS);

        $group->registerRight(UserAdminRightsInterface::RIGHT_VIEW_USERS, t('View users'))
            ->actionView();
    }
}
