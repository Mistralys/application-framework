<?php

declare(strict_types=1);

namespace AppFrameworkTests\DBHelper;

use Mistralys\AppFrameworkTests\TestClasses\DBHelperTestCase;
use TestDriver\TestDBRecords\TestDBCollection;

final class CollectionTests extends DBHelperTestCase
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
}
