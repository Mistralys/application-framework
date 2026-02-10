<?php

declare(strict_types=1);

namespace Application\Countries\Admin\Traits;

use Application\Countries\Admin\CountryRequestType;

/**
 * @see CountryRequestInterface
 */
trait CountryRequestTrait
{
    private ?CountryRequestType $countryRequest = null;

    public function getCountryRequest() : CountryRequestType
    {
        if(!isset($this->countryRequest)) {
            $this->countryRequest = new CountryRequestType($this);
        }

        return $this->countryRequest;
    }
}
