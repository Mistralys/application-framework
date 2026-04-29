<?php
/**
 * @package Countries
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Countries\API\Params;

use Application\API\Parameters\Type\StringListParameter;
use Application\AppFactory;
use Application\Countries\API\AppCountriesAPIInterface;
use Application_Countries_Country;

/**
 * Country ISO codes parameter for the multi-country Countries API.
 *
 * Accepts one or more two-letter country ISO codes as a comma-separated
 * string or array. Each ISO code is validated individually via
 * {@see AppCountryISOsValidation}, which produces a per-ISO error message
 * identifying any ISO codes that do not exist.
 *
 * @package Countries
 * @subpackage API
 * @see AppCountryISOsHandler
 * @see AppCountriesParamInterface
 */
class AppCountryISOsParam extends StringListParameter implements AppCountriesParamInterface
{
    public function __construct()
    {
        parent::__construct(AppCountriesAPIInterface::PARAM_COUNTRY_ISOS, 'Country ISO codes');

        $this
            ->setDescription('One or more two-letter country ISO codes (e.g. de, en, fr), as a comma-separated list or array. Case insensitive.')
            ->validateBy(new AppCountryISOsValidation());
    }

    /**
     * Returns the resolved country objects for each ISO code in the parameter value.
     *
     * Returns an empty array if the parameter has no value.
     *
     * @return Application_Countries_Country[]
     */
    public function getCountries(): array
    {
        $isos = $this->getValue();

        if($isos === null || empty($isos)) {
            return array();
        }

        $result = array();
        $countries = AppFactory::createCountries();

        foreach($isos as $iso) {
            $result[] = $countries->getByISO($iso);
        }

        return $result;
    }
}
