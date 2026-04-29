<?php
/**
 * @package Countries
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Countries\API;

use Application\API\APIMethodInterface;

/**
 * Interface for API methods that work with multiple countries.
 *
 * Complements the singular {@see AppCountryAPIInterface}: use this when an API
 * method must accept a list of countries (by IDs or ISO codes). The two traits
 * can coexist on the same method if needed.
 *
 * @package Countries
 * @subpackage API
 * @see AppCountriesAPITrait
 */
interface AppCountriesAPIInterface extends APIMethodInterface
{
    public const string PARAM_COUNTRY_IDS = 'countryIDs';
    public const string PARAM_COUNTRY_ISOS = 'countryISOs';

    public function manageAppCountriesParams(): AppCountriesParamsContainer;
}
