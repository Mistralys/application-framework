<?php

declare(strict_types=1);

class TestDriver_Area_WizardTest extends Application_Admin_Area
{

    public function getDefaultMode() : string
    {
        return 'wizard';
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
        return true;
    }

    public function getNavigationTitle() : string
    {
        return '';
    }

    public function getTitle() : string
    {
        return '';
    }

    public function getURLName() : string
    {
        return 'wizardtest';
    }

    public function isAdminMode() : bool
    {
        return true;
    }
}