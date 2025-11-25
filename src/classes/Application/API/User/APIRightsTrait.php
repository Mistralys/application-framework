<?php
/**
 * @package API
 * @subpackage User
 */

declare(strict_types=1);

namespace Application\API\User;

use Application_User_Rights;
use Application_User_Rights_Group;

/**
 * Trait used to implement the rights for the API clients module.
 *
 * @package API
 * @subpackage User
 *
 * @see APIRightsInterface
 */
trait APIRightsTrait
{
    public function canEditAPIClients() : bool { return $this->can(APIRightsInterface::RIGHT_EDIT_API_CLIENTS); }
    public function canViewAPIClients() : bool { return $this->can(APIRightsInterface::RIGHT_VIEW_API_CLIENTS); }
    public function canDeleteAPIClients() : bool { return $this->can(APIRightsInterface::RIGHT_DELETE_API_CLIENTS); }
    public function canCreateAPIClients() : bool { return $this->can(APIRightsInterface::RIGHT_CREATE_API_CLIENTS); }

    protected function registerAPIClientsGroup(Application_User_Rights $manager) : void
    {
        $manager->registerGroup(
            APIRightsInterface::GROUP_API,
            t('API Clients Management'),
            $this->registerAPIClientRights(...)
        );
    }

    protected function registerAPIClientRights(Application_User_Rights_Group $group) : void
    {
        $group->registerRight(APIRightsInterface::RIGHT_VIEW_API_CLIENTS, t('View API clients'))
            ->actionView();

        $group->registerRight(APIRightsInterface::RIGHT_CREATE_API_CLIENTS, t('Create API clients'))
            ->actionCreate()
            ->grantRight(APIRightsInterface::RIGHT_VIEW_API_CLIENTS)
            ->grantRight(APIRightsInterface::RIGHT_EDIT_API_CLIENTS);

        $group->registerRight(APIRightsInterface::RIGHT_EDIT_API_CLIENTS, t('Edit API clients'))
            ->actionEdit()
            ->grantRight(APIRightsInterface::RIGHT_VIEW_API_CLIENTS);

        $group->registerRight(APIRightsInterface::RIGHT_DELETE_API_CLIENTS, t('Delete API clients'))
            ->actionDelete()
            ->grantRight(APIRightsInterface::RIGHT_CREATE_API_CLIENTS);
    }
}
