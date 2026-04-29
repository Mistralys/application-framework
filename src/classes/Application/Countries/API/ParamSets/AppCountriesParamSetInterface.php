<?php
/**
 * @package Countries
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Countries\API\ParamSets;

use Application\API\Parameters\Rules\CustomParamSetInterface;
use Application\Countries\API\AppCountriesAPIInterface;
use Application_Countries_Country;

/**
 * Contract for parameter sets that resolve to an array of countries.
 *
 * Mirrors {@see AppCountryParamSetInterface} (singular) for pattern consistency.
 *
 * @package Countries
 * @subpackage API
 * @see BaseAppCountriesParamSet
 *
 * @method AppCountriesAPIInterface getMethod()
 */
interface AppCountriesParamSetInterface extends CustomParamSetInterface
{
    /**
     * Returns the resolved country objects for the parameter set's current value.
     *
     * @return Application_Countries_Country[]
     */
    public function getCountries(): array;
}
