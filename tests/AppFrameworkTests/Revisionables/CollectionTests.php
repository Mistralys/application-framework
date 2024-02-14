<?php

declare(strict_types=1);

namespace AppFrameworkTests\Revisionables;

use Mistralys\AppFrameworkTests\TestClasses\RevisionableTestCase;

class CollectionTests extends RevisionableTestCase
{
    public function test_createRecord() : void
    {
        $record = $this->createTestRevisionable('FooBar');

        $this->assertSame('FooBar', $record->getLabel());
        $this->assertRecordIsDraft($record);
    }

    public function test_recordExists() : void
    {
        $this->assertFalse($this->revCollection->idExists(42));

        $record = $this->createTestRevisionable();

        $this->assertTrue($this->revCollection->idExists($record->getID()));
    }

    public function test_revisionExists() : void
    {
        $this->assertFalse($this->revCollection->revisionExists(42));

        $record = $this->createTestRevisionable();

        $this->assertNotFalse($this->revCollection->revisionExists($record->getRevision()));
    }
}
