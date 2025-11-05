<?php
/**
 * @package Countries
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Countries\API;

use Application\API\Parameters\Type\StringParameter;
use Application\API\Parameters\ValueLookup\SelectableParamValue;
use Application\API\Parameters\ValueLookup\SelectableValueParamInterface;
use Application\API\Parameters\ValueLookup\SelectableValueParamTrait;
use Application\AppFactory;
use Application_Countries_Country;

/**
 * Country ISO code parameter for the Countries API.
 *
 * > NOTE: This implements {@see AppCountryParamInterface}
 * > to provide access to the resolved country object.
 *
 * @package Countries
 * @subpackage API
 */
class AppCountryISOParam extends StringParameter implements SelectableValueParamInterface, AppCountryParamInterface
{
    use SelectableValueParamTrait;

    public function __construct()
    {
        parent::__construct(AppCountryAPIInterface::PARAM_COUNTRY_ISO, 'Country ISO code');

        $this
            ->setDescription('Two-letter country ISO code, e.g. `de` for Germany. Case insensitive.')
            ->validateByValueExistsCallback(static function (mixed $value) : bool {
                if(is_string($value)) {
                    return AppFactory::createCountries()->isoExists($value);
                }
                return false;
            });
    }

    public function getCountry() : ?Application_Countries_Country
    {
        $value = $this->getValue();
        if ($value === null) {
            return null;
        }

        return AppFactory::createCountries()->getByISO($value);
    }

    protected function _getValues(): array
    {
        $result = array();
        foreach (AppFactory::createCountries()->getAll() as $country) {
            $result[] = new SelectableParamValue(
                $country->getISO(),
                sprintf('%s (%s)', strtoupper($country->getISO()), $country->getLabel())
            );
        }

        return $result;
    }

    public function getDefaultSelectableValue(): ?SelectableParamValue
    {
        return null;
    }
}
