<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\Rules;

use Application\API\APIMethodInterface;
use Application\API\Parameters\ParamSetInterface;

/**
 * Interface for custom parameter sets that need access to their API method.
 * A base implementation is provided by {@see BaseCustomParamSet}.
 *
 * @package API
 * @subpackage Parameters
 */
interface CustomParamSetInterface extends ParamSetInterface
{
    public function getMethod() : APIMethodInterface;
}