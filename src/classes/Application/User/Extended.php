<?php

declare(strict_types=1);

abstract class Application_User_Extended extends Application_User
{
    /**
     * Retrieves group id=>label pairs.
     * @return array<string,string>
     */
    abstract public function getRightGroups() : array;

    private static ?Application_User_Rights $rightsManager = null;

    public function getRightsManager() : Application_User_Rights
    {
        return $this->initRightsManager();
    }

    private function initRightsManager() : Application_User_Rights
    {
        if(isset(self::$rightsManager))
        {
            return self::$rightsManager;
        }

        self::$rightsManager = new Application_User_Rights();

        $groupIDs = $this->getRightGroups();

        // Add the core rights group.
        // !Must be at the end!
        $groupIDs[self::RIGHTS_CORE] = t('System core');

        foreach ($groupIDs as $groupID => $label)
        {
            $this->registerGroupRights(self::$rightsManager->registerGroup($groupID, $label));
        }

        $this->registerRoles();

        return self::$rightsManager;
    }

    abstract protected function registerRoles() : void;

    protected function getRoleDefs()
    {
        return $this->getRightsManager()->toArray();
    }

    protected function registerGroupRights(Application_User_Rights_Group $group) : void
    {
        $method = 'registerRights_'.$group->getID();

        if(!method_exists($this, $method))
        {
            throw new Application_Exception(
                'Unknown user right group',
                sprintf('The right group method [%s] is not present.', $method),
                self::ERROR_RIGHT_GROUP_METHOD_MISSING
            );
        }

        $this->$method($group);
    }

    protected function registerRights_system_core(Application_User_Rights_Group $group) : void
    {
        $group->setDescription(t('Application framework core rights.'));

        $group->registerRight(self::RIGHT_LOGIN, t('Log in'))
            ->setDescription(t('Logging into the application.'));

        $group->registerRight(self::RIGHT_TRANSLATE_UI, t('Translate UI'))
            ->setDescription(t('Handle translations of the user interface.'));

        $groupIDs = array_keys($this->getRightGroups());

        $dev = $group->registerRight(self::RIGHT_DEVELOPER, t('Developer mode'))
            ->setDescription(t('Allows enabling the developer mode for developer-specific functionality'));

        $dev->grantRight(self::RIGHT_LOGIN);
        $dev->grantRight(self::RIGHT_TRANSLATE_UI);

        foreach($groupIDs as $id)
        {
            $dev->grantGroupAll($id);
        }
    }
}
