<?php
/**
 * @package Countries
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Countries\API\ParamSets;

use Application\API\Parameters\Rules\BaseCustomParamSet;
use Application\Countries\API\AppCountryAPIInterface;

/**
 * Abstract base class for parameter sets that are used
 * to resolve a specific country. Implements the interface
 * {@see AppCountryParamSetInterface}.
 *
 * @package Countries
 * @subpackage API
 *
 * @method AppCountryAPIInterface getMethod()
 */
abstract class BaseAppCountryParamSet extends BaseCustomParamSet implements AppCountryParamSetInterface
{
    public function __construct(AppCountryAPIInterface $method)
    {
        parent::__construct($method);
    }
}
