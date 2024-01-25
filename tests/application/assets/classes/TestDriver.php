<?php
/**
 * @package TestDriver
 * @subpackage Driver
 */

declare(strict_types=1);

use TestDriver\Area\MediaLibraryScreen;
use TestDriver\Area\NewsScreen;
use TestDriver\Area\QuickNavScreen;
use TestDriver\Area\RevisionableScreen;
use TestDriver\Area\TestingScreen;
use TestDriver\Area\TranslationsScreen;
use TestDriver\Area\WelcomeScreen;
use TestDriver\CustomIcon;

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
     * NOTE: Uses the custom navigation generation,
     * see {@see \TestDriver\UI\MainNavConfigurator}.
     *
     * @return array<string,string>
     */
    public function getAdminAreas() : array
    {
        return array(
            Application_Admin_Area_Welcome::URL_NAME_WELCOME => getClassTypeName(WelcomeScreen::class),
            Application_Admin_Area_Devel::URL_NAME => getClassTypeName(TestDriver_Area_Devel::class),
            Application_Admin_Area_Settings::URL_NAME => getClassTypeName(TestDriver_Area_Settings::class),
            Application_Admin_TranslationsArea::URL_NAME => getClassTypeName(TranslationsScreen::class),
            TestDriver_Area_WizardTest::URL_NAME => getClassTypeName(TestDriver_Area_WizardTest::class),
            QuickNavScreen::URL_NAME => getClassTypeName(QuickNavScreen::class),
            NewsScreen::URL_NAME => getClassTypeName(NewsScreen::class),
            MediaLibraryScreen::URL_NAME => getClassTypeName(MediaLibraryScreen::class),
            TestingScreen::URL_NAME => getClassTypeName(TestingScreen::class),
            RevisionableScreen::URL_NAME => getClassTypeName(RevisionableScreen::class),
        );
    }

    public function areaExists(string $name) : bool
    {
        $areas = $this->getAdminAreas();
        return isset($areas[$name]);
    }

    /**
     * Overridden to check if the test driver is running
     * in unit test mode: In this case, the redirect is
     * ignored to support testing admin screen classes.
     *
     * @param $paramsOrURL
     * @return void
     * @throws Application_Exception
     */
    public function redirectTo($paramsOrURL = null) : void
    {
        if(defined('APP_FRAMEWORK_TESTS'))
        {
            return;
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
    
    public function getExtendedVersion() : string
    {
        return '1.0.0';
    }
}
