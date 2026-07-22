<?php

declare(strict_types=1);

namespace AppFrameworkTests\Application;

use Application\Application;
use AppFrameworkTestClasses\ApplicationTestCase;

/**
 * Verifies that Application::createUser() caches user instances correctly.
 *
 * @see Application::createUser()
 * @see Application::clearUserCache()
 */
final class CreateUserCacheTest extends ApplicationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->startTransaction();
        Application::clearUserCache();
    }

    protected function tearDown(): void
    {
        Application::clearUserCache();
        parent::tearDown();
    }

    /**
     * AC-08: Calling createUser() twice with the same ID must return the same instance.
     *
     * createTestUser() internally calls Application::createUser(), which now populates
     * the cache on first call. A second call with the same ID must return the cached instance.
     */
    public function test_createUserReturnsSameInstance(): void
    {
        $user = $this->createTestUser();
        $userID = $user->getID();

        $second = Application::createUser($userID);

        $this->assertSame(
            $user,
            $second,
            'createUser() must return the same cached instance for the same user ID.'
        );
    }

    /**
     * Clearing the cache must evict previously cached instances, so that the
     * next call to createUser() with the same ID creates a fresh object.
     */
    public function test_clearUserCacheEvictsInstances(): void
    {
        $user = $this->createTestUser();
        $userID = $user->getID();

        Application::clearUserCache();

        $fresh = Application::createUser($userID);

        $this->assertNotSame(
            $user,
            $fresh,
            'After clearUserCache(), createUser() must return a new instance, not the evicted cached one.'
        );
    }
}
