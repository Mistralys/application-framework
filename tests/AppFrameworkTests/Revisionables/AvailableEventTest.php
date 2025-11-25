<?php

declare(strict_types=1);

namespace AppFrameworkTests\Revisionables;

use Application\Revisionable\Event\BeforeSaveEvent;
use Application\Revisionable\Event\TransactionEndedEvent;
use Application\Revisionable\Event\RevisionAddedEvent;
use Mistralys\AppFrameworkTests\TestClasses\RevisionableTestCase;

final class AvailableEventTest extends RevisionableTestCase
{
    public function test_onRevisionAdded() : void
    {
        $record = $this->createTestRevisionable('FooBar');
        $triggered = null;

        $record->onRevisionAdded(function(RevisionAddedEvent $event) use(&$triggered) {
            $triggered = $event;
        });

        $record->startCurrentUserTransaction();

        $this->assertInstanceOf(RevisionAddedEvent::class, $triggered);
        $this->assertSame($triggered->getNumber(), $record->getRevision());

        $record->rollBackTransaction();
    }

    public function test_onBeforeSave() : void
    {
        $record = $this->createTestRevisionable('FooBar');
        $triggered = null;

        $record->onBeforeSave(function (BeforeSaveEvent $event) use (&$triggered) {
            $triggered = $event;
        });

        $record->startCurrentUserTransaction();
            $record->setLabel('New label');
        $record->endTransaction();

        $this->assertInstanceOf(BeforeSaveEvent::class, $triggered);
    }

    public function test_onTransactionEndedChanged() : void
    {
        $record = $this->createTestRevisionable('FooBar');
        $triggered = null;

        $record->onTransactionEnded(function(TransactionEndedEvent $event) use(&$triggered) {
            $triggered = $event;
        });

        $revCount = $record->countRevisions();

        $record->startCurrentUserTransaction();
            $record->setAlias('new_alias');
        $record->endTransaction();

        $this->assertInstanceOf(TransactionEndedEvent::class, $triggered);

        $info = $triggered->getTransactionInfo();

        $this->assertSame($revCount+1, $record->countRevisions(), 'The revision count must have increased by 1.');
        $this->assertSame($record->getRevision(), $info->getNewRevision());
        $this->assertTrue($info->isNewRevision());
        $this->assertTrue($info->isChanged());
        $this->assertFalse($info->isSimulated());
        $this->assertFalse($info->isRolledBack());
        $this->assertFalse($info->isUnchanged());
    }

    public function test_onTransactionEndedUnchanged() : void
    {
        $record = $this->createTestRevisionable('FooBar');
        $triggered = null;

        $record->onTransactionEnded(function(TransactionEndedEvent $event) use(&$triggered) {
            $triggered = $event;
        });

        $revCount = $record->countRevisions();

        $record->startCurrentUserTransaction();
        $record->endTransaction();

        $this->assertInstanceOf(TransactionEndedEvent::class, $triggered);

        $info = $triggered->getTransactionInfo();

        $this->assertSame($revCount, $record->countRevisions(), 'The revision count must stay unchanged');
        $this->assertNull($info->getNewRevision());
        $this->assertFalse($info->isNewRevision());
        $this->assertFalse($info->isChanged());
        $this->assertFalse($info->isSimulated());
        $this->assertFalse($info->isRolledBack());
        $this->assertTrue($info->isUnchanged());
    }

    public function test_onTransactionRolledBack() : void
    {
        $record = $this->createTestRevisionable('FooBar');
        $triggered = null;

        $record->onTransactionEnded(function(TransactionEndedEvent $event) use(&$triggered) {
            $triggered = $event;
        });

        $revCount = $record->countRevisions();

        $record->startCurrentUserTransaction();
            $record->setAlias('new_alias_foo');
        $record->rollBackTransaction();

        $this->assertInstanceOf(TransactionEndedEvent::class, $triggered);

        $info = $triggered->getTransactionInfo();

        $this->assertSame($revCount, $record->countRevisions(), 'The revision count should not have changed');
        $this->assertNull($info->getNewRevision());
        $this->assertFalse($info->isNewRevision());
        $this->assertFalse($info->isChanged());
        $this->assertFalse($info->isSimulated());
        $this->assertTrue($info->isRolledBack());
        $this->assertFalse($info->isUnchanged());
    }
}
