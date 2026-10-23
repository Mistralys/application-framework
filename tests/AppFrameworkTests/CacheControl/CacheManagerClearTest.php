<?php

declare(strict_types=1);

namespace AppFrameworkTests\CacheControl;

use Application\CacheControl\CacheManager;
use Application\CacheControl\CacheManagerException;
use AppFrameworkTestClasses\ApplicationTestCase;
use AppFrameworkTestClasses\Stubs\CacheControl\StubCacheLocation;
use AppFrameworkTestClasses\Stubs\CacheControl\StubRegeneratingCacheLocation;
use Exception;

/**
 * Tests the {@see CacheManager} "regenerate in place" behavior for
 * {@see \Application\CacheControl\RegeneratingCacheLocationInterface}
 * locations, via {@see CacheManager::clearLocation()} and
 * {@see CacheManager::clearLocations()}.
 */
final class CacheManagerClearTest extends ApplicationTestCase
{
    private function getManager(): CacheManager
    {
        return CacheManager::getInstance();
    }

    // region: Single location clearing

    public function test_plainLocationIsCleared(): void
    {
        $location = new StubCacheLocation();

        $this->getManager()->clearLocation($location);

        $this->assertTrue($location->wasCleared());
    }

    public function test_regeneratingLocationIsRegeneratedNotCleared(): void
    {
        $location = new StubRegeneratingCacheLocation();

        $this->getManager()->clearLocation($location);

        $this->assertTrue($location->wasRegenerated());
        $this->assertFalse($location->wasCleared());
    }

    // endregion

    // region: Batch clearing and ordering

    public function test_regenerationRunsAfterAllClears(): void
    {
        $callOrder = array();

        $plainA = new StubCacheLocation('plain-a', $callOrder);
        $regenerating = new StubRegeneratingCacheLocation('regen-a', $callOrder);
        $plainB = new StubCacheLocation('plain-b', $callOrder);

        $locations = array($plainA, $regenerating, $plainB);

        $this->getManager()->clearLocations($locations);

        $this->assertTrue($plainA->wasCleared());
        $this->assertTrue($plainB->wasCleared());
        $this->assertTrue($regenerating->wasRegenerated());
        $this->assertFalse($regenerating->wasCleared());

        // Both plain clears must have happened before the
        // regeneration, regardless of registration order.
        $this->assertSame(
            array('plain-a:clear', 'plain-b:clear', 'regen-a:regenerate'),
            $callOrder
        );
    }

    // endregion

    // region: Failure handling

    public function test_regenerationFailureThrowsWrappedException(): void
    {
        $original = new Exception('Simulated regeneration failure');
        $location = (new StubRegeneratingCacheLocation('regen-fail'))
            ->failRegenerationWith($original);

        try {
            $this->getManager()->clearLocation($location);
            $this->fail('Expected a CacheManagerException to be thrown.');
        } catch (CacheManagerException $e) {
            $this->assertSame(CacheManagerException::ERROR_REGENERATION_FAILED, $e->getCode());
            $this->assertSame($original, $e->getPrevious());
            $this->assertStringContainsString('regen-fail', $e->getMessage());
        }
    }

    public function test_clearLocationRegenerationFailureThrowsWrapped(): void
    {
        $original = new Exception('Simulated failure');
        $location = (new StubRegeneratingCacheLocation('regen-single-fail'))
            ->failRegenerationWith($original);

        $this->expectException(CacheManagerException::class);
        $this->expectExceptionCode(CacheManagerException::ERROR_REGENERATION_FAILED);

        $this->getManager()->clearLocation($location);
    }

    public function test_regenerationFailureDoesNotStopOthers(): void
    {
        $original = new Exception('Simulated failure');

        $plain = new StubCacheLocation('plain-c');
        $failing = (new StubRegeneratingCacheLocation('regen-failing'))
            ->failRegenerationWith($original);
        $succeeding = new StubRegeneratingCacheLocation('regen-succeeding');

        try {
            $this->getManager()->clearLocations(array($plain, $failing, $succeeding));
            $this->fail('Expected a CacheManagerException to be thrown.');
        } catch (CacheManagerException $e) {
            $this->assertSame(CacheManagerException::ERROR_REGENERATION_FAILED, $e->getCode());
            $this->assertStringContainsString('regen-failing', $e->getMessage());
        }

        // Despite the failure, the other locations still ran.
        $this->assertTrue($plain->wasCleared());
        $this->assertTrue($succeeding->wasRegenerated());
    }

    // endregion
}
