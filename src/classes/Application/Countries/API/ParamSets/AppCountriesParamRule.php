<?php
/**
 * @package Countries
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Countries\API\ParamSets;

use Application\API\Parameters\ParamSetInterface;
use Application\API\Parameters\Rules\Type\OrRule;
use Application\Countries\API\AppCountriesAPIInterface;
use Application\Countries\API\CountryAPIException;
use Application_Countries_Country;

/**
 * Custom rule that combines all parameter sets that can be used to resolve
 * a list of countries.
 *
 * Enforces mutual exclusivity between {@see CountryIDsSet} and
 * {@see CountryISOsSet} via {@see OrRule}: callers must provide
 * `countryIDs` **or** `countryISOs`, not both.
 *
 * The {@see addSet()} override ensures only {@see AppCountriesParamSetInterface}
 * instances are accepted, throwing {@see CountryAPIException} otherwise.
 *
 * Mirrors {@see AppCountryParamRule} (singular) for pattern consistency.
 *
 * @package Countries
 * @subpackage API
 *
 * @method AppCountriesParamSetInterface|NULL getValidSet()
 * @method AppCountriesParamSetInterface requireValidSet()
 */
class AppCountriesParamRule extends OrRule
{
    public function __construct(AppCountriesAPIInterface $method)
    {
        parent::__construct('Selecting countries');

        $this
            ->addSet(new CountryIDsSet($method))
            ->addSet(new CountryISOsSet($method));
    }

    /**
     * Adds a parameter set to the rule.
     *
     * Only {@see AppCountriesParamSetInterface} instances are accepted.
     * Passing any other {@see ParamSetInterface} implementation throws
     * a {@see CountryAPIException}.
     *
     * @param AppCountriesParamSetInterface|ParamSetInterface $set Only {@see AppCountriesParamSetInterface} instances are allowed.
     * @return $this
     * @throws CountryAPIException When $set does not implement {@see AppCountriesParamSetInterface}.
     */
    public function addSet(AppCountriesParamSetInterface|ParamSetInterface $set): self
    {
        if($set instanceof AppCountriesParamSetInterface) {
            return parent::addSet($set);
        }

        throw new CountryAPIException(
            'Not a countries API parameter set.',
            sprintf(
                'The param set is of type %s, but must implement %s.',
                get_class($set),
                AppCountriesParamSetInterface::class
            ),
            CountryAPIException::INVALID_PARAM_SET
        );
    }

    /**
     * Returns the resolved list of countries from the valid parameter set,
     * or an empty array if no valid set was matched.
     *
     * @return Application_Countries_Country[]
     */
    public function getCountries(): array
    {
        $set = $this->getValidSet();

        if($set instanceof AppCountriesParamSetInterface) {
            return $set->getCountries();
        }

        return array();
    }
}
