<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Keys;

use Application\API\APIManager;
use Application\API\Clients\API\APIKeyMethodInterface;
use Mistralys\AppFrameworkTests\TestClasses\APIClientTestCase;
use TestDriver\API\TestAPIKeyMethod;

final class KeyParamTest extends APIClientTestCase
{
    public function test_getValue() : void
    {
        $key = $this->createTestAPIKey();

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer '.$key->getAPIKey();

        $method = new TestAPIKeyMethod(APIManager::getInstance());

        $this->assertSame(
            $key,
            $method->manageParamAPIKey()->getKey()
        );
    }
}
