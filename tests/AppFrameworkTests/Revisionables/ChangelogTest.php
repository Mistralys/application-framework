<?php

declare(strict_types=1);

namespace AppFrameworkTests\Revisionables;

use Application\Application;
use TestDriver\Revisionables\ChangelogHandler;
use Mistralys\AppFrameworkTests\TestClasses\RevisionableTestCase;

final class ChangelogTest extends RevisionableTestCase
{
    public function test_changelogEntryAdded() : void
    {
        $record = $this->createTestRevisionable('FooBar');

        $record->startCurrentUserTransaction();

        $record->setAlias('foo_bar');

        $this->assertChangelogableHasTypeEnqueued($record, ChangelogHandler::CHANGELOG_SET_ALIAS);
    }

    public function test_changelogEntriesAreAddedInCorrectRevision() : void
    {
        $record = $this->createTestRevisionable('FooBar');

        $record->startCurrentUserTransaction();
            $record->setAlias('foo_bar');
        $record->endTransaction();

        $changelog = $record->getChangelog();
        $latest = $changelog->getFilters()->getLatest();
        $this->assertNotEmpty($changelog->getEntries());
        $this->assertNotNull($latest);
        $this->assertSame($latest->getType(), ChangelogHandler::CHANGELOG_SET_ALIAS);
    }

    public function test_changelogQueueIsClearedWhenTransactionHasEnded() : void
    {
        $record = $this->createTestRevisionable('FooBar');

        $record->startCurrentUserTransaction();
            $record->setAlias('foo_bar');
            $this->assertNotEmpty($record->getChangelogQueue());
        $record->endTransaction();

        $this->assertEmpty($record->getChangelogQueue());
    }

    public function test_changelogAuthorIsTransactionOwner() : void
    {
        $record = $this->createTestRevisionable('FooBar');
        $owner = $this->createTestUser();

        $this->assertNotSame(
            $owner->getID(),
            Application::getUser()->getID(),
            'The test owner must differ from the session user for this test to be meaningful.'
        );

        $record->startTransaction($owner->getID(), $owner->getName());
        $record->setAlias('foo_bar');
        $record->endTransaction();

        $latest = $record->getChangelog()->getFilters()->getLatest();

        $this->assertNotNull($latest);
        $this->assertSame($owner->getID(), $latest->getAuthorID());
    }

    public function test_changelogAuthorForCurrentUserTransactionIsUnchanged() : void
    {
        $record = $this->createTestRevisionable('FooBar');

        $record->startCurrentUserTransaction();
        $record->setAlias('foo_bar');
        $record->endTransaction();

        $latest = $record->getChangelog()->getFilters()->getLatest();

        $this->assertNotNull($latest);
        $this->assertSame(Application::getUser()->getID(), $latest->getAuthorID());
    }
}
