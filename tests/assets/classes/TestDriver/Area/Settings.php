<?php

require_once 'Application/Admin/Area/Settings.php';

class TestDriver_Area_Settings extends Application_Admin_Area_Settings
{
    public function getNavigationGroup()
    {
        return t('Manage');
    }
}