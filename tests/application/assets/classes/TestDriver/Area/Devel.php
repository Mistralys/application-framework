<?php

declare(strict_types=1);

class TestDriver_Area_Devel extends Application_Admin_Area_Devel
{
    protected function initItems() : void
    {
        $this->registerErrorLog();
        $this->registerAppSettings();
        $this->registerAppInterface();
        $this->registerAppLogs();
        $this->registerWhatsNewEditor();
        $this->registerMaintenance();
        $this->registerRightsOverview();
        $this->registerUsers();
        $this->registerDeploymentRegistry();
        $this->registerNewsCentral();
    }
}
