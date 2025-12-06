<?php

declare(strict_types=1);

namespace Application\Countries\API\Params;

use Application\API\Parameters\Handlers\BaseParamHandler;
use Application_Countries_Country;
use AppUtils\ClassHelper;

class AppCountryIDHandler extends BaseParamHandler
{
    protected function resolveValueFromSubject(): ?Application_Countries_Country
    {
        return $this->getParam()?->getCountry();
    }

    public function register(): AppCountryIDParam
    {
        return ClassHelper::requireObjectInstanceOf(
            AppCountryIDParam::class,
            parent::register()
        );
    }

    public function getParam(): ?AppCountryIDParam
    {
        $param = parent::getParam();
        if ($param instanceof AppCountryIDParam) {
            return $param;
        }

        return null;
    }

    protected function createParam(): AppCountryIDParam
    {
        return new AppCountryIDParam();
    }
}
