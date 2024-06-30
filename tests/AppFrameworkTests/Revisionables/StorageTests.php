<?php

declare(strict_types=1);

namespace AppFrameworkTests\Revisionables;

use Application_RevisionStorage;
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

    public function test_setPrivateKey() : void
    {
        $this->storage->setPrivateKey('foo', 'private');
        $this->storage->setKey('foo', 'public');

        $this->assertSame('private', $this->storage->getPrivateKey('foo'));
        $this->assertSame('public', $this->storage->getKey('foo'));
    }

    public function test_clearPrivateKey() : void
    {
        $this->storage->setPrivateKey('foo', 'private');

        $this->assertTrue($this->storage->hasPrivateKey('foo'));

        $this->storage->clearPrivateKey('foo');

        $this->assertFalse($this->storage->hasPrivateKey('foo'));
    }

    public function test_noPrivateKeyPrefixDuplicates() : void
    {
        $this->storage->setPrivateKey('foo', 'initial');
        $this->storage->setPrivateKey(Application_RevisionStorage::PRIVATE_KEY_PREFIX.'foo', 'overwritten');

        $this->assertSame('overwritten', $this->storage->getPrivateKey('foo'));
    }

    public function test_ownerExists() : void
    {
        $rev = $this->createTestRevisionable();

        $this->assertTrue($rev->getRevisionAuthorID() > 0, 'The revision author ID must not be 0. Given: ['.$rev->getRevisionAuthorID().'].');
        $this->assertNotNull($rev->getRevisionAuthor());

        $rev->getCreator();

        $this->addToAssertionCount(1);
    }

    public function test_hasTimestamp() : void
    {
        $rev = $this->createTestRevisionable();

        $this->assertNotNull($rev->getRevisionTimestamp());
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
