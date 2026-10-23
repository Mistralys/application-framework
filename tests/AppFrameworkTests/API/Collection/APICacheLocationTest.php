<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Collection;

use Application\API\APIManager;
use Application\API\Collection\APIMethodIndex;
use Application\AppFactory\APICacheLocation;
use Application\CacheControl\CacheManager;
use Application\CacheControl\RegeneratingCacheLocationInterface;
use AppFrameworkTestClasses\ApplicationTestCase;

/**
 * Tests the {@see APICacheLocation} "regenerate in place" behavior,
 * both standalone and via the {@see CacheManager}.
 */
final class APICacheLocationTest extends ApplicationTestCase
{
    private function getIndex(): APIMethodIndex
    {
        return APIManager::getInstance()->getMethodIndex();
    }

    public function test_isRegeneratingLocation(): void
    {
        $location = $this->getIndex()->getCacheLocation();

        $this->assertInstanceOf(APICacheLocation::class, $location);
        $this->assertInstanceOf(RegeneratingCacheLocationInterface::class, $location);
    }

    public function test_regenerateLeavesIndexPresent(): void
    {
        $index = $this->getIndex();
        $index->build();

        $location = $index->getCacheLocation();
        $file = $index->getDataFile();

        $file->delete();
        $this->assertFalse($file->exists());

        $location->regenerate();

        $this->assertTrue($file->exists());

        $data = $file->getData();
        $this->assertSame(APIMethodIndex::SCHEMA_VERSION, $data[APIMethodIndex::KEY_SCHEMA_VERSION]);

        // The in-memory index must have been reset, so the next
        // read picks up the freshly rebuilt data from disk.
        $names = $index->getMethodNames();
        $this->assertNotEmpty($names);
    }

    public function test_clearLocationRegeneratesIndex(): void
    {
        $index = $this->getIndex();
        $index->build();

        $location = $index->getCacheLocation();
        $file = $index->getDataFile();

        CacheManager::getInstance()->clearLocation($location);

        // The index must still be present: it was regenerated,
        // never merely deleted.
        $this->assertTrue($file->exists());

        $data = $file->getData();
        $this->assertSame(APIMethodIndex::SCHEMA_VERSION, $data[APIMethodIndex::KEY_SCHEMA_VERSION]);
    }
}
