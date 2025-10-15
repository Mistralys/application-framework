<?php

declare(strict_types=1);

namespace Application\Countries\API\ParamSets;

use Application\Countries\API\AppCountryIDParam;
use Application_Countries_Country;

class CountryIDSet extends BaseAppCountryParamSet
{
    public const string SET_NAME = 'CountryID';

    private AppCountryIDParam $param;

    public function getCountry(): ?Application_Countries_Country
    {
        return $this->param->getCountry();
    }

    protected function initParams(): void
    {
        $this->param = $this->getMethod()->registerAppCountryID();
        $this->registerParam($this->param);
    }

    protected function _getID(): string
    {
        return self::SET_NAME;
    }
}
