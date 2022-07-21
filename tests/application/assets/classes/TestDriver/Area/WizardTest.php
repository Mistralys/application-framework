<?php

declare(strict_types=1);

class TestDriver_Area_WizardTest extends Application_Admin_Area
{
    public const URL_NAME = 'wizardtest';

    public function getDefaultMode() : string
    {
        return TestDriver_Area_WizardTest_Wizard::URL_NAME;
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
        return t('Test wizard');
    }

    public function getTitle() : string
    {
        return t('Test wizard');
    }

    public function getURLName() : string
    {
        return self::URL_NAME;
    }

    public function isAdminMode() : bool
    {
        return true;
    }
}