<?php

declare(strict_types=1);

namespace AppFrameworkTests\AppSettings;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\AppSettings\AppSettingsFilterCriteria;

final class AppSettingTest extends ApplicationTestCase
{
    public function test_getItems() : void
    {
        $filters = new AppSettingsFilterCriteria();
        $filters->addSetting('test_key_1', 'test_value_1');

        $items = $filters->getItemsObjects();

        $this->assertCount(1, $items);
        $this->assertEquals('test_value_1', $items[0]->getDataValue());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->startTransaction();

        $this->cleanUpTables(array(AppSettingsFilterCriteria::TABLE_NAME));
    }
}