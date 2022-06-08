<?php

declare(strict_types=1);

use Application\Admin\Area\Mode\Users\UsersListSubmode;

abstract class Application_Admin_Area_Mode_Users extends Application_Admin_Area_Mode
{
    public const URL_NAME = 'users';

    public function getDefaultSubmode() : string
    {
        return UsersListSubmode::URL_NAME;
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
        return self::URL_NAME;
    }
    
    public function getTitle() : string
    {
        return t('Users management');
    }

    public function getNavigationIcon() : ?UI_Icon
    {
        return UI::icon()->users();
    }
}
