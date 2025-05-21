<?php

declare(strict_types=1);

abstract class Application_Admin_Area_Devel_Appsets extends Application_Admin_Area_Mode
{
    public const URL_NAME = 'appsets';

    public function getURLName() : string
    {
        return self::URL_NAME;
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