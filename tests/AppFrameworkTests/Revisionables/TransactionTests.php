<?php

declare(strict_types=1);

namespace AppFrameworkTests\Revisionables;

use Mistralys\AppFrameworkTests\TestClasses\RevisionableTestCase;
use TestDriver\Revisionables\RevisionableRecord;

final class TransactionTests extends RevisionableTestCase
{
    public function test_startTransaction() : void
    {
        $revisionable = $this->createTestRevisionable();

        $this->assertFalse($revisionable->isTransactionStarted());

        $revisionable->startCurrentUserTransaction();

            $this->assertTrue($revisionable->isTransactionStarted());
            $this->assertFalse($revisionable->hasChanges());
            $this->assertEmpty($revisionable->getChangedParts());

            $revisionable->setStructuralKey('New value');

            $this->assertTrue($revisionable->hasChanges());
            $this->assertContains(RevisionableRecord::STORAGE_PART_CUSTOM_KEYS, $revisionable->getChangedParts());
            $this->assertTrue($revisionable->hasStructuralChanges());
            $this->assertTrue($revisionable->isPartChanged(RevisionableRecord::STORAGE_PART_CUSTOM_KEYS));

        $revisionable->endTransaction();

        $this->assertFalse($revisionable->isTransactionStarted());
        $this->assertFalse($revisionable->hasChanges());
        $this->assertFalse($revisionable->hasStructuralChanges());
        $this->assertEmpty($revisionable->getChangedParts());
    }
}
