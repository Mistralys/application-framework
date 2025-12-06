<?php

declare(strict_types=1);

namespace Application\Countries\API\ParamSets;

use Application\API\Parameters\Rules\CustomParamSetInterface;
use Application\Countries\API\AppCountryAPIInterface;
use Application_Countries_Country;

/**
 * @method AppCountryAPIInterface getMethod()
 */
interface AppCountryParamSetInterface extends CustomParamSetInterface
{
    public function getCountry() : ?Application_Countries_Country;
}
