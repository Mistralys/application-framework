<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Cache;

use Application\API\APIManager;
use Application\API\Cache\APICacheManager;
use AppFrameworkTestClasses\ApplicationTestCase;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\FileHelper\JSONFile;
use TestDriver\API\TestCacheableMethod;
use TestDriver\API\TestUserScopedMethod;

/**
 * Unit tests for corrupt-cache resilience and cache key stability:
 *
 * - Reading a corrupt JSON file returns null and deletes the file.
 * - getCacheKey() is deterministic for the same parameters.
 * - getCacheKey() throws JsonException for non-JSON-encodable parameters.
 */
final class CacheResilienceTest extends ApplicationTestCase
{
    protected function tearDown() : void
    {
        parent::tearDown();

        APICacheManager::clearAll();
    }

    // region: Corrupt-cache resilience

    /**
     * When the cache file contains invalid JSON, readFromCache() must return
     * null (cache miss) rather than throwing an exception.
     */
    public function test_corruptCacheFileReturnsNull() : void
    {
        $method = new TestCacheableMethod(APIManager::getInstance());
        $version = TestCacheableMethod::VERSION_1_0;

        // Create the cache folder and write an invalid JSON file at the expected path.
        $folder = APICacheManager::getMethodCacheFolder($method->getMethodName());
        FolderInfo::factory($folder->getPath())->create();

        $corruptFile = JSONFile::factory($folder->getPath() . '/' . $method->getCacheKey($version) . '.json');
        file_put_contents($corruptFile->getPath(), '{this is not valid json}');

        $this->assertFileExists($corruptFile->getPath());

        $result = $method->readFromCache($version);

        $this->assertNull($result);
    }

    /**
     * After readFromCache() encounters a corrupt file, the file must be
     * deleted so that future requests trigger a fresh data collection.
     */
    public function test_corruptCacheFileIsDeletedAfterRead() : void
    {
        $method = new TestCacheableMethod(APIManager::getInstance());
        $version = TestCacheableMethod::VERSION_1_0;

        $folder = APICacheManager::getMethodCacheFolder($method->getMethodName());
        FolderInfo::factory($folder->getPath())->create();

        $corruptFile = JSONFile::factory($folder->getPath() . '/' . $method->getCacheKey($version) . '.json');
        file_put_contents($corruptFile->getPath(), '{this is not valid json}');

        $method->readFromCache($version);

        $this->assertFileDoesNotExist($corruptFile->getPath());
    }

    // endregion

    // region: Cache key stability

    /**
     * getCacheKey() must return the same hash for identical parameters,
     * across two separate method instances (determinism).
     */
    public function test_cacheKeyIsDeterministicAcrossInstances() : void
    {
        $params = array('region' => 'eu', 'lang' => 'de');

        $methodA = new TestUserScopedMethod(APIManager::getInstance(), 'user-1', $params);
        $methodB = new TestUserScopedMethod(APIManager::getInstance(), 'user-1', $params);

        $this->assertSame(
            $methodA->getCacheKey(TestUserScopedMethod::VERSION_1_0),
            $methodB->getCacheKey(TestUserScopedMethod::VERSION_1_0)
        );
    }

    /**
     * getCacheKey() must produce different hashes for different parameters
     * (even when the parameters differ only in value, not key).
     */
    public function test_cacheKeyDiffersForDifferentParameters() : void
    {
        $methodA = new TestUserScopedMethod(APIManager::getInstance(), 'user-1', array('page' => '1'));
        $methodB = new TestUserScopedMethod(APIManager::getInstance(), 'user-1', array('page' => '2'));

        $this->assertNotSame(
            $methodA->getCacheKey(TestUserScopedMethod::VERSION_1_0),
            $methodB->getCacheKey(TestUserScopedMethod::VERSION_1_0)
        );
    }

    /**
     * getCacheKey() must throw a JsonException when getCacheKeyParameters()
     * returns a value that json_encode cannot serialize (e.g. a resource).
     *
     * This tests the JSON_THROW_ON_ERROR contract in getCacheKey().
     */
    public function test_nonEncodableCacheKeyParameterThrows() : void
    {
        $resource = fopen('php://memory', 'r');

        $methodWithResource = new class(APIManager::getInstance(), 'user-1', array()) extends TestUserScopedMethod {
            /** @var resource */
            private $res;

            public function setResource(mixed $res) : void
            {
                $this->res = $res;
            }

            public function getUserScopedCacheKeyParameters() : array
            {
                return array('data' => $this->res);
            }
        };

        $methodWithResource->setResource($resource);

        $this->expectException(\JsonException::class);

        $methodWithResource->getCacheKey(TestUserScopedMethod::VERSION_1_0);

        fclose($resource);
    }

    // endregion
}
