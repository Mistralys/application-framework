<?php
/**
 * @package Countries
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Countries\API;

use Application\API\APIMethodInterface;
use Application\Countries\API\ParamSets\AppCountryParamRule;
use Application_Countries_Country;

/**
 * Interface for API methods that work with countries.
 *
 * @package Countries
 * @subpackage API
 * @see AppCountryAPITrait
 */
interface AppCountryAPIInterface extends APIMethodInterface
{
    public const string KEY_COUNTRY_ID = 'countryID';
    public const string KEY_COUNTRY_ISO = 'countryISO';

    public function getAppCountryIDParam() : ?AppCountryIDParam;
    public function getAppCountryISOParam() : ?AppCountryISOParam;
    public function registerAppCountryID() : AppCountryIDParam;
    public function registerAppCountryISO() : AppCountryISOParam;
    public function resolveAppCountry() : ?Application_Countries_Country;
    public function requireAppCountry() : Application_Countries_Country;
    public function getAppCountryParamRule() : ?AppCountryParamRule;
}
