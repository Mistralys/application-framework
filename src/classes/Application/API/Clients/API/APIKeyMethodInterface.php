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
}
