<?php

class Application_Admin_Area_Devel_Appsets extends Application_Admin_Area_Mode
{
    public function getURLName() : string
    {
        return 'appsets';
    }
    
    public function getTitle() : string
    {
        return t('Application interface sets');
    }
    
    public function getNavigationTitle() : string
    {
        return t('Appsets');
    }
    
    public function getDefaultSubmode() : string
    {
        return 'list';
    }
    
    public function isUserAllowed() : bool
    {
        return $this->user->isDeveloper();
    }
}