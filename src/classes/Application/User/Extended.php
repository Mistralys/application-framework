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

        $this->registerNews($group);
        $this->registerMedia($group);

        // Give the developer all rights.
        $dev = $group->registerRight(self::RIGHT_DEVELOPER, t('Developer mode'))
            ->setDescription(t('Allows enabling the developer mode for developer-specific functionality'));

        $coreRights = array(
            self::RIGHT_LOGIN,
            self::RIGHT_TRANSLATE_UI,
            self::RIGHT_CREATE_NEWS,
            self::RIGHT_EDIT_NEWS,
            self::RIGHT_DELETE_NEWS,
            self::RIGHT_VIEW_NEWS,
            self::RIGHT_CREATE_NEWS_ALERTS,
            self::RIGHT_CREATE_MEDIA,
            self::RIGHT_EDIT_MEDIA,
            self::RIGHT_DELETE_MEDIA,
            self::RIGHT_VIEW_MEDIA,
        );

        foreach($coreRights as $coreRight)
        {
            $dev->grantRight($coreRight);
        }

        $groupIDs = array_keys($this->getRightGroups());

        foreach($groupIDs as $id)
        {
            $dev->grantGroupAll($id);
        }
    }

    protected function registerNews(Application_User_Rights_Group $group) : void
    {
        $group->registerRight(self::RIGHT_CREATE_NEWS, t('Create news'))
            ->setDescription(t('Create news entries.'));

        $group->registerRight(self::RIGHT_EDIT_NEWS, t('Edit news'))
            ->setDescription(t('Edit news entries.'));

        $group->registerRight(self::RIGHT_DELETE_NEWS, t('Delete news'))
            ->setDescription(t('Delete news entries.'));

        $group->registerRight(self::RIGHT_VIEW_NEWS, t('View news'))
            ->setDescription(t('View news entries.'));

        $group->registerRight(self::RIGHT_CREATE_NEWS_ALERTS, t('Create alerts'))
            ->setDescription(t('Create and modify news alerts.'));
    }

    protected function registerMedia(Application_User_Rights_Group $group) : void
    {
        $group->registerRight(self::RIGHT_CREATE_MEDIA, t('Add media'))
            ->setDescription(t('Add media files.'));

        $group->registerRight(self::RIGHT_EDIT_MEDIA, t('Edit media'))
            ->setDescription(t('Modify media files.'));

        $group->registerRight(self::RIGHT_DELETE_MEDIA, t('Delete media'))
            ->setDescription(t('Delete media files.'));

        $group->registerRight(self::RIGHT_VIEW_MEDIA, t('View media'))
            ->setDescription(t('View media files.'));
    }
}
