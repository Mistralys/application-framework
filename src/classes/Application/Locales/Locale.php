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
    private ?string $label = null;

    public function __construct(string $localeID)
    {
        $this->localeID = $localeID;

        $parts = explode('_', $localeID);
        $this->langISO = strtolower($parts[0]);
        $this->countryISO = strtolower($parts[1]);
    }

    public function getID(): string
    {
        return $this->localeID;
    }

     public function getCode() : string
     {
         return $this->getID();
     }

    public function getLabel(): string
    {
        if (!isset($this->label)) {
            $this->label = sprintf(
                '%s (%s)',
                $this->getLanguage()->getLabel(),
                strtoupper($this->getCountry()->getISO())
            );
        }

        return $this->label;
    }

    public function getLangISO(): string
    {
        return $this->langISO;
    }

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
