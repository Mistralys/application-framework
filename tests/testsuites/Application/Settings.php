<?php

final class Application_SettingsTest extends ApplicationTestCase
{
    private $settingName = 'expiry_test';
    private $settingValue = 'bar';
    private $settingUpdatedValue = 'bar2';
    private $expiryColumnExists = false;

    protected function setUp() : void
    {
        parent::setUp();

        $this->expiryColumnExists = DBHelper::columnExists(Application_Driver_Storage_DB::TABLE_NAME, Application_Driver_Storage_DB::COL_EXPIRY);

        $this->assertEquals(Application_Driver::STORAGE_TYPE_DB, Application_Driver::getStorageType());

        $this->clearRecords();
    }

    protected function tearDown() : void
    {
        parent::tearDown();

        $this->clearRecords();
    }

    public function test_setSetting() : void
    {
        $this->logHeader('Set application setting');

        Application_Driver::setSetting($this->settingName, $this->settingValue);

        $this->assertSame($this->settingValue, Application_Driver::getSetting($this->settingName));
    }

    public function test_updateSetting() : void
    {
        $this->logHeader('Update application setting');

        Application_Driver::setSetting($this->settingName, $this->settingValue);
        Application_Driver::setSetting($this->settingName, $this->settingUpdatedValue);

        $this->assertSame($this->settingUpdatedValue, Application_Driver::getSetting($this->settingName));
    }

    public function test_setExpirySettingFuture() : void
    {
        $this->skipIfExpiryNotPresent();

        $this->logHeader('Update expiry date one day later application setting');

        Application_Driver::setSetting($this->settingName, $this->settingValue);

        $value = new DateTime();
        $value->modify('+1 day');
        Application_Driver::setSettingExpiry($this->settingName, $value);

        $this->assertEquals($this->settingValue, Application_Driver::getSetting($this->settingName));
    }

    public function test_setExpirySettingPast() : void
    {
        $this->skipIfExpiryNotPresent();

        $this->logHeader('Update expiry date one day before application setting');

        Application_Driver::setSetting($this->settingName, $this->settingValue);

        $value = new DateTime();
        $value->modify('-1 day');
        Application_Driver::setSettingExpiry($this->settingName, $value);

        $this->assertNull(Application_Driver::getSetting($this->settingName));
    }

    private function skipIfExpiryNotPresent() : void
    {
        if(!$this->expiryColumnExists) {
            $this->markTestSkipped('Expiry column is not present.');
        }
    }

    private function clearRecords() : void
    {
        DBHelper::deleteRecords(Application_Driver_Storage_DB::TABLE_NAME);
    }
}
