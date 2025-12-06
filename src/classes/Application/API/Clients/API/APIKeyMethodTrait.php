<?php

declare(strict_types=1);

namespace Application\API\Clients\API;

use Application\API\Clients\API\Params\APIKeyHandler;

/**
 * @see APIKeyMethodInterface
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
