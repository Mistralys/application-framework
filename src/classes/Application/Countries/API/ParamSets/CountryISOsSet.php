<?php
/**
 * @package Countries
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Countries\API\ParamSets;

use Application\Countries\API\Params\AppCountryISOsParam;
use Application_Countries_Country;

/**
 * Parameter set for the multi-country OrRule that resolves countries by their ISO codes.
 *
 * Registers the {@see AppCountryISOsParam} via the container's
 * {@see \Application\Countries\API\AppCountriesParamsContainer::manageISOs()} handler
 * and exposes the resolved list of countries via {@see getCountries()}.
 *
 * Mirrors {@see CountryISOSet} (singular) for pattern consistency.
 *
 * @package Countries
 * @subpackage API
 * @see AppCountriesParamRule
 */
class CountryISOsSet extends BaseAppCountriesParamSet
{
    public const string SET_NAME = 'CountryISOs';

    private AppCountryISOsParam $param;

    /**
     * @return Application_Countries_Country[]
     */
    public function getCountries(): array
    {
        return $this->param->getCountries();
    }

    protected function initParams(): void
    {
        $this->param = $this->getMethod()->manageAppCountriesParams()->manageISOs()->register();
        $this->registerParam($this->param);
    }

    protected function _getID(): string
    {
        return self::SET_NAME;
    }
}
