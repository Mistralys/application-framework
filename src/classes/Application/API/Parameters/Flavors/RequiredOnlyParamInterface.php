<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\Flavors;

use Application\API\Parameters\APIParameterInterface;

/**
 * Interface for API parameters that are always required
 * and cannot be made optional.
 *
 * In the documentation, this parameter will be marked
 * as required, even if parameters are, on principle,
 * never marked as required.
 *
 * @package API
 * @subpackage Parameters
 */
interface RequiredOnlyParamInterface extends APIParameterInterface
{

}
