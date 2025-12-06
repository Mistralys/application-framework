<?php
/**
 * @package Countries
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Countries\API\ParamSets;

use Application\API\Parameters\ParamSetInterface;
use Application\API\Parameters\Rules\Type\OrRule;
use Application\Countries\API\AppCountryAPIInterface;
use Application\Countries\API\CountryAPIException;
use Application_Countries_Country;

/**
 * Custom rule that combines all parameter sets that can be used
 * to resolve a specific country.
 *
 * @package Countries
 * @subpackage API
 *
 * @method AppCountryParamSetInterface|NULL getValidSet()
 * @method AppCountryParamSetInterface requireValidSet()
 */
class AppCountryParamRule extends OrRule
{
    public function __construct(AppCountryAPIInterface $method)
    {
        parent::__construct('Selecting the country');

        $this
            ->addSet(new CountryIDSet($method))
            ->addSet(new CountryISOSet($method));
    }

    /**
     * @param AppCountryParamSetInterface|ParamSetInterface $set Only {@see AppCountryParamSetInterface} instances are allowed.
     * @return $this
     */
    public function addSet(AppCountryParamSetInterface|ParamSetInterface $set): self
    {
        if($set instanceof AppCountryParamSetInterface) {
            return parent::addSet($set);
        }

        throw new CountryAPIException(
            'Not a country API parameter set.',
            sprintf(
                'The param set is of type %s, but must implement %s.',
                get_class($set),
                AppCountryParamSetInterface::class
            ),
            CountryAPIException::INVALID_PARAM_SET
        );
    }

    public function getCountry() : ?Application_Countries_Country
    {
        return $this->getValidSet()?->getCountry();
    }
}
