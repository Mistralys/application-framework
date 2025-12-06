<?php

declare(strict_types=1);

use AppFrameworkTestClasses\Traits\DBHelperTestInterface;
use AppUtils\Microtime;
use Mistralys\AppFrameworkTests\TestClasses\DBHelperTestCase;

final class DBHelper_DataTableTest extends DBHelperTestCase
{
    /**
     * @var DBHelper_DataTable
     */
    private $dataTable;

    private $recordTable = DBHelperTestInterface::TEST_RECORDS_TABLE;

    private $recordTableData = DBHelperTestInterface::TEST_RECORDS_DATA_TABLE;

    private $recordPrimaryName = DBHelperTestInterface::TEST_RECORDS_PRIMARY;

    private $recordTypeName = 'record';

    private $testLabel = 'Test Record';

    private $testAlias = 'TESTRECORD';

    private $testDataName = 'testname';

    private $testDataValue = 'testvalue1,testvalue2,testvalue3';

    private $testDataValue2 = 'testvalue1';

    /**
     * @var int
     */
    private $recordID;

    // region: Tests

    public function test_setKey() : void
    {
        $this->logHeader('Set data table key');

        $this->dataTable->setKey($this->testDataName, $this->testDataValue);

        $result = $this->dataTable->getKey($this->testDataName);

        $this->assertSame($this->testDataValue, $result);
    }

    public function test_updateKey() : void
    {
        $this->logHeader('Update data table key');

        $this->assertFalse($this->dataTable->hasModifiedKeys());
        $this->assertTrue($this->dataTable->setKey($this->testDataName, $this->testDataValue));
        $this->assertFalse($this->dataTable->hasModifiedKeys());

        $this->dataTable->setKey($this->testDataName, $this->testDataValue2);

        $result = $this->dataTable->getKey($this->testDataName);

        $this->assertSame($this->testDataValue2, $result);
    }

    public function test_deleteKey() : void
    {
        $this->logHeader('Delete data table key');

        $this->dataTable->setKey($this->testDataName, $this->testDataValue);

        $this->dataTable->deleteKey($this->testDataName);

        $result = $this->dataTable->getKey($this->testDataName);

        $this->assertEmpty($result);
    }

    public function test_updateAfterDeleteKey() : void
    {
        $this->logHeader('First delete, after that update data table key');

        $this->dataTable->setKey($this->testDataName, $this->testDataValue);
        $this->dataTable->deleteKey($this->testDataName);
        $this->dataTable->setKey($this->testDataName, $this->testDataValue2);

        $result = $this->dataTable->getKey($this->testDataName);

        $this->assertSame($this->testDataValue2, $result);
    }

    public function test_deleteAfterUpdateKey() : void
    {
        $this->logHeader('First update, after that delete data table key');

        $this->dataTable->setKey($this->testDataName, $this->testDataValue);
        $result = $this->dataTable->getKey($this->testDataName);
        $this->assertSame($this->testDataValue, $result);

        $this->dataTable->setKey($this->testDataName, $this->testDataValue2);
        $result = $this->dataTable->getKey($this->testDataName);
        $this->assertSame($this->testDataValue2, $result);

        $this->dataTable->deleteKey($this->testDataName);
        $result = $this->dataTable->getKey($this->testDataName);
        $this->assertEmpty($result);
    }

    /**
     * An unknown key must return an empty value.
     */
    public function test_getUnknownKey() : void
    {
        $this->startTest('Accessing an unknown key');

        $value = $this->dataTable->getKey('unknown_key_'.$this->getTestCounter());

        $this->assertSame('', $value);
    }

    public function test_isKeyExists() : void
    {
        $this->startTest('Checking if a key exists');

        $this->assertFalse($this->dataTable->isKeyExists('unknown_key_'.$this->getTestCounter()));

        $this->dataTable->setKey('key_exists', 'yes');

        $this->assertTrue($this->dataTable->isKeyExists('key_exists'));
    }

    /**
     * A key with an empty value must still be shown
     * as existing.
     */
    public function test_isKeyExists_emptyString() : void
    {
        $this->startTest('Checking if empty value key exists');

        $this->dataTable->setKey('key_exists_empty', '');

        $this->assertTrue($this->dataTable->isKeyExists('key_exists_empty'));
    }

    public function test_getKey() : void
    {
        $this->startTest('Get an existing key');

        $this->dataTable->setKey('known_key','key_value');

        $this->assertSame('key_value', $this->dataTable->getKey('known_key'));
    }

    public function test_intKey() : void
    {
        $this->startTest('Get an integer key');

        $this->dataTable->setIntKey('int_value', 458);

        $this->assertSame(458, $this->dataTable->getIntKey('int_value'));
    }

    public function test_dateKey() : void
    {
        $this->startTest('Get a date key');

        $date = new Microtime();

        $this->dataTable->setDateTimeKey('microtime', $date);

        $this->assertSame($date->getMySQLDate(), $this->dataTable->getDateTimeKey('microtime')->getMySQLDate());
    }

    /**
     * When creating multiple keys, every value has to be
     * correctly accessible.
     *
     * Test for a bug where the key name was not used internally
     * to fetch the value, but only the record's primary key.
     */
    public function test_multiKeys() : void
    {
        $this->startTest('Accessing multiple keys');

        $this->dataTable->setKey('a', 'A');
        $this->dataTable->setKey('b', 'B');
        $this->dataTable->setKey('c', 'C');
        $this->dataTable->setKey('d', 'D');

        // Reset the internal values cache to fetch fresh
        // values from the DB
        $this->dataTable->resetCache();

        $this->assertSame('A', $this->dataTable->getKey('a'));
        $this->assertSame('B', $this->dataTable->getKey('b'));
        $this->assertSame('C', $this->dataTable->getKey('c'));
        $this->assertSame('D', $this->dataTable->getKey('d'));
    }

    public function test_setUserKey() : void
    {
        $this->startTest('Setting a user object key');

        $user = Application::createSystemUser();

        $this->dataTable->setUserKey('user_key', $user);

        $value = $this->dataTable->getUserKey('user_key');

        $this->assertNotNull($value);
        $this->assertSame($user->getID(), $value->getID());
    }

    /**
     * The key delete event must be triggered and listeners
     * must be called on saving (auto save is enabled for
     * the tests).
     */
    public function test_deletedListener() : void
    {
        $this->startTest('Use a listener for deleted keys');

        $dataTable = $this->createDataTable();

        $dataTable->addKeysDeletedListener(array($this, 'callback_keysDeleted'));

        $dataTable->setKey('to_delete', 'yes');
        $dataTable->deleteKey('to_delete');

        $this->assertTrue($this->keysDeletedCalled);
    }

    /**
     * The delete key listener must only be triggered if the
     * key actually exists, and a value could be deleted.
     */
    public function test_deletedListenerKeyNotExists() : void
    {
        $this->startTest('Listener for deleting keys that do not exist');

        $dataTable = $this->createDataTable();

        $dataTable->addKeysDeletedListener(array($this, 'callback_keysDeleted'));

        $dataTable->deleteKey('no_such_key');

        $this->assertFalse($this->keysDeletedCalled);
    }

    /**
     * Working with boolean values.
     */
    public function test_setBoolKey() : void
    {
        $this->startTest('Set and get boolean keys');

        $dataTable = $this->createDataTable();

        // A key that does not exist must return false
        $this->assertFalse($dataTable->getBoolKey('no_such_key'));

        $dataTable->setBoolKey('boolean', true);

        $this->assertTrue($dataTable->getBoolKey('boolean'));

        $dataTable->setBoolKey('boolean', false);

        $this->assertFalse($dataTable->getBoolKey('boolean'));
    }

    /**
     * Setting the maximum key name length must work
     * as intended, by replacing key names that are
     * too long with MD5 encoded strings.
     */
    public function test_maxKeyNameLength() : void
    {
        $dataTable = $this->createDataTable();

        $dataTable->setMaxKeyNameLength(50);

        $okayName = str_repeat('k', 50);
        $tooLongName = str_repeat('k', 60);

        $this->assertEquals($okayName, $dataTable->getStorageKeyName($okayName));
        $this->assertEquals(md5($tooLongName), $dataTable->getStorageKeyName($tooLongName));
    }

    // endregion

    // region: Support methods

    /**
     * @var bool
     */
    private $keysDeletedCalled = false;

    protected function setUp() : void
    {
        parent::setUp();

        $this->keysDeletedCalled = false;
        $this->createTestDBRecordEntry();
        $this->dataTable = $this->createDataTable();
    }

    protected function tearDown() : void
    {
        $this->dataTable->save();
        parent::tearDown();
    }

    public function callback_keysDeleted() : void
    {
        $this->keysDeletedCalled = true;
    }

    private function createTestDBRecordEntry() : void
    {
        $insertID = (int)DBHelper::insertDynamic(
            $this->recordTable,
            array(
                'label' => $this->testLabel,
                'alias' => $this->testAlias
            )
        );

        $result = DBHelper::createFetchKey($this->recordPrimaryName, $this->recordTable)->whereValue($this->recordPrimaryName, $insertID)->fetchInt();

        $this->assertSame($insertID, $result);

        $this->recordID = $insertID;
    }

    private function createDataTable() : DBHelper_DataTable
    {
        $dataTable = (new DBHelper_DataTable(
            $this->recordTableData,
            $this->recordPrimaryName,
            $this->recordID,
            sprintf(
                '%s [#%s]',
                ucfirst($this->recordTypeName),
                $this->getTestCounter()
            )
        ))
            ->setAutoSave(true);

        // The tests need the class to be in auto save mode.
        $this->assertTrue($dataTable->isAutoSaveEnabled());

        return $dataTable;
    }

    // endregion
}
