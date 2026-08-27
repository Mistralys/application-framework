<?php
/**
 * @package API Clients
 * @subpackage API Methods
 */

declare(strict_types=1);

namespace Application\API\Clients\API;

use Application\API\APIMethodInterface;
use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Clients\API\Params\APIKeyHandler;

/**
 * Interface for API methods that require an API Key.
 *
 * > NOTE: The API Key parameter is always required and cannot be made optional.
 * > Additionally, it is automatically registered as soon as an API method
 * > implements this interface (see {@see BaseAPIMethod::initReservedParams()}).
 *
 * @package API Clients
 * @subpackage API Methods
 *
 * @see APIKeyMethodTrait
 */
interface APIKeyMethodInterface extends APIMethodInterface
{
    public const string API_KEY_PARAM_NAME = 'apiKey';

    public function manageParamAPIKey() : APIKeyHandler;

    /**
     * Returns the user right required to call this API method,
     * or `null` if no specific right is required.
     *
     * This declaration is **mandatory** — every implementing class
     * must provide an explicit return value. The `APIKeyMethodTrait`
     * does not supply a default. Return `null` explicitly when no
     * right is required; this is a visible, reviewable decision.
     *
     * When a non-null value is returned, the framework satisfies
     * the right from the API key's method grants via
     * `APIKeyRights::satisfies()` (not from the pseudo user).
     *
     * **Override contract:** Overrides must only **strengthen** the
     * right declaration. Returning `null` where a parent returns a
     * non-null right bypasses the authorization check.
     *
     * @return string|null The right name, or `null` if no right is required.
     */
    public function getRequiredRight() : ?string;
}
