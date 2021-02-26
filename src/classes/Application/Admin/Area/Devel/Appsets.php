<?php

require_once 'Application/Admin/Area/Mode.php';

class Application_Admin_Area_Devel_Appsets extends Application_Admin_Area_Mode
{
    public function getURLName()
    {
        return 'appsets';
    }
    
    public function getTitle()
    {
        return t('Application interface sets');
    }
    
    public function getNavigationTitle()
    {
        return t('Appsets');
    }
    
    public function getDefaultSubmode()
    {
        return 'list';
    }
    
    public function isUserAllowed()
    {
        return $this->user->isDeveloper();
    }
}