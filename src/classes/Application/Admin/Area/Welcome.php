<?php

declare(strict_types=1);

class Application_Admin_Area_Welcome extends Application_Admin_Area
{
    const URL_NAME_WELCOME = 'welcome';

    public function getDefaultMode()
    {
        return Application_Admin_Area_Welcome_Overview::URL_NAME_OVERVIEW;
    }

    public function getNavigationGroup()
    {
        return '';
    }

    public function isUserAllowed()
    {
        return true;
    }

    public function getDependencies()
    {
        return array();
    }

    public function isCore()
    {
        return false;
    }

    public function getURLName()
    {
        return self::URL_NAME_WELCOME;
    }

    public function getNavigationTitle()
    {
        return '';
    }

    public function getTitle()
    {
        return t('Quickstart');
    }

    public function getNavigationIcon(): ?UI_Icon
    {
        return UI::icon()->home();
    }
}
