<?php
/**
 * @package TestDriver
 * @subpackage Administration
 */

declare(strict_types=1);

namespace TestDriver\UI;

use Application\Admin\Area\BaseMediaLibraryScreen;
use Application\Admin\Area\BaseNewsScreen;
use Application\API\Admin\Screens\BaseAPIClientsArea;
use Application\Area\BaseTagsScreen;
use Application\TimeTracker\Admin\Screens\BaseTimeTrackerArea;
use Application\Users\Admin\Screens\BaseUsersArea;
use Application_Admin_Area_Devel;
use Application_Admin_Area_Devel_Appinterface;
use Application_Admin_Area_Settings;
use Application_Admin_Area_Welcome;
use Application_Admin_TranslationsArea;
use TestDriver\Area\CountriesScreen;
use TestDriver\Area\QuickNavScreen;
use TestDriver\Area\RevisionableScreen;
use TestDriver\Area\TestingScreen;
use TestDriver_Area_WizardTest;
use UI\Page\Navigation\NavConfigurator;
use UI\Page\Navigation\NavConfigurator\MenuConfigurator;

/**
 * @package TestDriver
 * @subpackage Administration
 */
class MainNavConfigurator extends NavConfigurator
{
    public function configure() : void
    {
        $this->addArea(Application_Admin_Area_Welcome::URL_NAME_WELCOME, true);

        $this->configureReferencesMenu($this->addMenu(t('References')));
        $this->configureManageMenu($this->addMenu(t('Manage')));
    }

    private function configureManageMenu(MenuConfigurator $menu) : void
    {
        $menu
            ->addAreaChained(Application_Admin_Area_Settings::URL_NAME)
            ->addSeparator()
            ->addAreaChained(CountriesScreen::URL_NAME)
            ->addAreaChained(BaseNewsScreen::URL_NAME)
            ->addAreaChained(BaseMediaLibraryScreen::URL_NAME)
            ->addAreaChained(RevisionableScreen::URL_NAME)
            ->addAreaChained(BaseTagsScreen::URL_NAME)
            ->addAreaChained(BaseTimeTrackerArea::URL_NAME)
            ->addAreaChained(BaseAPIClientsArea::URL_NAME)
            ->addAreaChained(BaseUsersArea::URL_NAME)
            ->addSeparator()
            ->addAreaChained(Application_Admin_Area_Devel::URL_NAME)
            ->addAreaChained(TestingScreen::URL_NAME)
            ->addAreaChained(Application_Admin_TranslationsArea::URL_NAME);
    }

    private function configureReferencesMenu(MenuConfigurator $menu) : void
    {
        $menu
            ->setAutoActivate(false)
            ->addAreaChained(QuickNavScreen::URL_NAME)
            ->addPathChained(
                Application_Admin_Area_Devel::URL_NAME,
                Application_Admin_Area_Devel_Appinterface::URL_NAME
            )
            ->addArea(TestDriver_Area_WizardTest::URL_NAME);
    }
}
