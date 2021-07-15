<?php

declare(strict_types=1);

use AppUtils\Microtime;

final class DBHelper_DataTable_TestCase extends ApplicationTestCase
{
    /**
     * @var DBHelper_DataTable
     */
    private $dataTable;

    private $recordTable = 'test_records';

    private $recordTableData = 'test_records_data';

    private $recordPrimaryName = 'record_id';

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

        $this->dataTable->setKey($this->testDataName, $this->testDataValue);

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

    // endregion

    // region: Support methods

    protected function setUp() : void
    {
        parent::setUp();

        $this->startTransaction();
        $this->createTestRecord();
        $this->createDataTable();
    }

    protected function tearDown() : void
    {
        $this->dataTable->save();
        parent::tearDown();
    }


    private function createTestRecord() : void
    {
        $insertID = intval(DBHelper::insertDynamic(
            $this->recordTable,
            array(
                'label' => $this->testLabel,
                'alias' => $this->testAlias
            )
        ));

        $result = DBHelper::createFetchKey($this->recordPrimaryName, $this->recordTable)->whereValue($this->recordPrimaryName, $insertID)->fetchInt();

        $this->assertSame($insertID, $result);

        $this->recordID = $insertID;
    }

    private function createDataTable() : void
    {
        $this->dataTable = new DBHelper_DataTable(
            $this->recordTableData,
            $this->recordPrimaryName,
            $this->recordID,
            sprintf(
                '%s [#%s]',
                ucfirst($this->recordTypeName),
                $this->getTestCounter()
            )
        );

        $this->dataTable->setAutoSave(true);

        // The tests need the class to be in auto save mode.
        $this->assertTrue($this->dataTable->isAutoSaveEnabled());
    }

    // endregion
}
