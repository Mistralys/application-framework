<?php

abstract class Application_Admin_Area_Mode_Users extends Application_Admin_Area_Mode
{
    public function getDefaultSubmode() : string
    {
        return 'list';
    }
    
    public function getNavigationTitle() : string
    {
        return t('Users');
    }
    
    public function isUserAllowed() : bool
    {
        return $this->user->isDeveloper();
    }
    
    public function getURLName() : string
    {
        return 'users';
    }
    
    public function getTitle() : string
    {
        return t('Users');
    }
}