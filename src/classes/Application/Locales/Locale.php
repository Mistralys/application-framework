<?php
/**
 * @package Application
 * @subpackage Countries
 */

declare(strict_types=1);

namespace Application\Locales;

use Application\AppFactory;
use Application\Languages\Language;
use Application_Countries_Country;
use AppLocalize\Localization\Locales\LocaleInterface;
use AppUtils\Interfaces\StringPrimaryRecordInterface;

/**
 * Holds information about a single locale.
 *
 * This is a composite of the language and the country.
 * It is used to represent a country-specific language,
 * aka a locale.
 *
 * Example: "en_US" for English in the United States.
 *
 * @package Application
 * @subpackage Countries
 */
class Locale implements StringPrimaryRecordInterface
{
    private string $localeID;
    private string $langISO;
    private string $countryISO;
    private ?Language $language = null;
    private ?Application_Countries_Country $country = null;
    private LocaleInterface $locale;

    public function __construct(LocaleInterface $locale)
    {
        $this->locale = $locale;
        $this->localeID = $locale->getID();
        $this->langISO = $locale->getLanguageCode();
        $this->countryISO = $locale->getCountryCode();
    }

    /**
     * @return string The locale ID, e.g., `en_US`.
     *
     */
    public function getID(): string
    {
        return $this->localeID;
    }

    /**
     * @return string The locale code, e.g., `en_US`.
     */
     public function getCode() : string
     {
         return $this->getID();
     }

    public function getLabel(): string
    {
        return $this->locale->getLabel();
    }

    /**
     * @return string The ISO code of the language, e.g., `en`.
     */
    public function getLangISO(): string
    {
        return $this->langISO;
    }

    /**
     * @return string The ISO code of the country, e.g., `US`.
     */
    public function getCountryISO(): string
    {
        return $this->countryISO;
    }

    public function getLanguage(): Language
    {
        if (!isset($this->language)) {
            $this->language = AppFactory::createLanguages()->getByISO($this->getLangISO());
        }

        return $this->language;
    }

    public function getCountry(): Application_Countries_Country
    {
        if (!isset($this->country)) {
            $this->country = AppFactory::createCountries()->getByISO($this->getCountryISO());
        }

        return $this->country;
    }
}
