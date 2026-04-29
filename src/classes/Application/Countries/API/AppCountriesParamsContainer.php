<?php
/**
 * @package Countries
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Countries\API;

use Application\API\Parameters\Handlers\BaseParamsHandlerContainer;
use Application\Countries\API\Params\AppCountryIDsHandler;
use Application\Countries\API\Params\AppCountryISOsHandler;
use Application\Countries\API\ParamSets\AppCountriesRuleHandler;
use Application_Countries_Country;

/**
 * Parameters container for API methods that work with multiple countries.
 *
 * Manages three handlers (IDs, ISOs, OrRule) and resolves to an array of
 * country records. Complements the singular {@see AppCountryParamsContainer}.
 *
 * @method AppCountriesAPIInterface getMethod()
 *
 * @package Countries
 * @subpackage API
 * @see AppCountriesAPITrait
 * @see AppCountriesAPIInterface
 */
class AppCountriesParamsContainer extends BaseParamsHandlerContainer
{
    public function __construct(AppCountriesAPIInterface $method)
    {
        parent::__construct($method);
    }

    /**
     * Resolves the list of countries from the registered handlers.
     *
     * Returns an empty array if no handler was able to resolve countries.
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
     * Requires that at least one handler resolves a list of countries.
     * Triggers an API error response if no value can be resolved.
     *
     * @return Application_Countries_Country[]
     */
    public function requireValue(): array
    {
        $value = parent::requireValue();

        if(is_array($value)) {
            return $value;
        }

        return array();
    }

    /**
     * Pre-selects the given list of countries in all handlers that support
     * value selection.
     *
     * @param Application_Countries_Country[] $countries
     * @return $this
     */
    public function selectAppCountries(array $countries): self
    {
        return $this->selectValue($countries);
    }

    private ?AppCountryIDsHandler $countryIDsHandler = null;

    public function manageIDs(): AppCountryIDsHandler
    {
        if(!isset($this->countryIDsHandler)) {
            $this->countryIDsHandler = new AppCountryIDsHandler($this->getMethod());
            $this->registerHandler($this->countryIDsHandler);
        }

        return $this->countryIDsHandler;
    }

    private ?AppCountryISOsHandler $countryISOsHandler = null;

    public function manageISOs(): AppCountryISOsHandler
    {
        if(!isset($this->countryISOsHandler)) {
            $this->countryISOsHandler = new AppCountryISOsHandler($this->getMethod());
            $this->registerHandler($this->countryISOsHandler);
        }

        return $this->countryISOsHandler;
    }

    private ?AppCountriesRuleHandler $countriesRuleHandler = null;

    public function manageAllParamsRule(): AppCountriesRuleHandler
    {
        if(!isset($this->countriesRuleHandler)) {
            $this->countriesRuleHandler = new AppCountriesRuleHandler($this->getMethod());
            $this->registerHandler($this->countriesRuleHandler);
        }

        return $this->countriesRuleHandler;
    }

    protected function isValidValueType(float|object|array|bool|int|string $value): bool
    {
        return is_array($value);
    }
}
