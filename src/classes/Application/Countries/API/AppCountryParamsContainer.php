<?php

declare(strict_types=1);

namespace Application\Countries\API;

use Application\API\Parameters\Handlers\BaseParamsHandlerContainer;
use Application\Countries\API\Params\AppCountryIDHandler;
use Application\Countries\API\Params\AppCountryISOHandler;
use Application\Countries\API\ParamSets\AppCountryRuleHandler;
use Application_Countries_Country;
use AppUtils\ClassHelper;

/**
 * @method AppCountryAPIInterface getMethod()
 */
class AppCountryParamsContainer extends BaseParamsHandlerContainer
{
    public function __construct(AppCountryAPIInterface $method)
    {
        parent::__construct($method);
    }

    public function resolveValue(): ?Application_Countries_Country
    {
        $value = parent::resolveValue();

        if($value instanceof Application_Countries_Country) {
            return $value;
        }

        return null;
    }

    public function requireValue(): Application_Countries_Country
    {
        return ClassHelper::requireObjectInstanceOf(
            Application_Countries_Country::class,
            parent::requireValue()
        );
    }

    public function selectAppCountry(Application_Countries_Country $country) : self
    {
        return $this->selectValue($country);
    }

    protected function isValidValueType(float|object|array|bool|int|string $value): bool
    {
        return $value instanceof Application_Countries_Country;
    }

    private ?AppCountryIDHandler $countryIDHandler = null;

    public function manageID() : AppCountryIDHandler
    {
        if(!isset($this->countryIDHandler)) {
            $this->countryIDHandler = new AppCountryIDHandler($this->getMethod());
            $this->registerHandler($this->countryIDHandler);
        }

        return $this->countryIDHandler;
    }

    private ?AppCountryISOHandler $countryISOHandler = null;

    public function manageISO() : AppCountryISOHandler
    {
        if(!isset($this->countryISOHandler)) {
            $this->countryISOHandler = new AppCountryISOHandler($this->getMethod());
            $this->registerHandler($this->countryISOHandler);
        }

        return $this->countryISOHandler;
    }

    private ?AppCountryRuleHandler $countryRuleHandler = null;

    public function manageAllParamsRule() : AppCountryRuleHandler
    {
        if(!isset($this->countryRuleHandler)) {
            $this->countryRuleHandler = new AppCountryRuleHandler($this->getMethod());
            $this->registerHandler($this->countryRuleHandler);
        }

        return $this->countryRuleHandler;
    }
}
