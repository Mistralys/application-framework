<?php
/**
 * @package Countries
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Countries\API;

use Application\API\Parameters\APIParameterInterface;
use Application_Countries_Country;

/**
 * Interface for API parameters that represent a country.
 *
 * @package Countries
 * @subpackage API
 */
interface AppCountryParamInterface extends APIParameterInterface
{
    public function getCountry() : ?Application_Countries_Country;
}
