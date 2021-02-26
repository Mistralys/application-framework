<?php

abstract class Application_Admin_Area_Devel_Errorlog extends Application_Admin_Area_Mode
{
    public function getURLName()
    {
        return 'errorlog';
    }
    
    public function getTitle()
    {
        return t('Error log');
    }
    
    public function getNavigationTitle()
    {
        return $this->getTitle();
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
