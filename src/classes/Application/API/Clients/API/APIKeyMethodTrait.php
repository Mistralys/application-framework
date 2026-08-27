<?php

declare(strict_types=1);

namespace Application\API\Clients\API;

use Application\API\Clients\API\Params\APIKeyHandler;

/**
 * Provides the API key parameter handling for methods implementing
 * {@see APIKeyMethodInterface}. The right declaration contract is
 * defined on the interface — each method class must implement
 * {@see APIKeyMethodInterface::getRequiredRight()} directly.
 */
trait APIKeyMethodTrait
{
    private ?APIKeyHandler $apiKeyHandler = null;

    final public function manageParamAPIKey() : APIKeyHandler
    {
        if(!isset($this->apiKeyHandler)) {
            $this->apiKeyHandler = new APIKeyHandler($this);
        }

        return $this->apiKeyHandler;
    }
}
