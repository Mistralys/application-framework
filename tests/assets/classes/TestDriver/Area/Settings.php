<?php

declare(strict_types=1);

class TestDriver_Area_Settings extends Application_Admin_Area_Settings
{
    public function getNavigationGroup() : string
    {
        return t('Manage');
    }
}
