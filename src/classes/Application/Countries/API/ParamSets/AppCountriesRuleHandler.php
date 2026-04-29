<?php
/**
 * @package Countries
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Countries\API\ParamSets;

use Application\API\Parameters\Handlers\BaseRuleHandler;
use Application\Countries\API\AppCountriesAPIInterface;
use Application_Countries_Country;

/**
 * Bridges the {@see AppCountriesParamRule} into the container's handler
 * architecture.
 *
 * Provides type-narrowed overrides for {@see resolveValue()} (returning
 * `Application_Countries_Country[]`) and {@see getRule()} (returning
 * `?AppCountriesParamRule`) so consumers receive correctly-typed values
 * without casting.
 *
 * Mirrors {@see AppCountryRuleHandler} (singular) for pattern consistency.
 *
 * @package Countries
 * @subpackage API
 *
 * @method AppCountriesAPIInterface getMethod()
 */
class AppCountriesRuleHandler extends BaseRuleHandler
{
    public function __construct(AppCountriesAPIInterface $method)
    {
        parent::__construct($method);
    }

    /**
     * Returns the resolved list of countries, or an empty array if none
     * could be resolved.
     *
     * @return Application_Countries_Country[]
     */
    public function resolveValue(): array
    {
        $value = parent::resolveValue();

        if(is_array($value)) {
            return $value;
        }

        return array();
    }

    /**
     * Resolves the list of countries from the underlying OrRule's valid
     * parameter set.
     *
     * Returns `null` when no rule has been registered or the rule
     * resolves no countries, so that the
     * {@see BaseParamsHandlerContainer} "first non-null wins"
     * iteration can fall through to the next handler.
     *
     * @return Application_Countries_Country[]|null
     */
    protected function resolveValueFromSubject(): ?array
    {
        $rule = $this->getRule();

        if($rule === null) {
            return null;
        }

        $countries = $rule->getCountries();

        return empty($countries) ? null : $countries;
    }

    /**
     * Returns the underlying {@see AppCountriesParamRule}, or `null` if
     * the rule has not been registered yet.
     *
     * @return AppCountriesParamRule|null
     */
    public function getRule(): ?AppCountriesParamRule
    {
        $rule = parent::getRule();

        if($rule instanceof AppCountriesParamRule) {
            return $rule;
        }

        return null;
    }

    protected function createRule(): AppCountriesParamRule
    {
        return new AppCountriesParamRule($this->getMethod());
    }
}
