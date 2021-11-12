<?php

declare(strict_types=1);

final class DBHelper_Disposing_TestCase extends ApplicationTestCase
{
    protected function setUp() : void
    {
        parent::setUp();

        $this->startTransaction();
    }

    public function test_resetCollection() : void
    {
        $collection = new Application_Countries();
        $collection->createNewCountry('uk', 'United Kingdom');
        $collection->createNewCountry('de', 'Germany');

        $records = $collection->getAll();

        $collection->resetCollection();

        foreach($records as $record)
        {
            $this->assertTrue($record->isDisposed());
            $this->assertFalse($collection->isRecordLoaded($record->getID()));
        }
    }

    /**
     * When a DB collection has a parent record, and this record
     * is disposed, a fresh instance of the record must be fetched
     * from the DB automatically.
     *
     * @see DBHelper_BaseCollection::callback_parentRecordDisposed()
     */
    public function test_parentRecordDisposed() : void
    {
        $this->markTestIncomplete();

        $parentCollection = new TestDriver_DBHelperCollection();
        $parentRecord = $parentCollection->addTestRecord();

        $collection = new TestDriver_DBHelperCollectionWithParent();
        $collection->bindParentRecord($parentRecord);

        $parentRecord->dispose();

        $this->assertNotEquals($parentRecord->getInstanceID(), $collection->getParentRecord()->getInstanceID());
    }

    /**
     * When the parent record of a collection is deleted, it must
     * not fetch a fresh instance, and throw an exception if the
     * collection is
     */
    public function test_parentRecordDeleted() : void
    {
        $this->markTestIncomplete();
    }
}
