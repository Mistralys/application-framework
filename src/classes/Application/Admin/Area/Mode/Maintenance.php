<?php

declare(strict_types=1);

abstract class Application_Admin_Area_Mode_Maintenance extends Application_Admin_Area_Mode
{
    public function getDefaultSubmode() : string
    {
        return 'list';
    }
    
    public function getNavigationTitle() : string
    {
        return t('Maintenance');
    }
    
    public function isUserAllowed() : bool
    {
        return $this->user->isDeveloper();
    }
    
    public function getURLName() : string
    {
        return 'maintenance';
    }
    
    public function getTitle() : string
    {
        return t('Planned maintenance');
    }
}
