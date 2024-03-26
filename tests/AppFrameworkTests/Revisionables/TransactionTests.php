<?php

declare(strict_types=1);

namespace AppFrameworkTests\Revisionables;

use Application\Revisionable\RevisionableStatelessInterface;
use Mistralys\AppFrameworkTests\TestClasses\RevisionableTestCase;
use TestDriver\Revisionables\RevisionableRecord;

/**
 * Possible cases are:
 *
 * - No changes made, the new revision is discarded.
 * - Changes made (either structural or not) trigger a new revision.
 * - Status changes trigger a new revision.
 */
final class TransactionTests extends RevisionableTestCase
{
    public function test_changeTracking() : void
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

    public function test_authorChangesWithTransaction() : void
    {
        $revisionable = $this->createTestRevisionable();

        $newUser = $this->createTestUser();

        $this->assertNotSame($newUser->getID(), $revisionable->getOwnerID(), 'Prerequisite is two different users.');

        $revisionable->startTransaction($newUser->getID(), $newUser->getName(), 'New revision comments');

            $this->assertSame($newUser->getID(), $revisionable->getOwnerID());
            $revisionable->setStructuralKey('New value');

        $revisionable->endTransaction();

        $this->assertTrue($revisionable->hasLastTransactionAddedARevision());
        $this->assertSame($newUser->getID(), $revisionable->getOwnerID());
        $this->assertSame('New revision comments', $revisionable->getRevisionComments());
    }

    public function test_transactionWithoutChanges() : void
    {
        $revisionable = $this->createTestRevisionable();

        $newUser = $this->createTestUser();
        $initialOwnerID = $revisionable->getOwnerID();
        $initialRevision = $revisionable->requireRevision();
        $initialComments = $revisionable->getRevisionComments();

        $this->assertNotSame($newUser->getID(), $revisionable->getOwnerID(), 'Prerequisite is two different users.');

        $revisionable->startTransaction($newUser->getID(), $newUser->getName());
            // Do nothing in the transaction
        $revisionable->endTransaction();

        $this->assertFalse($revisionable->hasLastTransactionAddedARevision());
        $this->assertSame($initialRevision, $revisionable->requireRevision(), 'The revision must not change.');
        $this->assertSame($initialOwnerID, $revisionable->getOwnerID());
        $this->assertSame($initialComments, $revisionable->getRevisionComments());
    }

    public function test_checkingAddedRevisionInTransactionThrowsException() : void
    {
        $revisionable = $this->createTestRevisionable();

        $revisionable->startCurrentUserTransaction();

        $this->expectExceptionCode(RevisionableStatelessInterface::ERROR_CANNOT_GET_ADDED_REVISION_DURING_TRANSACTION);

        $revisionable->hasLastTransactionAddedARevision();
    }
}
