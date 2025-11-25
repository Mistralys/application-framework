<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Parameters;

use Application\API\APIManager;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;
use TestDriver\API\TestAPIKeyMethod;

final class APIKeyParameterTest extends APITestCase
{
    public function test_selectKeyManually() : void
    {
        $method = new TestAPIKeyMethod(APIManager::getInstance());

        $key = $this->createTestAPIKey();

        $method->manageParamAPIKey()->selectKey($key);

        $this->assertSuccessfulResponse($method);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->startTransaction();
    }
}