<?php

final class Application_SettingsTest extends ApplicationTestCase
{
    private $settingName = 'expiry_test';
    private $settingValue = 'bar';
    private $settingUpdatedValue = 'bar2';

    protected function setUp() : void
    {
        parent::setUp();
        Application_Driver::setSetting($this->settingName, $this->settingValue);
    }

    protected function tearDown() : void
    {
        parent::tearDown();
        Application_Driver::deleteSetting($this->settingName);
    }

    public function test_setSetting() : void
    {
        $this->logHeader('Set application setting');

        $this->assertSame($this->settingValue, Application_Driver::getSetting($this->settingName));
    }

    public function test_updateSetting() : void
    {
        $this->logHeader('Update application setting');

        Application_Driver::setSetting($this->settingName, $this->settingUpdatedValue);
        $this->assertSame($this->settingUpdatedValue, Application_Driver::getSetting($this->settingName));
    }

    public function test_setExpirySettingFuture() : void
    {
        $this->logHeader('Update expiry date one day later application setting');

        $value = new DateTime();
        $value->modify('+1 day');
        Application_Driver::setSettingExpiry($this->settingName, $value);
        $this->assertNotEmpty(Application_Driver::getSetting($this->settingName));
    }

    public function test_setExpirySettingPast() : void
    {
        $this->logHeader('Update expiry date one day before application setting');

        $value = new DateTime();
        $value->modify('-1 day');
        Application_Driver::setSettingExpiry($this->settingName, $value);
        $this->assertEmpty(Application_Driver::getSetting($this->settingName));
    }
}