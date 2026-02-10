<?php

declare(strict_types=1);

namespace Application\Countries\Admin\Traits;

use Application\Countries\Admin\CountryRequestType;
use Application\Interfaces\Admin\AdminScreenInterface;

interface CountryRequestInterface extends AdminScreenInterface
{
    public function getCountryRequest() : CountryRequestType;
}
