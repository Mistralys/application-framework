<?php

declare(strict_types=1);

namespace AppFrameworkTests\Revisionables;

use Mistralys\AppFrameworkTests\TestClasses\RevisionableTestCase;
use TestDriver\Revisionables\RevisionableRecord;

final class ChangelogTests extends RevisionableTestCase
{
    public function test_changelogEntryAdded() : void
    {
        $record = $this->createTestRevisionable('FooBar');

        $record->startCurrentUserTransaction();

        $record->setAlias('foo_bar');

        $this->assertRecordHasChangelogType($record, RevisionableRecord::CHANGELOG_SET_ALIAS);
    }
}
