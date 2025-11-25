<?php

declare(strict_types=1);

namespace Mistralys\AppFrameworkTests\TestClasses\Traits;

use Application\Revisionable\StatusHandling\StandardStateSetupInterface;
use DBHelper;
use TestDriver\ClassFactory;
use TestDriver\Revisionables\RevisionableCollection;
use TestDriver\Revisionables\RevisionableRecord;

trait RevisionableTestTrait
{
    protected RevisionableCollection $revCollection;

    protected function setUpRevisionableTest(): void
    {
        $this->startTransaction();

        DBHelper::deleteRecords(RevisionableCollection::TABLE_NAME);

        $this->revCollection = ClassFactory::createRevisionableCollection();
    }

    protected function createTestRevisionable(?string $label = null, ?string $alias = null): RevisionableRecord
    {
        if (empty($label)) {
            $label = 'Test Revisionable ' . $this->getTestCounter('revisionable');
        }

        if(empty($alias)) {
            $alias = 'test_revisionable_'.$this->getTestCounter('revisionable');
        }

        return $this->revCollection->createNew($label, $alias);
    }

    protected function assertRecordIsFinalized(RevisionableRecord $record, ?string $message=null): void
    {
        $this->assertRecordStateIs($record, StandardStateSetupInterface::STATUS_FINALIZED, $message);
    }

    protected function assertRecordIsInactive(RevisionableRecord $record): void
    {
        $this->assertRecordStateIs($record, StandardStateSetupInterface::STATUS_INACTIVE);
    }

    protected function assertRecordIsDeleted(RevisionableRecord $record): void
    {
        $this->assertRecordStateIs($record, StandardStateSetupInterface::STATUS_DELETED);
    }

    protected function assertRecordIsDraft(RevisionableRecord $record, ?string $message=null): void
    {
        $this->assertRecordStateIs($record, StandardStateSetupInterface::STATUS_DRAFT, $message);
    }

    protected function assertRecordStateIs(RevisionableRecord $record, string $state, ?string $message=null): void
    {
        $this->assertSame($state, $record->getStateName(), (string)$message);
    }
}
