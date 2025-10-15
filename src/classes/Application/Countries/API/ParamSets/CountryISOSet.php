<?php

declare(strict_types=1);

namespace Application\Countries\API\ParamSets;

use Application\Countries\API\AppCountryISOParam;
use Application_Countries_Country;

class CountryISOSet extends BaseAppCountryParamSet
{
    public const string SET_NAME = 'CountryISO';

    private AppCountryISOParam $param;

    public function getCountry(): ?Application_Countries_Country
    {
        return $this->param->getCountry();
    }

    protected function initParams(): void
    {
        $this->param = $this->getMethod()->registerAppCountryISO();
        $this->registerParam($this->param);
    }

    protected function _getID(): string
    {
        return self::SET_NAME;
    }
}
