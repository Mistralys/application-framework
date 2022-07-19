<?php

use Mistralys\AppFrameworkTests\TestClasses\ApplicationTestCase;

final class Application_SettingsTest extends ApplicationTestCase
{
    private string $settingName = 'expiry_test';
    private string $settingValue = 'bar';
    private string $settingUpdatedValue = 'bar2';
    private bool $expiryColumnExists = false;

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

        $settings = Application_Driver::createSettings();

        $settings->set($this->settingName, $this->settingValue);

        $this->assertSame($this->settingValue, $settings->get($this->settingName));
    }

    public function test_setArray() : void
    {
        $settings = Application_Driver::createSettings();

        $settings->setArray('array_setting', array('value' => 'true'));

        $this->assertSame(array('value' => 'true'), $settings->getArray('array_setting'));
    }

    public function test_getFromTestDriver() : void
    {
        TestDriver::createSettings()
            ->setArray('array_setting', array('value' => 'true'));

        $this->assertSame(
            array('value' => 'true'),
            TestDriver::createSettings()->getArray('array_setting')
        );
    }

    public function test_longName() : void
    {
        $settings = Application_Driver::createSettings();

        $name = 'setting_with_long_name_'.md5('very long name');

        $settings->set($name, 'value');

        $this->assertSame('value', $settings->get($name));
    }

    public function test_updateSetting() : void
    {
        $this->logHeader('Update application setting');

        $settings = Application_Driver::createSettings();

        $settings->set($this->settingName, $this->settingValue);
        $settings->set($this->settingName, $this->settingUpdatedValue);

        $this->assertSame($this->settingUpdatedValue, $settings->get($this->settingName));
    }

    public function test_setExpirySettingFuture() : void
    {
        $this->skipIfExpiryNotPresent();

        $this->logHeader('Update expiry date one day later application setting');

        $settings = Application_Driver::createSettings();
        $settings->set($this->settingName, $this->settingValue);

        $value = new DateTime();
        $value->modify('+1 day');
        $settings->setExpiry($this->settingName, $value);

        $this->assertEquals($this->settingValue, $settings->get($this->settingName));
    }

    public function test_setExpirySettingPast() : void
    {
        $this->skipIfExpiryNotPresent();

        $this->logHeader('Update expiry date one day before application setting');

        $settings = Application_Driver::createSettings();

        $settings->set($this->settingName, $this->settingValue);

        $value = new DateTime();
        $value->modify('-1 day');
        $settings->setExpiry($this->settingName, $value);

        $this->assertNull($settings->get($this->settingName));
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
