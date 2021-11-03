<?php

abstract class Application_Admin_Area_Devel_Errorlog extends Application_Admin_Area_Mode
{
    public function getURLName() : string
    {
        return 'errorlog';
    }
    
    public function getTitle() : string
    {
        return t('Error log');
    }
    
    public function getNavigationTitle() : string
    {
        return $this->getTitle();
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
