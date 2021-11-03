<?php

declare(strict_types=1);

class Application_Admin_Area_Welcome extends Application_Admin_Area
{
    const URL_NAME_WELCOME = 'welcome';

    public function getDefaultMode() : string
    {
        return Application_Admin_Area_Welcome_Overview::URL_NAME_OVERVIEW;
    }

    public function getNavigationGroup() : string
    {
        return '';
    }

    public function isUserAllowed() : bool
    {
        return true;
    }

    public function getDependencies() : array
    {
        return array();
    }

    public function isCore() : bool
    {
        return false;
    }

    public function getURLName() : string
    {
        return self::URL_NAME_WELCOME;
    }

    public function getNavigationTitle() : string
    {
        return '';
    }

    public function getTitle() : string
    {
        return t('Quickstart');
    }

    public function getNavigationIcon(): ?UI_Icon
    {
        return UI::icon()->home();
    }
}
