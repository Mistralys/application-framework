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
     * At testing phase we don't need redirect to any page.
     * It is stopping test because of page change.
     *
     * @param $paramsOrURL
     * @return void
     */
    public function redirectTo($paramsOrURL = null) : void
    {
        return;
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
