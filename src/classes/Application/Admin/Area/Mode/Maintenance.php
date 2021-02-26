<?php

require_once 'Application/Admin/Area/Mode.php';

abstract class Application_Admin_Area_Mode_Maintenance extends Application_Admin_Area_Mode
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
        return t('Maintenance');
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
        return 'maintenance';
    }
    
    /**
     * {@inheritDoc}
     * @see Application_Admin_Skeleton::getTitle()
     */
    public function getTitle()
    {
        return t('Planned maintenance');
    }
}