<?php 

class TestDriver extends Application_Driver
{
    protected function getCookieNamespace()
    {
        return 'app-testcases';
    }

    public function getAdminAreas()
    {
        return array('Settings');
    }

    protected function setUpUI()
    {
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