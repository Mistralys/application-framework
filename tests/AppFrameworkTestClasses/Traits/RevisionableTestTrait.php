<?php

declare(strict_types=1);

namespace Mistralys\AppFrameworkTests\TestClasses\Traits;

use DBHelper;
use TestDriver\Revisionables\RevisionableCollection;
use TestDriver\Revisionables\RevisionableRecord;

trait RevisionableTestTrait
{
    protected RevisionableCollection $revCollection;

    protected function setUpRevisionableTest(): void
    {
        $this->startTransaction();

        DBHelper::deleteRecords(RevisionableCollection::TABLE_NAME);

        $this->revCollection = RevisionableCollection::getInstance();
    }

    protected function createTestRevisionable(?string $label = null): RevisionableRecord
    {
        if (empty($label)) {
            $label = 'Test Revisionable ' . $this->getTestCounter('revisionable');
        }

        return $this->revCollection->createNewRevisionable($label);
    }

    protected function assertRecordIsFinalized(RevisionableRecord $record): void
    {
        $this->assertRecordStateIs($record, RevisionableRecord::STATUS_FINALIZED);
    }

    protected function assertRecordIsInactive(RevisionableRecord $record): void
    {
        $this->assertRecordStateIs($record, RevisionableRecord::STATUS_INACTIVE);
    }

    protected function assertRecordIsDeleted(RevisionableRecord $record): void
    {
        $this->assertRecordStateIs($record, RevisionableRecord::STATUS_DELETED);
    }

    protected function assertRecordIsDraft(RevisionableRecord $record): void
    {
        $this->assertRecordStateIs($record, RevisionableRecord::STATUS_DRAFT);
    }

    protected function assertRecordStateIs(RevisionableRecord $record, string $state): void
    {
        $this->assertSame($state, $record->getStateName());
    }
}
