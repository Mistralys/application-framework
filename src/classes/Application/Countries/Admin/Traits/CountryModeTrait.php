<?php

declare(strict_types=1);

namespace Application\Countries\Admin\Traits;

use Application\AppFactory;
use Application\Countries\Admin\Screens\CountriesArea;
use Application_Countries;

trait CountryModeTrait
{
    protected function createCollection(): Application_Countries
    {
        return AppFactory::createCountries();
    }

    public function getParentScreenClass() : string
    {
        return CountriesArea::class;
    }
}
