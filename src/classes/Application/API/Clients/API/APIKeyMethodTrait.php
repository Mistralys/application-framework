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

    /**
     * Returns the user right required to call this API method, or `null` if
     * no specific right is required (default).
     *
     * Override this method in a concrete API method class to declare the right
     * the API key's user must hold before the method is executed.
     *
     * **Override contract:** Overrides must only **strengthen** the right
     * declaration — returning `null` when a parent returns a non-null right
     * bypasses the user-rights check and must be avoided.
     *
     * @return string|null The right name, or `null` if no right is required.
     *
     * @see APIKeyMethodInterface::getRequiredRight()
     */
    public function getRequiredRight() : ?string
    {
        return null;
    }
}
