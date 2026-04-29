<?php
/**
 * @package Countries
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Countries\API\ParamSets;

use Application\API\Parameters\Rules\BaseCustomParamSet;
use Application\Countries\API\AppCountriesAPIInterface;

/**
 * Abstract base class for parameter sets that are used to resolve a list of
 * countries. Implements the interface {@see AppCountriesParamSetInterface}.
 *
 * Mirrors {@see BaseAppCountryParamSet} (singular) for pattern consistency.
 *
 * @package Countries
 * @subpackage API
 *
 * @method AppCountriesAPIInterface getMethod()
 */
abstract class BaseAppCountriesParamSet extends BaseCustomParamSet implements AppCountriesParamSetInterface
{
    public function __construct(AppCountriesAPIInterface $method)
    {
        parent::__construct($method);
    }
}
