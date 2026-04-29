<?php
/**
 * @package Countries
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Countries\API\Params;

use Application\API\Parameters\Type\IDListParameter;
use Application\AppFactory;
use Application\Countries\API\AppCountriesAPIInterface;
use Application_Countries_Country;

/**
 * Country IDs parameter for the multi-country Countries API.
 *
 * Accepts one or more application country IDs as a comma-separated
 * string or array. Each ID is validated individually via
 * {@see AppCountryIDsValidation}, which produces a per-ID error message
 * identifying any IDs that do not exist.
 *
 * @package Countries
 * @subpackage API
 * @see AppCountryIDsHandler
 * @see AppCountriesParamInterface
 */
class AppCountryIDsParam extends IDListParameter implements AppCountriesParamInterface
{
    public function __construct()
    {
        parent::__construct(AppCountriesAPIInterface::PARAM_COUNTRY_IDS, 'Country IDs');

        $this
            ->setDescription('One or more application country IDs, as a comma-separated list or array.')
            ->validateBy(new AppCountryIDsValidation());
    }

    /**
     * Returns the resolved country objects for each ID in the parameter value.
     *
     * Returns an empty array if the parameter has no value.
     *
     * @return Application_Countries_Country[]
     */
    public function getCountries(): array
    {
        $ids = $this->getValue();

        if($ids === null || empty($ids)) {
            return array();
        }

        $result = array();
        $countries = AppFactory::createCountries();

        foreach($ids as $id) {
            $result[] = $countries->getCountryByID($id);
        }

        return $result;
    }
}
