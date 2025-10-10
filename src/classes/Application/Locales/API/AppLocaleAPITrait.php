<?php
/**
 * @package Locales
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Locales\API;

use AppLocalize\Localization;
use AppLocalize\Localization\Locales\LocaleInterface;

/**
 * Trait used to add application locale parameter handling to API methods.
 *
 * @package Locales
 * @subpackage API
 * @see AppLocaleAPIInterface
 */
trait AppLocaleAPITrait
{
    private ?AppLocaleParam $appLocaleParam = null;

    public function registerAppLocaleParameter() : AppLocaleParam
    {
        if(isset($this->appLocaleParam)) {
            return $this->appLocaleParam;
        }

        $this->appLocaleParam = new AppLocaleParam();

        $this->manageParams()->registerParam($this->appLocaleParam);
        return $this->appLocaleParam;
    }

    public function getAppLocaleParam() : ?AppLocaleParam
    {
        return $this->appLocaleParam;
    }

    public function resolveAppLocaleID() : ?string
    {
        return $this->getAppLocaleParam()?->getValue();
    }

    public function getAppLocale() : LocaleInterface
    {
        $localeID = $this->resolveAppLocaleID();

        if($localeID !== null) {
            return Localization::getAppLocaleByName($localeID);
        }

        return Localization::getAppLocaleByName(Localization::BUILTIN_LOCALE_NAME);
    }

    public function requireAppLocaleID() : string
    {
        $locale = $this->resolveAppLocaleID();
        if($locale !== null) {
            return $locale;
        }

        $this->errorResponse(AppLocaleAPIInterface::ERROR_INVALID_REQUEST_PARAMS)
            ->makeBadRequest()
            ->send();
    }

    public function applyLocale() : void
    {
        Localization::selectAppLocale($this->getAppLocale()->getName());
    }
}
