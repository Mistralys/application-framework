<?php

declare(strict_types=1);

namespace AppFrameworkTests\Revisionables;

use Application\Revisionable\RevisionableInterface;
use Application\RevisionStorage\StubDBRevisionStorage;
use Mistralys\AppFrameworkTests\TestClasses\RevisionableTestCase;

final class StubTests extends RevisionableTestCase
{
    public function test_createStubIsStub() : void
    {
        $stub = $this->revCollection->createDummyRecord();

        $this->assertTrue($stub->isStub());
    }

    public function test_stubUsesStubRevisionStorage() : void
    {
        $stub = $this->revCollection->createDummyRecord();

        $this->assertInstanceOf(StubDBRevisionStorage::class, $stub->getRevisionStorage());
    }

    public function test_getRevisionIsDefaultStubRevision() : void
    {
        $stub = $this->revCollection->createDummyRecord();

        // Stub records have no current revision on the collection level,
        // as they do not exist in the database.
        $this->assertNull($this->revCollection->getCurrentRevision($stub->getID()));

        // This is why the stub record's current revision is fixed to
        // the stub revision constant.
        $this->assertSame(StubDBRevisionStorage::STUB_REVISION_NUMBER, $stub->getCurrentRevision());

        // The revision is selected correctly, because DB revisionables
        // select the current revision on instantiation.
        /* @see Application_RevisionableCollection_DBRevisionable::__construct() */

        $this->assertSame($stub->getRevision(), StubDBRevisionStorage::STUB_REVISION_NUMBER);
    }

    public function test_revisionExists() : void
    {
        $stub = $this->revCollection->createDummyRecord();

        $this->assertTrue($stub->revisionExists(StubDBRevisionStorage::STUB_REVISION_NUMBER));
    }

    public function test_transactionNotAllowedOnStubs() : void
    {
        $stub = $this->revCollection->createDummyRecord();

        $this->expectExceptionCode(RevisionableInterface::ERROR_OPERATION_NOT_ALLOWED_ON_STUB);

        $stub->startCurrentUserTransaction();
    }
}
