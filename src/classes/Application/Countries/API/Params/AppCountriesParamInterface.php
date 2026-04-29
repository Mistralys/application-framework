<?php
/**
 * @package Countries
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Countries\API\Params;

use Application\API\Parameters\APIParameterInterface;
use Application_Countries_Country;

/**
 * Shared interface for API parameters that resolve to multiple countries.
 *
 * Mirrors {@see AppCountryParamInterface} (singular) for pattern consistency.
 *
 * @package Countries
 * @subpackage API
 * @see AppCountryIDsParam
 * @see AppCountryISOsParam
 */
interface AppCountriesParamInterface extends APIParameterInterface
{
    /**
     * Returns the resolved country objects for the parameter's current value.
     *
     * @return Application_Countries_Country[]
     */
    public function getCountries(): array;
}
