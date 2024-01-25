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

    public function test_automaticStateChange() : void
    {
        $record = $this->createTestRevisionable();

        $record->makeFinalized();

        $record->startCurrentUserTransaction();
        $record->setLabel('New label');
        $record->endTransaction();

        $this->assertSame('New label', $record->getLabel());
    }
}
