<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Cache;

use TestDriver\API\TestCacheableMethod;
use Application\API\APIManager;
use Application\API\Cache\APICacheManager;
use Application\API\Cache\APIResponseCacheLocation;
use Application\API\Cache\Strategies\FixedDurationStrategy;
use Application\API\ErrorResponsePayload;
use Application\API\ResponsePayload;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;

/**
 * Integration tests that verify end-to-end cache hit/miss/invalidation
 * behaviour and CacheControl integration for API response caching.
 */
final class APICacheIntegrationTest extends APITestCase
{
    protected function tearDown() : void
    {
        parent::tearDown();

        APICacheManager::clearAll();
    }

    private function createMethod(int $ttl = FixedDurationStrategy::DURATION_1HOUR) : TestCacheableMethod
    {
        return new TestCacheableMethod(APIManager::getInstance(), $ttl);
    }

    // region: End-to-end cache hit

    public function test_secondCallReturnsCachedData() : void
    {
        $method = $this->createMethod();

        // First call: fresh data is computed and written to the cache.
        $response1 = $method->processReturn();

        $this->assertInstanceOf(ResponsePayload::class, $response1);
        $this->assertSame(1, $method->getCollectCount());

        // Second call: data must be served from cache without re-invoking collectResponseData.
        $response2 = $method->processReturn();

        $this->assertInstanceOf(ResponsePayload::class, $response2);

        // collectResponseData must NOT have been called again.
        $this->assertSame(1, $method->getCollectCount());

        // Both responses must carry the same payload.
        $this->assertSame($response1->getData(), $response2->getData());
    }

    // endregion

    // region: Cache invalidation

    public function test_invalidateCacheForcesRecompute() : void
    {
        $method = $this->createMethod();

        // First call to populate the cache.
        $method->processReturn();
        $this->assertSame(1, $method->getCollectCount());

        // Invalidate and call again — must recompute.
        $method->invalidateCache();

        $method->processReturn();

        $this->assertSame(2, $method->getCollectCount());
    }

    // endregion

    // region: Cache file path

    public function test_cacheFileIsWrittenToExpectedPath() : void
    {
        $method = $this->createMethod();
        $version = $method->getCurrentVersion();

        $method->processReturn();

        $expectedFile = APICacheManager::getMethodCacheFolder($method->getMethodName())->getPath()
            . '/' . $method->getCacheKey($version) . '.json';

        $this->assertFileExists($expectedFile);
    }

    // endregion

    // region: Expired cache

    public function test_expiredCacheEntryIsIgnored() : void
    {
        // Use 1-second TTL so the entry expires almost immediately.
        $method = $this->createMethod(1);

        $method->processReturn();
        $this->assertSame(1, $method->getCollectCount());

        // Expire the cache entry by back-dating the file's modification time.
        $version = $method->getCurrentVersion();
        $cacheFile = APICacheManager::getMethodCacheFolder($method->getMethodName())->getPath()
            . '/' . $method->getCacheKey($version) . '.json';

        touch($cacheFile, time() - 60);

        // Second call must recompute because the entry has expired.
        $method->processReturn();

        $this->assertSame(2, $method->getCollectCount());
    }

    // endregion

    // region: CacheControl integration

    public function test_cacheLocation_byteSizeIsPositiveAfterCaching() : void
    {
        $method = $this->createMethod();
        $method->processReturn();

        $location = new APIResponseCacheLocation();

        $this->assertGreaterThan(0, $location->getByteSize());
    }

    public function test_cacheLocation_clearRemovesAllCachedData() : void
    {
        $method = $this->createMethod();
        $method->processReturn();

        $location = new APIResponseCacheLocation();
        $location->clear();

        $this->assertSame(0, $location->getByteSize());
    }

    // endregion
}
