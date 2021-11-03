<?php

abstract class Application_Admin_Area_Mode_Users_List extends Application_Admin_Area_Mode_Submode
{
   /**
    * @var Application_Admin_Area_Mode_Users
    */
    protected $mode;
    
    public function getNavigationTitle() : string
    {
        return t('List');
    }

    public function getDefaultAction() : string
    {
        return '';
    }

    public function getURLName() : string
    {
        return 'list';
    }

    public function getTitle() : string
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