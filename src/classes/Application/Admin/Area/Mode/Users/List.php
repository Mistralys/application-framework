<?php

abstract class Application_Admin_Area_Mode_Users_List extends Application_Admin_Area_Mode_Submode
{
   /**
    * @var Application_Admin_Area_Mode_Users
    */
    protected $mode;
    
    /**
     * {@inheritDoc}
     * @see Application_Admin_Area_Mode_Submode::getNavigationTitle()
     */
    public function getNavigationTitle()
    {
        return t('List');
    }

    /**
     * {@inheritDoc}
     * @see Application_Admin_Area_Mode_Submode::getDefaultAction()
     */
    public function getDefaultAction()
    {
        return null;
    }

    /**
     * {@inheritDoc}
     * @see Application_Admin_Skeleton::getURLName()
     */
    public function getURLName()
    {
        return 'list';
    }

    /**
     * {@inheritDoc}
     * @see Application_Admin_Skeleton::getTitle()
     */
    public function getTitle()
    {
        return t('Users list');
    }

    protected function _renderContent()
    {
        return $this->renderer
            ->setTitle($this->getTitle())
            ->makeWithSidebar();
    }
}