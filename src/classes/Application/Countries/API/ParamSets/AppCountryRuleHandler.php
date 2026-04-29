<?php
/**
 * @package Countries
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Countries\API\ParamSets;

use Application\API\Parameters\Handlers\BaseRuleHandler;
use Application\Countries\API\AppCountryAPIInterface;
use Application_Countries_Country;

/**
 * Bridges the {@see AppCountryParamRule} into the container's handler
 * architecture.
 *
 * Provides type-narrowed overrides for {@see resolveValue()} (returning
 * `?Application_Countries_Country`) and {@see getRule()} (returning
 * `?AppCountryParamRule`) so consumers receive correctly-typed values
 * without casting.
 *
 * Mirrors {@see AppCountriesRuleHandler} (plural) for pattern consistency.
 *
 * @package Countries
 * @subpackage API
 *
 * @method AppCountryAPIInterface getMethod()
 */
class AppCountryRuleHandler extends BaseRuleHandler
{
    public function __construct(AppCountryAPIInterface $method)
    {
        parent::__construct($method);
    }

    /**
     * Returns the resolved country, or `null` if none could be resolved.
     *
     * @return Application_Countries_Country|null
     */
    public function resolveValue(): ?Application_Countries_Country
    {
        $value = parent::resolveValue();

        if($value instanceof Application_Countries_Country) {
            return $value;
        }

        return null;
    }

    /**
     * Resolves the country from the underlying OrRule's valid parameter set,
     * or `null` if no set matched.
     *
     * @return Application_Countries_Country|null
     */
    protected function resolveValueFromSubject(): ?Application_Countries_Country
    {
        return $this->getRule()?->getCountry();
    }

    /**
     * Returns the underlying {@see AppCountryParamRule}, or `null` if
     * the rule has not been registered yet.
     *
     * @return AppCountryParamRule|null
     */
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
