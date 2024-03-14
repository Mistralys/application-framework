<?php

declare(strict_types=1);

namespace Mistralys\AppFrameworkTests\TestClasses;

use AppFrameworkTestClasses\ApplicationTestCase;
use TestDriver\Revisionables\RevisionableRecord;

abstract class RevisionableTestCase extends ApplicationTestCase
{
    use Traits\RevisionableTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpRevisionableTest();
    }

    protected function assertRecordHasChangelogType(RevisionableRecord $record, string $type) : void
    {
        $queue = $record->getChangelogQueue();
        foreach($queue as $entry) {
            if($entry['type'] === $type) {
                $this->addToAssertionCount(1);
                return;
            }
        }

        $this->fail(sprintf(
            'The record does not have a changelog entry of type [%s].',
            $type
        ));
    }
}
