<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\API;

use AppFrameworkTestClasses\ApplicationTestCaseInterface;
use Application\API\Clients\Keys\APIKeyRecord;

/**
 * @see APIClientTestTrait
 */
interface APIClientTestInterface extends ApplicationTestCaseInterface
{
    public function createTestAPIKey() : APIKeyRecord;
}
