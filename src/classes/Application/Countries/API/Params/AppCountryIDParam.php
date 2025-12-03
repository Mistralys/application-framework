<?php
/**
 * @package Countries
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Countries\API\Params;

use Application\API\Parameters\Type\IntegerParameter;
use Application\API\Parameters\ValueLookup\SelectableParamValue;
use Application\API\Parameters\ValueLookup\SelectableValueParamInterface;
use Application\API\Parameters\ValueLookup\SelectableValueParamTrait;
use Application\AppFactory;
use Application\Countries\API\AppCountryAPIInterface;
use Application_Countries_Country;

/**
 * Country ID parameter for the Countries API.
 *
 * > NOTE: This implements {@see AppCountryParamInterface}
 * > to provide access to the resolved country object.
 *
 * @package Countries
 * @subpackage API
 */
class AppCountryIDParam extends IntegerParameter implements SelectableValueParamInterface, AppCountryParamInterface
{
    use SelectableValueParamTrait;

    public function __construct()
    {
        parent::__construct(AppCountryAPIInterface::PARAM_COUNTRY_ID, 'App Country ID');

        $this
            ->setDescription('Application country ID.')
            ->validateByValueExistsCallback(static function (mixed $value) : bool {
                if(is_numeric($value)) {
                    return AppFactory::createCountries()->idExists((int)$value);
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

        return AppFactory::createCountries()->getCountryByID($value);
    }

    protected function _getValues(): array
    {
        $result = array();
        foreach (AppFactory::createCountries()->getAll() as $country) {
            $result[] = new SelectableParamValue(
                (string)$country->getID(),
                sprintf('#%s - %s (%s)', $country->getID(), strtoupper($country->getISO()), $country->getLabel())
            );
        }

        return $result;
    }

    public function getDefaultSelectableValue(): ?SelectableParamValue
    {
        return null;
    }
}
