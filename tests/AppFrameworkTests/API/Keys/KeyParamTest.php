<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Keys;

use Application\API\APIManager;
use AppUtils\RequestHelper;
use Mistralys\AppFrameworkTests\TestClasses\APIClientTestCase;
use ReflectionProperty;
use TestDriver\API\TestAPIKeyMethod;

final class KeyParamTest extends APIClientTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // RequestHelper::getBearerToken() memoizes its result in a private
        // static property for the lifetime of the process. Any earlier test
        // that triggers a call to it (e.g. iterating API methods without an
        // Authorization header) permanently poisons the cache for the rest
        // of the suite. Reset it here so this test is independent of run order.
        $cache = new ReflectionProperty(RequestHelper::class, 'cachedBearerToken');
        $cache->setValue(null, null);
    }

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
