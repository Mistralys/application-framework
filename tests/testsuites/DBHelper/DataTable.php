<?php

declare(strict_types=1);

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

    protected function setUp() : void
    {
        parent::setUp();
        $this->startTransaction();

        $logIdentifier = sprintf(
            '%s [#%s]',
            ucfirst($this->recordTypeName),
            $this->getTestCounter()
        );
        $insertedTenantID = intval(DBHelper::insertDynamic(
            $this->recordTable,
            array(
                'label' => $this->testLabel,
                'alias' => $this->testAlias
            )
        ));

        $result = DBHelper::createFetchKey($this->recordPrimaryName, $this->recordTable)->whereValue($this->recordPrimaryName, $insertedTenantID)->fetchInt();

        $this->assertSame($insertedTenantID, $result);

        $this->dataTable = new DBHelper_DataTable($this->recordTableData, $this->recordPrimaryName, $insertedTenantID, $logIdentifier);
    }

    protected function tearDown() : void
    {
        $this->dataTable->save();
        parent::tearDown();
    }

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
}