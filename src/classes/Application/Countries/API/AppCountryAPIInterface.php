<?php
/**
 * @package Countries
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Countries\API;

use Application\API\APIMethodInterface;

/**
 * Interface for API methods that work with countries.
 *
 * @package Countries
 * @subpackage API
 * @see AppCountryAPITrait
 */
interface AppCountryAPIInterface extends APIMethodInterface
{
    public const string PARAM_COUNTRY_ID = 'countryID';
    public const string PARAM_COUNTRY_ISO = 'countryISO';
    public const string KEY_COUNTRY_ID = 'countryID';
    public const string KEY_COUNTRY_ISO = 'isoCode';

    public function manageAppCountryParams() : AppCountryParamsContainer;
}
