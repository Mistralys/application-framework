<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\API;

use Application\API\Clients\Keys\APIKeyRecord;
use Application\AppFactory;

/**
 * @see APIClientTestInterface
 */
trait APIClientTestTrait
{
    public function createTestAPIKey() : APIKeyRecord
    {
        $counter = $this->getTestCounter('api-client');

        return AppFactory::createAPIClients()->createNewClient(
            'Test API Client #'.$counter,
            'API-CLIENT-'.$counter
        )
            ->createNewAPIKey(
                'Test API Key #'.$this->getTestCounter('api-key'),
                $this->createTestUser()
            );
    }

    /**
     * Creates a test API key and grants it access to the specified method.
     *
     * Convenience wrapper around {@see createTestAPIKey()} that eliminates
     * the manual {@see APIKeyMethods::addMethod()} boilerplate.
     *
     * @param string $methodName The API method name to grant (e.g. TestAPIKeyMethod::METHOD_NAME).
     * @return APIKeyRecord
     */
    public function createTestAPIKeyForMethod(string $methodName) : APIKeyRecord
    {
        $key = $this->createTestAPIKey();
        $key->getMethods()->addMethod($methodName);
        return $key;
    }

    /**
     * Creates a test API key and grants it access to the specified method.
     *
     * The `$rights` parameter is accepted for backward compatibility but
     * has no effect: authorization now derives from method grants via
     * {@see \Application\API\Clients\Keys\APIKeyRights::satisfies()}, not
     * from pseudo-user rights. Prefer {@see createTestAPIKeyForMethod()}.
     *
     * @param string $methodName The API method name to grant.
     * @param string[] $rights Ignored — kept for signature compatibility with downstream call sites.
     * @return APIKeyRecord
     * @deprecated Use {@see createTestAPIKeyForMethod()} instead.
     */
    public function createTestAPIKeyWithRights(string $methodName, array $rights) : APIKeyRecord
    {
        return $this->createTestAPIKeyForMethod($methodName);
    }
}
