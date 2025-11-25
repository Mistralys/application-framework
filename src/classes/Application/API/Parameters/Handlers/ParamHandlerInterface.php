<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\Handlers;

use Application\API\Clients\API\Params\APIKeyHandler;
use Application\API\Parameters\APIParameterInterface;

/**
 * Interface for parameter handler classes that manage API parameters:
 * To handle the complex scenarios of parameter registration, value selection,
 * and resolution, parameter handlers provide a consistent interface to manage
 * these tasks.
 *
 * Instead of directly manipulating parameters and implementing intricate
 * logic within the API methods directly, these handlers encapsulate the
 * necessary functionality on a per-parameter basis.
 *
 * ## Usage
 *
 * See the abstract class {@see BaseParamHandler} for a base implementation
 * that can be extended to create specific parameter handlers. As an example,
 * look at {@see APIKeyHandler} on best practices for implementing a parameter
 * handler.
 *
 * The value handling methods are intentionally generic to accommodate a wide
 * variety of parameter types and resolution strategies. Ideally, your handler
 * class should guarantee and document the expected types for selected and
 * resolved values.
 *
 * @package API
 * @subpackage Parameters
 */
interface ParamHandlerInterface extends APIHandlerInterface
{
    /**
     * Registers the parameter with the API method's parameters collection.
     * @return APIParameterInterface
     */
    public function register() : APIParameterInterface;

    /**
     * Gets the parameter instance managed by this handler.
     *
     * > NOTE: The parameter is only returned if it has been registered.
     *
     * @return APIParameterInterface|null
     */
    public function getParam() : ?APIParameterInterface;
}
