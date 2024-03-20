<?php

declare(strict_types=1);

namespace AppFrameworkTests\Revisionables;

use TestDriver\Revisionables\ChangelogHandler;
use Mistralys\AppFrameworkTests\TestClasses\RevisionableTestCase;

final class ChangelogTests extends RevisionableTestCase
{
    public function test_changelogEntryAdded() : void
    {
        $record = $this->createTestRevisionable('FooBar');

        $record->startCurrentUserTransaction();

        $record->setAlias('foo_bar');

        $this->assertChangelogableHasTypeEnqueued($record, ChangelogHandler::CHANGELOG_SET_ALIAS);
    }
}
