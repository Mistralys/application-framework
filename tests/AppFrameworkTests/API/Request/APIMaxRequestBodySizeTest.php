<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Request;

use Application\ConfigSettings\AppConfig;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;

/**
 * Tests for {@see AppConfig::deriveAPIMaxRequestBodySize()} and
 * {@see AppConfig::getAPIMaxRequestBodySize()}, covering the default
 * derivation from the file-content size limit and the explicit override.
 *
 * {@see AppConfig::deriveAPIMaxRequestBodySize()} is a pure helper exercised
 * directly here with arbitrary inputs, since boot settings
 * ({@see \Application\ConfigSettings\BaseConfigRegistry::API_MAX_REQUEST_BODY_SIZE})
 * are fixed once the application has booted and cannot be lowered from a test.
 */
final class APIMaxRequestBodySizeTest extends APITestCase
{
    public function test_zeroDerivesFromFileLimit() : void
    {
        $this->assertSame(
            15029589,
            AppConfig::deriveAPIMaxRequestBodySize(0, 10485760)
        );
    }

    public function test_derivedCeilingFollowsRaisedFileLimit() : void
    {
        $raisedLimit = 20971520; // 20 MiB

        $this->assertSame(
            intdiv($raisedLimit * 4, 3) + AppConfig::API_REQUEST_BODY_OVERHEAD,
            AppConfig::deriveAPIMaxRequestBodySize(0, $raisedLimit)
        );
    }

    public function test_positiveValueIsReturnedAsIs() : void
    {
        $this->assertSame(
            123456,
            AppConfig::deriveAPIMaxRequestBodySize(123456, 10485760)
        );
    }

    public function test_bootedGetterUsesDerivation() : void
    {
        // The test application's booted defaults: API_MAX_REQUEST_BODY_SIZE
        // is left at its 0 default, so the ceiling is derived from the
        // booted API_MAX_FILE_CONTENT_SIZE default (10485760 / 10 MiB).
        $this->assertSame(15029589, AppConfig::getAPIMaxRequestBodySize());
    }
}
