<?php

declare(strict_types=1);

namespace Application\Countries\API\Params;

use Application\API\Parameters\Handlers\BaseParamHandler;
use Application_Countries_Country;
use AppUtils\ClassHelper;

class AppCountryISOHandler extends BaseParamHandler
{
    protected function resolveValueFromSubject(): ?Application_Countries_Country
    {
        return $this->getParam()?->getCountry();
    }

    public function register(): AppCountryISOParam
    {
        return ClassHelper::requireObjectInstanceOf(
            AppCountryISOParam::class,
            parent::register()
        );
    }

    public function getParam(): ?AppCountryISOParam
    {
        $param = parent::getParam();
        if ($param instanceof AppCountryISOParam) {
            return $param;
        }

        return null;
    }

    protected function createParam(): AppCountryISOParam
    {
        return new AppCountryISOParam();
    }
}
