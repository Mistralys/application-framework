<?php

declare(strict_types=1);

class TestDriver_Area_Devel extends Application_Admin_Area_Devel
{
    protected function initItems()
    {
        $this->registerErrorLog();
        $this->registerAppSettings();
        $this->registerAppInterface();
        $this->registerAppLogs();
    }
}
