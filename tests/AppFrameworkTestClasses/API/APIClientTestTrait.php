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
}
