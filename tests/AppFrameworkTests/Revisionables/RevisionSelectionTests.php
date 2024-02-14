<?php

declare(strict_types=1);

namespace AppFrameworkTests\Revisionables;

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

        $this->expectExceptionCode(Application_Revisionable::ERROR_INVALID_STATE_CHANGE);

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
}
