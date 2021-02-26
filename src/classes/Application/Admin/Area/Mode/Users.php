<?php

abstract class Application_Admin_Area_Mode_Users extends Application_Admin_Area_Mode
{
    /**
     * {@inheritDoc}
     * @see Application_Admin_Area_Mode::getDefaultSubmode()
     */
    public function getDefaultSubmode()
    {
        return 'list';
    }
    
    /**
     * {@inheritDoc}
     * @see Application_Admin_Area_Mode::getNavigationTitle()
     */
    public function getNavigationTitle()
    {
        return t('Users');
    }
    
    /**
     * {@inheritDoc}
     * @see Application_Admin_Area_Mode::isUserAllowed()
     */
    public function isUserAllowed()
    {
        return $this->user->isDeveloper();
    }
    
    /**
     * {@inheritDoc}
     * @see Application_Admin_Skeleton::getURLName()
     */
    public function getURLName()
    {
        return 'users';
    }
    
    /**
     * {@inheritDoc}
     * @see Application_Admin_Skeleton::getTitle()
     */
    public function getTitle()
    {
        return t('Users');
    }
}