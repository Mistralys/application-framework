<?php
/**
 * @package TestDriver
 * @subpackage Administration
 */

declare(strict_types=1);

namespace TestDriver\UI;

use Application\Admin\Area\BaseNewsScreen;
use Application\Admin\Welcome\Screens\WelcomeArea;
use Application\API\Admin\Screens\BaseAPIClientsArea;
use Application\Area\BaseTagsScreen;
use Application\Development\Admin\Screens\DevelArea;
use Application\Media\Admin\Screens\MediaLibraryArea;
use Application\TimeTracker\Admin\Screens\BaseTimeTrackerArea;
use Application\Users\Admin\Screens\BaseUsersArea;
use Application\Users\Admin\Screens\UserSettingsArea;
use Application_Admin_TranslationsArea;
use TestDriver\Area\CountriesScreen;
use TestDriver\Area\QuickNavScreen;
use TestDriver\Area\RevisionableScreen;
use TestDriver\Area\TestingScreen;
use TestDriver_Area_WizardTest;
use UI\Admin\Screens\AppInterfaceDevelMode;
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
        $this->addArea(WelcomeArea::URL_NAME, true);

        $this->configureReferencesMenu($this->addMenu(t('References')));
        $this->configureManageMenu($this->addMenu(t('Manage')));
    }

    private function configureManageMenu(MenuConfigurator $menu) : void
    {
        $menu
            ->addAreaChained(UserSettingsArea::URL_NAME)
            ->addSeparator()
            ->addAreaChained(CountriesScreen::URL_NAME)
            ->addAreaChained(BaseNewsScreen::URL_NAME)
            ->addAreaChained(MediaLibraryArea::URL_NAME)
            ->addAreaChained(RevisionableScreen::URL_NAME)
            ->addAreaChained(BaseTagsScreen::URL_NAME)
            ->addAreaChained(BaseTimeTrackerArea::URL_NAME)
            ->addAreaChained(BaseAPIClientsArea::URL_NAME)
            ->addAreaChained(BaseUsersArea::URL_NAME)
            ->addSeparator()
            ->addAreaChained(DevelArea::URL_NAME)
            ->addAreaChained(TestingScreen::URL_NAME)
            ->addAreaChained(Application_Admin_TranslationsArea::URL_NAME);
    }

    private function configureReferencesMenu(MenuConfigurator $menu) : void
    {
        $menu
            ->setAutoActivate(false)
            ->addAreaChained(QuickNavScreen::URL_NAME)
            ->addPathChained(
                DevelArea::URL_NAME,
                AppInterfaceDevelMode::URL_NAME
            )
            ->addArea(TestDriver_Area_WizardTest::URL_NAME);
    }
}
