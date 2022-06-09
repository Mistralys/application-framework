<?php

declare(strict_types=1);

namespace TestDriver\UI;

use Application_Admin_Area_Devel;
use Application_Admin_Area_Devel_Appinterface;
use Application_Admin_Area_Settings;
use TestDriver\Area\Welcome;
use TestDriver_Area_Devel_Appinterface;
use TestDriver_Area_QuickNav;
use TestDriver_Area_WizardTest;
use UI\Page\Navigation\NavConfigurator;
use UI\Page\Navigation\NavConfigurator\MenuConfigurator;

class MainNavigation extends NavConfigurator
{
    public function configure() : void
    {
        $this->addArea(Welcome::URL_NAME_WELCOME, true);
        $this->configureManageMenu($this->addMenu(t('Manage')));
        $this->configureReferencesMenu($this->addMenu(t('References')));
    }

    private function configureManageMenu(MenuConfigurator $menu) : void
    {
        $menu->addArea(Application_Admin_Area_Settings::URL_NAME);
        $menu->addSeparator();
        $menu->addArea(Application_Admin_Area_Devel::URL_NAME);
    }

    private function configureReferencesMenu(MenuConfigurator $menu) : void
    {
        $menu->addArea(TestDriver_Area_QuickNav::URL_NAME);

        $menu->addPath(
            Application_Admin_Area_Devel::URL_NAME,
            Application_Admin_Area_Devel_Appinterface::URL_NAME
        );

        $menu->addArea(TestDriver_Area_WizardTest::URL_NAME);
    }
}
