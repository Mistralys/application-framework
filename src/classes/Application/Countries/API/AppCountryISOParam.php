<?php

declare(strict_types=1);

namespace Application\Countries\API;

use Application\API\Parameters\Type\IntegerParameter;
use Application\API\Parameters\Type\StringParameter;
use Application\AppFactory;
use Application_Countries_Country;

class AppCountryISOParam extends StringParameter
{
    public function __construct()
    {
        parent::__construct(AppCountryAPIInterface::KEY_COUNTRY_ISO, 'Country ISO code');

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
}
