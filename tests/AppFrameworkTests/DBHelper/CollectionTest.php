<?php

declare(strict_types=1);

namespace AppFrameworkTests\DBHelper;

use AppFrameworkTestClasses\Stubs\IDTableCollectionStub;
use DBHelper;
use DBHelper\BaseCollection\DBHelperCollectionInterface;
use Mistralys\AppFrameworkTests\TestClasses\DBHelperTestCase;
use TestDriver\TestDBRecords\TestDBCollection;

final class CollectionTest extends DBHelperTestCase
{
    public function test_unknownIDDoesNotExist() : void
    {
        $collection = TestDBCollection::getInstance();

        $this->assertFalse($collection->idExists(99911));

        $record = $collection->createTestRecord('Foo', 'foo');

        $recordID = $record->getID();

        $this->assertTrue($collection->idExists($recordID));
    }

    public function test_idExists() : void
    {
        $collection = TestDBCollection::getInstance();

        $record = $collection->createTestRecord('Foo', 'foo');

        $this->assertTrue($collection->idExists($record->getID()));
    }

    public function test_idExistsAfterReset() : void
    {
        $collection = TestDBCollection::getInstance();

        $record = $collection->createTestRecord('Foo', 'foo');

        $collection->resetCollection();

        $this->assertFalse($collection->isRecordLoaded($record->getID()));
        $this->assertTrue($collection->idExists($record->getID()));
    }

    public function test_idDoesNotExistAfterDelete() : void
    {
        $collection = TestDBCollection::getInstance();

        $record = $collection->createTestRecord('Foo', 'foo');

        $recordID = $record->getID();

        $collection->deleteRecord($record);

        $this->assertFalse($collection->idExists($recordID));
    }

    public function test_createNewRecordWithCustomIDWithRegularAutoIncrement() : void
    {
        $collection = TestDBCollection::getInstance();

        $this->assertFalse($collection->hasRecordIDTable());

        $this->assertCustomIDIsUsed($collection);
    }

    public function test_createNewRecordWithCustomIDAndIDTable() : void
    {
        $collection = DBHelper::createCollection(IDTableCollectionStub::class);

        $this->assertInstanceOf(IDTableCollectionStub::class, $collection);
        $this->assertTrue($collection->hasRecordIDTable());

        $this->assertCustomIDIsUsed($collection);
    }

    private function assertCustomIDIsUsed(TestDBCollection $collection) : void
    {
        $record = $collection->createNewRecord(
            array(
                TestDBCollection::COL_LABEL => 'Foo',
                TestDBCollection::COL_ALIAS => 'foo',
            ),
            false,
            array(
                DBHelperCollectionInterface::OPTION_CUSTOM_RECORD_ID => 999942
            )
        );

        $this->assertSame($record->getID(), 999942);
    }
}
