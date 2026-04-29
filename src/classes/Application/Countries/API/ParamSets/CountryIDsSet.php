<?php
/**
 * @package Countries
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Countries\API\ParamSets;

use Application\Countries\API\Params\AppCountryIDsParam;
use Application_Countries_Country;

/**
 * Parameter set for the multi-country OrRule that resolves countries by their IDs.
 *
 * Registers the {@see AppCountryIDsParam} via the container's
 * {@see \Application\Countries\API\AppCountriesParamsContainer::manageIDs()} handler
 * and exposes the resolved list of countries via {@see getCountries()}.
 *
 * Mirrors {@see CountryIDSet} (singular) for pattern consistency.
 *
 * @package Countries
 * @subpackage API
 * @see AppCountriesParamRule
 */
class CountryIDsSet extends BaseAppCountriesParamSet
{
    public const string SET_NAME = 'CountryIDs';

    private AppCountryIDsParam $param;

    /**
     * @return Application_Countries_Country[]
     */
    public function getCountries(): array
    {
        return $this->param->getCountries();
    }

    protected function initParams(): void
    {
        $this->param = $this->getMethod()->manageAppCountriesParams()->manageIDs()->register();
        $this->registerParam($this->param);
    }

    protected function _getID(): string
    {
        return self::SET_NAME;
    }
}
