<?php

declare(strict_types=1);

namespace AppFrameworkTests\CacheControl;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\Admin\Index\AdminScreenIndex;
use Application\Admin\Index\AdminScreenIndexCacheLocation;
use Application\Admin\Index\Events\RegisterAdminScreenIndexCacheListener;
use Application\CacheControl\CacheManager;
use Application\CacheControl\RegeneratingCacheLocationInterface;
use Application\EventHandler\OfflineEvents\Index\EventIndex;
use Application\EventHandler\OfflineEvents\Index\EventIndexCacheLocation;
use Application\EventHandler\OfflineEvents\Index\Events\RegisterEventIndexCacheListener;

/**
 * Tests that the admin screen index and offline event index are
 * registered with the {@see CacheManager} as
 * {@see RegeneratingCacheLocationInterface} locations, and that
 * clearing them regenerates the underlying index files in place
 * rather than leaving them absent.
 *
 * @see AdminScreenIndexCacheLocation
 * @see EventIndexCacheLocation
 */
final class BuildIndexCacheLocationsTest extends ApplicationTestCase
{
    private function getManager(): CacheManager
    {
        return CacheManager::getInstance();
    }

    public function test_locationsAreRegistered(): void
    {
        $admin = $this->getManager()->getByID(AdminScreenIndexCacheLocation::CACHE_ID);
        $event = $this->getManager()->getByID(EventIndexCacheLocation::CACHE_ID);

        $this->assertInstanceOf(AdminScreenIndexCacheLocation::class, $admin);
        $this->assertInstanceOf(RegeneratingCacheLocationInterface::class, $admin);

        $this->assertInstanceOf(EventIndexCacheLocation::class, $event);
        $this->assertInstanceOf(RegeneratingCacheLocationInterface::class, $event);
    }

    /**
     * The two new listener classes must themselves be discoverable
     * in the offline events index: otherwise, they would never be
     * triggered to register their cache locations with the
     * {@see CacheManager} in the first place.
     */
    public function test_cacheListenersAreIndexed(): void
    {
        $index = EventIndex::getInstance();

        $this->assertTrue($index->listenerClassExists(RegisterAdminScreenIndexCacheListener::class));
        $this->assertTrue($index->listenerClassExists(RegisterEventIndexCacheListener::class));
    }

    public function test_clearLocationRegeneratesAdminScreenIndex(): void
    {
        $file = AdminScreenIndex::getIndexFile();
        $location = $this->getManager()->getByID(AdminScreenIndexCacheLocation::CACHE_ID);

        $file->delete();
        $this->assertFalse($file->exists());

        $this->getManager()->clearLocation($location);

        $this->assertTrue($file->exists());
    }

    public function test_clearLocationRegeneratesEventIndex(): void
    {
        $file = EventIndex::getIndexFile();
        $location = $this->getManager()->getByID(EventIndexCacheLocation::CACHE_ID);

        $file->delete();
        $this->assertFalse($file->exists());

        $this->getManager()->clearLocation($location);

        $this->assertTrue($file->exists());
    }

    public function test_clearAllLeavesIndexesInPlace(): void
    {
        $adminFile = AdminScreenIndex::getIndexFile();
        $eventFile = EventIndex::getIndexFile();

        $this->getManager()->clearAll();

        $this->assertTrue($adminFile->exists());
        $this->assertTrue($eventFile->exists());
    }
}
