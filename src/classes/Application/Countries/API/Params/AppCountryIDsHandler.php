<?php
/**
 * @package Countries
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Countries\API\Params;

use Application\API\Parameters\Handlers\BaseParamHandler;
use Application_Countries_Country;
use AppUtils\ClassHelper;

/**
 * Handler that bridges {@see AppCountryIDsParam} into a
 * {@see \Application\API\Parameters\Handlers\BaseParamsHandlerContainer}.
 *
 * Provides type-narrowed overrides for {@see register()} and {@see getParam()}
 * so consumers receive a typed `AppCountryIDsParam` without casting.
 *
 * @package Countries
 * @subpackage API
 * @see AppCountryIDsParam
 */
class AppCountryIDsHandler extends BaseParamHandler
{
    /**
     * Resolves the list of countries from the registered parameter.
     *
     * Returns `null` when the parameter has no value so that the
     * {@see \Application\API\Parameters\Handlers\BaseParamsHandlerContainer}
     * "first non-null wins" iteration can fall through to the next handler
     * (e.g. {@see AppCountryISOsHandler}).
     *
     * @return Application_Countries_Country[]|null
     */
    protected function resolveValueFromSubject(): ?array
    {
        $param = $this->getParam();

        if($param === null || $param->getValue() === null) {
            return null;
        }

        return $param->getCountries();
    }

    /**
     * Registers the parameter and returns it type-narrowed.
     *
     * @return AppCountryIDsParam
     */
    public function register(): AppCountryIDsParam
    {
        return ClassHelper::requireObjectInstanceOf(
            AppCountryIDsParam::class,
            parent::register()
        );
    }

    /**
     * Returns the registered parameter type-narrowed, or `null` if not yet registered.
     *
     * @return AppCountryIDsParam|null
     */
    public function getParam(): ?AppCountryIDsParam
    {
        $param = parent::getParam();

        if($param instanceof AppCountryIDsParam) {
            return $param;
        }

        return null;
    }

    protected function createParam(): AppCountryIDsParam
    {
        return new AppCountryIDsParam();
    }
}
