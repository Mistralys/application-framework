<?php 

declare(strict_types=1);

class TestDriver extends Application_Driver
{
    protected function getCookieNamespace()
    {
        return 'app-testcases';
    }

    public function getAdminAreas()
    {
        return array(
            'devel' => 'Devel',
            'settings' => 'Settings',
            'wizardtest' => 'WizardTest'
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

    protected function setUpUI()
    {
        $this->configureAdminUIFramework();
    }

    public function getAppName()
    {
        return 'Application Framework TestSuite';
    }

    public function getAppNameShort()
    {
        return 'AppTestSuite';
    }

    public function getRevisionableTypes()
    {
        return array();
    }

    public function getPageParams(UI_Page $page)
    {
        return array();
    }
    
    public function getExtendedVersion()
    {
        return '1.0.0';
    }
}
