<?php
/**
 * @package Countries
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Countries\API;

/**
 * Trait used to implement API methods that work with multiple countries.
 *
 * ## Usage
 *
 * Use {@see self::manageAppCountriesParams()} to obtain the `AppCountriesParamsContainer`,
 * then register your preferred parameter pattern in `init()`.
 *
 * ### Pattern 1 — Individual registration (no mutual exclusivity)
 *
 * Register the IDs and ISOs handlers separately. When both are registered, the
 * container uses a "first non-null wins" strategy: the IDs handler is tried first;
 * if it returns `null` (no `countryIDs` value in the request), the ISOs handler
 * is tried next. Both parameters are optional and independent — the caller may
 * supply either, both, or neither.
 *
 * ```php
 * protected function init(): void
 * {
 *     $this->manageAppCountriesParams()->manageIDs()->register();
 *     $this->manageAppCountriesParams()->manageISOs()->register();
 * }
 * ```
 *
 * > **Important:** `AppCountryIDsHandler` and `AppCountryISOsHandler` both return
 * > `null` (not `[]`) when the request contains no value for their parameter. This
 * > `null` sentinel is what allows the container to fall through to the next handler.
 *
 * @see TestGetCountriesAPI for a live example of this registration pattern.
 *
 * ### Pattern 2 — OrRule registration (mutual exclusivity)
 *
 * Register the combined `AppCountriesParamRule` via `manageAllParamsRule()`. The
 * OrRule enforces that the caller supplies **either** `countryIDs` **or**
 * `countryISOs`, but not both. Supplying both or neither results in an API error
 * response. Use this pattern when mutual exclusivity is a requirement.
 *
 * ```php
 * protected function init(): void
 * {
 *     $this->manageAppCountriesParams()->manageAllParamsRule()->register();
 * }
 * ```
 *
 * @see TestGetCountriesBySetAPI for a live example of this registration pattern.
 *
 * @package Countries
 * @subpackage API
 * @see AppCountriesAPIInterface
 * @see AppCountriesParamsContainer
 */
trait AppCountriesAPITrait
{
    private ?AppCountriesParamsContainer $appCountriesParamsContainer = null;

    public function manageAppCountriesParams(): AppCountriesParamsContainer
    {
        if(!isset($this->appCountriesParamsContainer)) {
            $this->appCountriesParamsContainer = new AppCountriesParamsContainer($this);
        }

        return $this->appCountriesParamsContainer;
    }
}
