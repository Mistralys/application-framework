<?php

declare(strict_types=1);

namespace Application\Countries\API\ParamSets;

use Application\API\Parameters\Handlers\BaseRuleHandler;
use Application\Countries\API\AppCountryAPIInterface;
use Application_Countries_Country;

/**
 * @method AppCountryAPIInterface getMethod()
 */
class AppCountryRuleHandler extends BaseRuleHandler
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

    protected function resolveValueFromSubject(): ?Application_Countries_Country
    {
        return $this->getRule()?->getCountry();
    }

    public function getRule(): ?AppCountryParamRule
    {
        $rule = parent::getRule();

        if ($rule instanceof AppCountryParamRule) {
            return $rule;
        }

        return null;
    }

    protected function createRule(): AppCountryParamRule
    {
        return new AppCountryParamRule($this->getMethod());
    }
}
