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

    public function test_setLabel() : void
    {
        $this->assertKeyIsNotStructural(
            'FooBar',
            function () {
                return $this->revisionable->getLabel();
            },
            function () {
                $this->revisionable->setLabel('FooBar');
            }
        );
    }

    public function test_setStructuralKey() : void
    {
        $this->assertKeyIsStructural(
            'FooBar',
            function () {
                return $this->revisionable->getStructuralKey();
            },
            function () {
                $this->revisionable->setStructuralKey('FooBar');
            }
        );
    }

    public function test_setStructuralDataKey() : void
    {
        $this->assertKeyIsStructural(
            'FooBar',
            function () {
                return $this->revisionable->getStructuralDataKey();
            },
            function () {
                $this->revisionable->setStructuralDataKey('FooBar');
            }
        );
    }

    public function test_setNonStructuralDataKey() : void
    {
        $this->assertKeyIsNotStructural(
            'FooBar',
            function () {
                return $this->revisionable->getNonStructuralDataKey();
            },
            function () {
                $this->revisionable->setNonStructuralDataKey('FooBar');
            }
        );
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

    public function assertKeyIsStructural($expected, callable $getter, callable $setter) : void
    {
        $this->revisionable->makeFinalized();

        $this->assertNotSame($expected, $getter());

        $revision = $this->revisionable->getRevision();

        $this->revisionable->startCurrentUserTransaction();

        $this->assertFalse($this->revisionable->hasStructuralChanges());

        $setter();

        $this->assertSame($expected, $getter());
        $this->assertTrue($this->revisionable->hasStructuralChanges());

        $this->revisionable->endTransaction();

        $this->assertTrue($this->revisionable->isDraft());
        $this->assertSame($expected, $getter());
        $this->assertNotSame($revision, $this->revisionable->getRevision());
    }

    public function assertKeyIsNotStructural($expected, callable $getter, callable $setter) : void
    {
        $this->revisionable->makeFinalized();

        $this->assertNotSame($expected, $getter());

        $revision = $this->revisionable->getRevision();

        $this->revisionable->startCurrentUserTransaction();

        $this->assertFalse($this->revisionable->hasStructuralChanges());

        $setter();

        $this->assertSame($expected, $getter());
        $this->assertFalse($this->revisionable->hasStructuralChanges());

        $this->revisionable->endTransaction();

        $this->assertTrue($this->revisionable->isFinalized());
        $this->assertSame($expected, $getter());
        $this->assertNotSame($revision, $this->revisionable->getRevision());
    }

    // endregion
}
