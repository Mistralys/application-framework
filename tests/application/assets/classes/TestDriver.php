<?php
/**
 * @package TestDriver
 * @subpackage Driver
 */

declare(strict_types=1);

/**
 * @package TestDriver
 * @subpackage Driver
 */
class TestDriver extends Application_Driver
{
    /**
     * NOTE: Uses the custom navigation generation,
     * see {@see \TestDriver\UI\MainNavigation}.
     *
     * @return array<string,string>
     */
    public function getAdminAreas() : array
    {
        return array(
            \TestDriver\Area\Welcome::URL_NAME_WELCOME => getClassTypeName(\TestDriver\Area\Welcome::class),
            Application_Admin_Area_Devel::URL_NAME => getClassTypeName(TestDriver_Area_Devel::class),
            Application_Admin_Area_Settings::URL_NAME => getClassTypeName(TestDriver_Area_Settings::class),
            TestDriver_Area_WizardTest::URL_NAME => getClassTypeName(TestDriver_Area_WizardTest::class),
            TestDriver_Area_QuickNav::URL_NAME => getClassTypeName(TestDriver_Area_QuickNav::class)
        );
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
        return 'Application Framework TestSuite';
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
