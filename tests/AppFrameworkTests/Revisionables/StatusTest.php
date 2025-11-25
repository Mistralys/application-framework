<?php

declare(strict_types=1);

namespace AppFrameworkTests\Revisionables;

use Mistralys\AppFrameworkTests\TestClasses\RevisionableTestCase;

final class StatusTest extends RevisionableTestCase
{
    public function test_canBeFinalized() : void
    {
        $revisionable = $this->createTestRevisionable();

        $this->assertTrue($revisionable->canBeFinalized());

        $revisionable->makeFinalized();

        $this->assertFalse($revisionable->canBeFinalized());

        $revisionable->makeInactive();

        $this->assertFalse($revisionable->canBeFinalized());

        $revisionable->makeDeleted();

        $this->assertFalse($revisionable->canBeFinalized());
    }

    public function test_canBeDeleted() : void
    {
        $revisionable = $this->createTestRevisionable();

        $this->assertTrue($revisionable->canBeDeleted());

        $revisionable->makeFinalized();

        $this->assertTrue($revisionable->canBeDeleted());

        $revisionable->makeInactive();

        $this->assertTrue($revisionable->canBeDeleted());
    }

    public function test_canBeMadeInactive() : void
    {
        $revisionable = $this->createTestRevisionable();

        $this->assertTrue($revisionable->canBeMadeInactive());

        $revisionable->makeFinalized();

        $this->assertTrue($revisionable->canBeMadeInactive());

        $revisionable->makeInactive();

        $this->assertFalse($revisionable->canBeMadeInactive());

        $revisionable->makeDeleted();

        $this->assertFalse($revisionable->canBeMadeInactive());
    }
}
