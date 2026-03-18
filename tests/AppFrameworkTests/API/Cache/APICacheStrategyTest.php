<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Cache;

use AppFrameworkTestClasses\ApplicationTestCase;
use TestDriver\API\TestCacheableMethod;
use Application\API\APIManager;
use Application\API\Cache\APICacheManager;
use Application\API\Cache\Strategies\FixedDurationStrategy;
use Application\API\Cache\Strategies\ManualOnlyStrategy;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\FileHelper\JSONFile;

/**
 * Unit tests for API caching components:
 * strategies, cache key generation, and APICacheManager operations.
 */
final class APICacheStrategyTest extends ApplicationTestCase
{
    private string $tempFile = '';

    protected function setUp() : void
    {
        parent::setUp();

        $this->tempFile = sys_get_temp_dir() . '/api-cache-test-' . uniqid() . '.json';
        file_put_contents($this->tempFile, '{}');
    }

    protected function tearDown() : void
    {
        parent::tearDown();

        if(!empty($this->tempFile) && file_exists($this->tempFile))
        {
            unlink($this->tempFile);
        }

        APICacheManager::clearAll();
    }

    // region: FixedDurationStrategy

    public function test_fixedDuration_validFile() : void
    {
        $strategy = new FixedDurationStrategy(3600);
        $file = JSONFile::factory($this->tempFile);

        // File was just created — it is within the TTL window.
        $this->assertTrue($strategy->isCacheFileValid($file));
    }

    public function test_fixedDuration_expiredFile() : void
    {
        $strategy = new FixedDurationStrategy(60);

        // Wind the modification time back by 2 minutes so the 1-minute TTL is exceeded.
        touch($this->tempFile, time() - 120);

        $file = JSONFile::factory($this->tempFile);

        $this->assertFalse($strategy->isCacheFileValid($file));
    }

    // endregion

    // region: ManualOnlyStrategy

    public function test_manualOnly_alwaysValid_freshFile() : void
    {
        $strategy = new ManualOnlyStrategy();
        $file = JSONFile::factory($this->tempFile);

        $this->assertTrue($strategy->isCacheFileValid($file));
    }

    public function test_manualOnly_alwaysValid_oldFile() : void
    {
        $strategy = new ManualOnlyStrategy();

        // Even a very old file must be considered valid.
        touch($this->tempFile, time() - 999999);

        $file = JSONFile::factory($this->tempFile);

        $this->assertTrue($strategy->isCacheFileValid($file));
    }

    // endregion

    // region: Cache key generation

    private function createMethod(array $params = array()) : TestCacheableMethod
    {
        return new TestCacheableMethod(APIManager::getInstance(), FixedDurationStrategy::DURATION_1HOUR, $params);
    }

    public function test_cacheKey_isDeterministic() : void
    {
        $method = $this->createMethod(array('param1' => 'value1', 'param2' => 'value2'));

        $key1 = $method->getCacheKey('1.0');
        $key2 = $method->getCacheKey('1.0');

        $this->assertSame($key1, $key2);
    }

    public function test_cacheKey_changesWithVersion() : void
    {
        $method = $this->createMethod(array('param1' => 'value1'));

        $key1 = $method->getCacheKey('1.0');
        $key2 = $method->getCacheKey('2.0');

        $this->assertNotSame($key1, $key2);
    }

    public function test_cacheKey_changesWithParameterValues() : void
    {
        $method1 = $this->createMethod(array('param1' => 'value1'));
        $method2 = $this->createMethod(array('param1' => 'value2'));

        $this->assertNotSame(
            $method1->getCacheKey('1.0'),
            $method2->getCacheKey('1.0')
        );
    }

    public function test_cacheKey_isOrderIndependent() : void
    {
        $method1 = $this->createMethod(array('a' => '1', 'b' => '2'));
        $method2 = $this->createMethod(array('b' => '2', 'a' => '1'));

        $this->assertSame(
            $method1->getCacheKey('1.0'),
            $method2->getCacheKey('1.0')
        );
    }

    // endregion

    // region: APICacheManager

    public function test_manager_sizeIsZeroWhenNoCacheExists() : void
    {
        APICacheManager::clearAll();

        $this->assertSame(0, APICacheManager::getCacheSize());
    }

    public function test_manager_sizeIsPositiveAfterWritingCache() : void
    {
        $cacheFolder = APICacheManager::getMethodCacheFolder('TestMethod');
        FolderInfo::factory($cacheFolder->getPath())->create();

        $testFile = JSONFile::factory($cacheFolder->getPath() . '/testkey.json');
        $testFile->putData(array('foo' => 'bar'));

        $this->assertGreaterThan(0, APICacheManager::getCacheSize());
    }

    public function test_manager_invalidateMethod_deletesTargetFolder() : void
    {
        $cacheFolder = APICacheManager::getMethodCacheFolder('TestMethod');
        FolderInfo::factory($cacheFolder->getPath())->create();

        JSONFile::factory($cacheFolder->getPath() . '/testkey.json')
            ->putData(array('foo' => 'bar'));

        $this->assertTrue($cacheFolder->exists());

        APICacheManager::invalidateMethod('TestMethod');

        $this->assertFalse(
            FolderInfo::factory($cacheFolder->getPath())->exists()
        );
    }

    public function test_manager_invalidateMethod_isNoopWhenFolderMissing() : void
    {
        // Must not throw an exception when the folder does not exist.
        APICacheManager::invalidateMethod('NonExistentMethod');

        $this->addToAssertionCount(1);
    }

    public function test_manager_clearAll_deletesAllMethodFolders() : void
    {
        foreach(array('MethodA', 'MethodB') as $methodName)
        {
            $folder = APICacheManager::getMethodCacheFolder($methodName);
            FolderInfo::factory($folder->getPath())->create();

            JSONFile::factory($folder->getPath() . '/testkey.json')
                ->putData(array('method' => $methodName));
        }

        $this->assertGreaterThan(0, APICacheManager::getCacheSize());

        APICacheManager::clearAll();

        $this->assertSame(0, APICacheManager::getCacheSize());
    }

    // endregion
}
