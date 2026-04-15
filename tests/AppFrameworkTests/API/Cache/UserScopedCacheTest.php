<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Cache;

use Application\API\APIManager;
use Application\API\Cache\APICacheException;
use Application\API\Cache\APICacheManager;
use AppFrameworkTestClasses\ApplicationTestCase;
use TestDriver\API\TestUserScopedMethod;

/**
 * Unit tests for {@see \Application\API\Cache\UserScopedCacheTrait}:
 * user scope injection, cross-user key isolation, and empty-identifier guard.
 */
final class UserScopedCacheTest extends ApplicationTestCase
{
    protected function tearDown() : void
    {
        parent::tearDown();

        APICacheManager::clearAll();
    }

    private function createMethod(string $userIdentifier = 'user-1', array $scopedParams = array()) : TestUserScopedMethod
    {
        return new TestUserScopedMethod(APIManager::getInstance(), $userIdentifier, $scopedParams);
    }

    // region: _userScope injection

    /**
     * getCacheKeyParameters() must always include a _userScope key whose
     * value equals the identifier returned by getUserCacheIdentifier().
     */
    public function test_userScopeIsInjectedIntoCacheKeyParameters() : void
    {
        $method = $this->createMethod('user-abc');

        $params = $method->getCacheKeyParameters();

        $this->assertArrayHasKey('_userScope', $params);
        $this->assertSame('user-abc', $params['_userScope']);
    }

    /**
     * Method-specific parameters from getUserScopedCacheKeyParameters() must be
     * present alongside the injected _userScope key.
     */
    public function test_methodSpecificParamsAreMergedWithUserScope() : void
    {
        $method = $this->createMethod('user-1', array('region' => 'eu', 'lang' => 'de'));

        $params = $method->getCacheKeyParameters();

        $this->assertArrayHasKey('_userScope', $params);
        $this->assertArrayHasKey('region', $params);
        $this->assertArrayHasKey('lang', $params);
        $this->assertSame('eu', $params['region']);
        $this->assertSame('de', $params['lang']);
    }

    /**
     * If getUserScopedCacheKeyParameters() returns a _userScope key, the
     * trait's injected value must always take precedence (union operator
     * semantics: left operand wins on key collision).
     */
    public function test_userScopeKeyCannotBeOverriddenByConsumer() : void
    {
        $methodWithOverride = new class(APIManager::getInstance()) extends TestUserScopedMethod {
            public function getUserScopedCacheKeyParameters() : array
            {
                // Attempt to overwrite the reserved _userScope key.
                return array('_userScope' => 'attacker', 'other' => 'value');
            }
        };

        $params = $methodWithOverride->getCacheKeyParameters();

        // The trait-injected value must NOT be 'attacker'.
        $this->assertSame('user-1', $params['_userScope']);
        $this->assertSame('value', $params['other']);
    }

    // endregion

    // region: Cross-user key isolation

    /**
     * Two requests with different user identifiers must produce different
     * cache keys, ensuring one user's response cannot be served to another.
     */
    public function test_differentUsersProduceDifferentCacheKeys() : void
    {
        $methodUser1 = $this->createMethod('user-1');
        $methodUser2 = $this->createMethod('user-2');

        $key1 = $methodUser1->getCacheKey(TestUserScopedMethod::VERSION_1_0);
        $key2 = $methodUser2->getCacheKey(TestUserScopedMethod::VERSION_1_0);

        $this->assertNotSame($key1, $key2);
    }

    /**
     * Same user identifier must always produce the same cache key
     * (determinism requirement).
     */
    public function test_sameUserAlwaysProducesSameCacheKey() : void
    {
        $methodA = $this->createMethod('user-42');
        $methodB = $this->createMethod('user-42');

        $this->assertSame(
            $methodA->getCacheKey(TestUserScopedMethod::VERSION_1_0),
            $methodB->getCacheKey(TestUserScopedMethod::VERSION_1_0)
        );
    }

    /**
     * Same user but different method-specific params must produce different keys.
     */
    public function test_sameUserDifferentParamsProduceDifferentKeys() : void
    {
        $methodA = $this->createMethod('user-1', array('page' => '1'));
        $methodB = $this->createMethod('user-1', array('page' => '2'));

        $this->assertNotSame(
            $methodA->getCacheKey(TestUserScopedMethod::VERSION_1_0),
            $methodB->getCacheKey(TestUserScopedMethod::VERSION_1_0)
        );
    }

    // endregion

    // region: Empty-identifier guard

    /**
     * An empty string returned by getUserCacheIdentifier() must cause
     * APICacheException with ERROR_EMPTY_USER_CACHE_IDENTIFIER to be thrown.
     */
    public function test_emptyUserIdentifierThrowsAPICacheException() : void
    {
        $method = $this->createMethod('');

        $this->expectException(APICacheException::class);
        $this->expectExceptionCode(APICacheException::ERROR_EMPTY_USER_CACHE_IDENTIFIER);

        $method->getCacheKeyParameters();
    }

    /**
     * The exception thrown for an empty identifier must be APICacheException
     * specifically, not a generic TypeError or other exception.
     */
    public function test_emptyIdentifierExceptionType() : void
    {
        $method = $this->createMethod('');

        $caught = null;

        try
        {
            $method->getCacheKeyParameters();
        }
        catch(APICacheException $e)
        {
            $caught = $e;
        }

        $this->assertInstanceOf(APICacheException::class, $caught);
        $this->assertSame(APICacheException::ERROR_EMPTY_USER_CACHE_IDENTIFIER, $caught->getCode());
    }

    /**
     * A non-empty identifier must not throw any exception.
     */
    public function test_nonEmptyIdentifierDoesNotThrow() : void
    {
        $method = $this->createMethod('any-non-empty-value');

        // Must not throw.
        $params = $method->getCacheKeyParameters();

        $this->assertArrayHasKey('_userScope', $params);
    }

    // endregion
}
