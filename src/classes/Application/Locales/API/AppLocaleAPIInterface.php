<?php
/**
 * @package Locales
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Locales\API;

use Application\API\APIMethodInterface;
use Application\API\BaseMethods\BaseAPIMethod;
use AppLocalize\Localization\Locales\LocaleInterface;

/**
 * Interface for API methods that support selecting an application locale.
 *
 * @package Locales
 * @subpackage API
 * @see AppLocaleAPITrait
 */
interface AppLocaleAPIInterface extends APIMethodInterface
{
    public const string PARAM_LOCALE = 'appLocale';

    /**
     * Selects a locale manually to be used when working outside a request context.
     * The methods {@see self::getAppLocale()} and {@see self::applyLocale()} will
     * use the selected locale instead of trying to resolve it from the request.
     *
     * @param LocaleInterface $locale
     * @return self
     */
    public function selectLocale(LocaleInterface $locale) : self;
    public function registerAppLocaleParameter() : AppLocaleParam;
    public function getAppLocaleParam() : ?AppLocaleParam;
    public function resolveAppLocaleID() : ?string;
    public function requireAppLocaleID() : string;

    /**
     * Get the application locale to use for this request.
     * If no locale was specified in the request, the system default locale is returned.
     */
    public function getAppLocale() : LocaleInterface;

    /**
     * Selects the application locale to use for this request, based on the request parameters.
     * If no locale was specified in the request, the system default locale is used.
     *
     * Typically, this method should be called first thing in {@see BaseAPIMethod::collectResponseData()}.
     */
    public function applyLocale() : void;
}
