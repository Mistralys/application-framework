<?php

declare(strict_types=1);

namespace AppFrameworkTests\Revisionables;

use AppFrameworkTestClasses\Stubs\RevisionDependentDisposableStub;
use Mistralys\AppFrameworkTests\TestClasses\RevisionableTestCase;
use TestDisposable;

final class DisposingTests extends RevisionableTestCase
{
    /**
     * When a revisionable is disposed, its revision storage
     * instance must be disposed of as well. This recurses into
     * the storage's disposable keys, as long as they are dependent
     * on the revisionable and that specific revision.
     *
     * @see \Application_RevisionableStateless::_dispose()
     * @see \Application_RevisionStorage::_dispose()
     */
    public function test_disposeRevisionStorageAndDisposableKeys() : void
    {
        $record = $this->createTestRevisionable('FooBar');
        $subNonDependent = new TestDisposable();
        $subDependent = new RevisionDependentDisposableStub($record, $record->getRevision());
        $subDependentNoMatch = new RevisionDependentDisposableStub($record, $record->getRevision() + 1);

        $storage = $record->getRevisionStorage();
        $storage->setPrivateKey('sub-non-dependent', $subNonDependent); // disposable, but not dependent on the revisionable
        $storage->setPrivateKey('sub-dependent', $subDependent); // disposable and dependent on the revisionable
        $storage->setPrivateKey('sub-dependent-no-match', $subDependentNoMatch); // disposable and dependent on the revisionable, but with a different revision

        $this->assertNotNull($storage);
        $this->assertFalse($storage->isDisposed());
        $this->assertContains($storage, $record->getChildDisposables());

        $record->dispose();

        $this->assertTrue($record->isDisposed());
        $this->assertTrue($storage->isDisposed(), 'The record\'s revision storage must be disposed.');
        $this->assertFalse($subNonDependent->isDisposed(), 'Only matching revision-dependent instances must be disposed.');
        $this->assertTrue($subDependent->isDisposed(), 'Revision-dependent instances must be disposed.');
        $this->assertFalse($subDependentNoMatch->isDisposed(), 'Only matching revision-dependent instances must be disposed.');
    }

    /**
     * If a revision is unloaded, all dependent disposables
     * must be disposed of as well.
     */
    public function test_unloadingRevisionDisposesDependents() : void
    {
        $record = $this->createTestRevisionable('FooBar');
        $subDependent = new RevisionDependentDisposableStub($record, $record->getRevision());

        $storage = $record->getRevisionStorage();
        $storage->setPrivateKey('dependent', $subDependent);

        $storage->unloadRevision($record->getRevision());

        $this->assertTrue($subDependent->isDisposed());
    }

    /**
     * If a revision is removed, all dependent disposables
     * must be disposed of as well.
     */
    public function test_removingRevisionDisposesDependents() : void
    {
        $record = $this->createTestRevisionable('FooBar');

        // Make a change to add a revision we can remove,
        // since the initial revision cannot be removed.
        $record->startCurrentUserTransaction();
            $record->setAlias('new-alias');
        $record->endTransaction();

        $subDependent = new RevisionDependentDisposableStub($record, $record->getRevision());
        $storage = $record->getRevisionStorage();
        $storage->setPrivateKey('dependent', $subDependent);

        $storage->removeRevision($record->getRevision());

        $this->assertTrue($subDependent->isDisposed());
    }
}
