<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Cache;

use Application\API\Cache\APICacheException;
use Application\API\Cache\APICacheManager;
use AppFrameworkTestClasses\ApplicationTestCase;

/**
 * Unit tests for {@see APICacheManager::getMethodCacheFolder()} input
 * validation: verifies that dangerous method names are rejected and
 * valid names are accepted.
 */
final class APICacheManagerValidationTest extends ApplicationTestCase
{
    // region: Invalid method names — must throw

    /**
     * An empty string must throw with ERROR_INVALID_METHOD_NAME.
     */
    public function test_emptyMethodNameThrows() : void
    {
        $this->expectException(APICacheException::class);
        $this->expectExceptionCode(APICacheException::ERROR_INVALID_METHOD_NAME);

        APICacheManager::getMethodCacheFolder('');
    }

    /**
     * A method name containing a forward slash must throw.
     */
    public function test_forwardSlashInMethodNameThrows() : void
    {
        $this->expectException(APICacheException::class);
        $this->expectExceptionCode(APICacheException::ERROR_INVALID_METHOD_NAME);

        APICacheManager::getMethodCacheFolder('foo/bar');
    }

    /**
     * A method name containing double dots must throw (path traversal).
     */
    public function test_doubleDotsInMethodNameThrows() : void
    {
        $this->expectException(APICacheException::class);
        $this->expectExceptionCode(APICacheException::ERROR_INVALID_METHOD_NAME);

        APICacheManager::getMethodCacheFolder('foo..bar');
    }

    /**
     * A path traversal component (../) must throw.
     */
    public function test_pathTraversalThrows() : void
    {
        $this->expectException(APICacheException::class);
        $this->expectExceptionCode(APICacheException::ERROR_INVALID_METHOD_NAME);

        APICacheManager::getMethodCacheFolder('../etc/passwd');
    }

    /**
     * A method name containing the OS directory separator must throw.
     * On Unix this is '/', already covered above. On Windows it is '\'.
     * This test explicitly uses DIRECTORY_SEPARATOR to cover non-Unix hosts.
     */
    public function test_directorySeparatorInMethodNameThrows() : void
    {
        if(DIRECTORY_SEPARATOR === '/')
        {
            // Forward-slash is already covered by test_forwardSlashInMethodNameThrows().
            // Mark as passed to keep the assertion count meaningful.
            $this->addToAssertionCount(1);
            return;
        }

        $this->expectException(APICacheException::class);
        $this->expectExceptionCode(APICacheException::ERROR_INVALID_METHOD_NAME);

        APICacheManager::getMethodCacheFolder('foo' . DIRECTORY_SEPARATOR . 'bar');
    }

    // endregion

    // region: Valid method names — must not throw

    /**
     * A simple alphanumeric method name must be accepted without throwing.
     */
    public function test_simpleAlphanumericMethodNameIsAccepted() : void
    {
        // Must not throw.
        $folder = APICacheManager::getMethodCacheFolder('GetTenantsAPI');

        $this->assertStringContainsString('GetTenantsAPI', $folder->getPath());
    }

    /**
     * A method name with hyphens and underscores must be accepted.
     */
    public function test_methodNameWithHyphensAndUnderscoresIsAccepted() : void
    {
        $folder = APICacheManager::getMethodCacheFolder('Get-Tenants_API');

        $this->assertStringContainsString('Get-Tenants_API', $folder->getPath());
    }

    // endregion
}
