<?php

declare(strict_types=1);

namespace AppFrameworkTests\Revisionables;

use Application\Revisionable\RevisionableCollectionInterface;
use Application\Revisionable\RevisionableInterface;
use Application\Revisionable\StatusHandling\StandardStateSetupInterface;
use Application_Revisionable;
use Mistralys\AppFrameworkTests\TestClasses\RevisionableTestCase;
use TestDriver\Revisionables\RevisionableCollection;
use TestDriver\Revisionables\RevisionableRecord;

/**
 * @see RevisionableCollection
 * @see RevisionableRecord
 */
class RevisionSelectionTests extends RevisionableTestCase
{
    public function test_validStateChanges() : void
    {
        $record = $this->createTestRevisionable();

        $record->makeFinalized();

        $this->assertRecordIsFinalized($record);

        $record->makeInactive();

        $this->assertRecordIsInactive($record);

        $record->makeDraft();

        $this->assertRecordIsDraft($record);

        $record->makeDeleted();

        $this->assertRecordIsDeleted($record);
    }

    public function test_stateChangeNotAllowed() : void
    {
        $record = $this->createTestRevisionable();

        $record->makeDeleted();

        $this->expectExceptionCode(RevisionableInterface::ERROR_INVALID_STATE_CHANGE);

        $record->makeFinalized();
    }

    public function test_noStateChangeForNonStructuralSettings() : void
    {
        $record = $this->createTestRevisionable();

        $record->makeFinalized();

        $rev = $record->getRevision();

        $record->startCurrentUserTransaction();
        $record->setLabel('New label');
        $record->endTransaction();

        $this->assertTrue($record->getRevision() > $rev);
        $this->assertRecordIsFinalized($record, 'A non-structural change must not change the finalized state.');
        $this->assertSame('New label', $record->getLabel());
    }

    public function test_stateChangeForStructuralSettings() : void
    {
        $record = $this->createTestRevisionable();

        $record->makeFinalized();

        $rev = $record->getRevision();

        $record->startCurrentUserTransaction();
        $record->setStructuralKey('New freeform value');
        $this->assertTrue($record->hasStructuralChanges());
        $record->endTransaction();

        $this->assertTrue($record->getRevision() > $rev);
        $this->assertRecordIsDraft($record, 'A structural change must switch to draft.');
        $this->assertSame('New freeform value', $record->getStructuralKey());
    }

    public function test_getLatestRevisionByState() : void
    {
        $record = $this->createTestRevisionable();
        $draftRevision = $record->getRevision();
        $draftState = $record->getStateByName(StandardStateSetupInterface::STATUS_DRAFT);

        $record->makeFinalized();

        $collection = $record->getCollection();

        $latest = $collection->getLatestRevisionByState($record->getID(), $draftState);

        $this->assertSame($draftRevision, $latest);
    }

    public function test_getDateLastFinalized() : void
    {
        $record = $this->createTestRevisionable();

        $record->makeFinalized();
        $finalizedDate = $record->getRevisionDate();

        $record->startCurrentUserTransaction();
        $record->setStructuralKey('Structural change');
        $record->endTransaction();

        $this->assertEquals($finalizedDate, $record->getDateLastFinalized());
    }

    public function test_undoRevision() : void
    {
        $record = $this->createTestRevisionable();
        $draftRevision = $record->getRevision();

        $record->makeFinalized();

        $record->undoRevision();

        $this->assertSame($draftRevision, $record->getRevision());
    }

    public function test_undoRevisionCannotDeleteAllRevisions() : void
    {
        $record = $this->createTestRevisionable();

        $this->expectExceptionCode(RevisionableInterface::ERROR_CANNOT_UNDO_REVISION);

        $record->undoRevision();
    }
}
