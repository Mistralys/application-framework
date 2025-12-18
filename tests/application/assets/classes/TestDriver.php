<?php
/**
 * @package TestDriver
 * @subpackage Driver
 */

declare(strict_types=1);

use Application\Admin\Welcome\Screens\WelcomeArea;
use Application\API\Admin\Screens\APIClientsArea;
use Application\ConfigSettings\BaseConfigRegistry;
use Application\Countries\Admin\Screens\CountriesArea;
use Application\Development\Admin\Screens\DevelArea;
use Application\Media\Admin\Screens\MediaLibraryArea;
use Application\NewsCentral\Admin\Screens\ManageNewsArea;
use Application\Tags\Admin\Screens\Area\TagsArea;
use Application\TimeTracker\Admin\Screens\BaseTimeTrackerArea;
use Application\Users\Admin\Screens\UserSettingsArea;
use TestDriver\Area\QuickNavScreen;
use TestDriver\Area\RevisionableScreen;
use TestDriver\Area\TestingScreen;
use TestDriver\Area\TimeTrackerScreen;
use TestDriver\Area\TranslationsScreen;
use TestDriver\Area\UsersArea;
use TestDriver\CustomIcon;
use TestDriver\UnitTestRedirectException;
use UI\AdminURLs\AdminURLInterface;

/**
 * @package TestDriver
 * @subpackage Driver
 */
class TestDriver extends Application_Driver
{
    public static function icon() : CustomIcon
    {
        return new CustomIcon();
    }

    /**
     * > NOTE: Uses the custom navigation generation,
     * > see {@see \TestDriver\UI\MainNavConfigurator}.
     *
     * @return array<string,string>
     */
    public function getAdminAreas() : array
    {
        return array(
            DevelArea::URL_NAME => DevelArea::class,
            WelcomeArea::URL_NAME => WelcomeArea::class,
            UserSettingsArea::URL_NAME => UserSettingsArea::class,
            Application_Admin_TranslationsArea::URL_NAME => TranslationsScreen::class,
            TestDriver_Area_WizardTest::URL_NAME => TestDriver_Area_WizardTest::class,
            QuickNavScreen::URL_NAME => QuickNavScreen::class,
            ManageNewsArea::URL_NAME => ManageNewsArea::class,
            MediaLibraryArea::URL_NAME => MediaLibraryArea::class,
            TestingScreen::URL_NAME => TestingScreen::class,
            RevisionableScreen::URL_NAME => RevisionableScreen::class,
            BaseTimeTrackerArea::URL_NAME => TimeTrackerScreen::class,
            CountriesArea::URL_NAME => CountriesArea::class,
            UsersArea::URL_NAME => UsersArea::class,
            APIClientsArea::URL_NAME => APIClientsArea::class,
            TagsArea::URL_NAME => TagsArea::class
        );
    }

    /**
     * Overridden to check if the test driver is running
     * in unit test mode: In this case, the redirect is
     * ignored to support testing admin screen classes.
     *
     * @param string|array|AdminURLInterface|NULL $paramsOrURL
     * @return never
     * @throws Application_Exception
     */
    public function redirectTo(string|array|AdminURLInterface|NULL $paramsOrURL = null) : never
    {
        if(BaseConfigRegistry::areUnitTestsRunning())
        {
            throw new UnitTestRedirectException();
        }

        parent::redirectTo($paramsOrURL);
    }

    protected function setUpUI() : void
    {
        $this->configureAdminUIFramework();
    }

    public function getAppName() : string
    {
        return 'AppFramework TestSuite';
    }

    public function getAppNameShort() : string
    {
        return 'AppTestSuite';
    }

    public function getRevisionableTypes() : array
    {
        return array();
    }

    /**
     * @param UI_Page $page
     * @return array<string,string|number>
     */
    public function getPageParams(UI_Page $page) : array
    {
        return array();
    }
}
