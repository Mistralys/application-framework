<?php

declare(strict_types=1);

namespace AppFrameworkTests\Revisionables;

use Mistralys\AppFrameworkTests\TestClasses\RevisionableTestCase;
use TestDriver\Revisionables\RevisionableCollection;
use TestDriver\Revisionables\RevisionableRecord;
use TestDriver\Revisionables\Storage\RevisionableStorage;

/**
 * @see RevisionableStorage
 * @see RevisionableCollection
 * @see RevisionableRecord
 */
final class StorageTests extends RevisionableTestCase
{
    // region: _Tests

    public function test_latestRevisionSelectedByDefault() : void
    {
        $this->assertTrue($this->storage->hasRevisions());
        $this->assertSame($this->storage->getRevision(), $this->storage->getLatestRevision());
    }

    public function test_setKey() : void
    {
        $key = RevisionableCollection::COL_REV_STRUCTURAL;

        $this->assertSame('', $this->storage->getKey($key));

        $this->storage->setKey($key, 'freeform value');

        $this->assertSame('freeform value', $this->storage->getKey($key));
    }

    public function test_addByCopy() : void
    {
        $this->revisionable->setStructuralKey('FooBar');
    }

    // endregion

    // region: Support methods

    private RevisionableStorage $storage;
    private RevisionableRecord $revisionable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->revisionable = $this->createTestRevisionable();
        $this->storage = new RevisionableStorage($this->revisionable);
    }

    // endregion
}
