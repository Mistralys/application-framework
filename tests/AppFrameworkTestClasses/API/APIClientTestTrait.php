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
     * Creates a test API key, grants it access to the specified method, and
     * assigns the given rights to its pseudo-user.
     *
     * Convenience wrapper that eliminates the three-step
     * {@see createTestAPIKey()} + {@see APIKeyMethods::addMethod()} +
     * {@see APIKeyPseudoUser::setRights()} boilerplate for tests that need
     * both method access and user-right authorization.
     *
     * @param string $methodName The API method name to grant (e.g. TestAPIKeyMethodWithRight::METHOD_NAME).
     * @param string[] $rights Right names to assign to the pseudo-user (e.g. array(TestAPIKeyMethodWithRight::TEST_RIGHT)).
     * @return APIKeyRecord
     */
    public function createTestAPIKeyWithRights(string $methodName, array $rights) : APIKeyRecord
    {
        $key = $this->createTestAPIKeyForMethod($methodName);
        $key->getPseudoUser()->setRights($rights);
        return $key;
    }
}
