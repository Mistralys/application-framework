<?php
/**
 * File containing the {@link Application_Countries_Country} class.
 * @package Maileditor
 * @subpackage Countries
 */

use Application\AppFactory;
use Application\Languages;
use Application\Languages\Language;
use Application\Languages\LanguageException;
use AppLocalize\Localization;
use AppLocalize\Localization_Country;
use AppLocalize\Localization_Currency;

/**
 * Country data type; handles an individual country and its information.
 *
 * @package Application
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Countries_Country extends DBHelper_BaseRecord
{
    public const ERROR_UNKNOWN_LANGUAGE_CODE = 37801;

    public const COUNTRY_INDEPENDENT_ID = 9999;
    public const COUNTRY_INDEPENDENT_ISO = 'zz';
    public const COL_ISO = 'iso';
    public const COL_LABEL = 'label';

    protected Localization_Country $country;

    protected function init() : void
    {
        $this->country = Localization::createCountry($this->getISO());
    }
    
    public function getLabel() : string
    {
        $label = $this->getRecordKey(self::COL_LABEL);
        
        if($this->isInvariant()) {
            $label = '('.$label.')';
        }
        
        return $label;
    }

    public function getLocalizedLabel() : string
    {
        $label = $this->country->getLabel();
        
        if($this->isInvariant()) {
            $label = '('.$label.')';
        }
        
        return $label;
    }
    
    public function getIconLabel() : string
    {
        return $this->getIcon().' '.$this->getLocalizedLabel();
    }
    
   /**
    * @return Application_Countries_Country_Icon
    */
    public function getIcon() : Application_Countries_Country_Icon
    {
        return new Application_Countries_Country_Icon($this);
    }

   /**
    * The lowercase two-letter country iso string, e.g. "us", "de".
    * 
    * @param boolean $emptyIfInvariant Whether to return an empty string when this is the country independent entry.
    * @return string
    */
    public function getISO(bool $emptyIfInvariant=false) : string
    {
        if($this->isInvariant())
        {
            if($emptyIfInvariant) {
                return '';
            }

            return self::COUNTRY_INDEPENDENT_ISO;
        }
        
        return $this->getRecordKey(self::COL_ISO);
    }

    /**
     * @var array<string,string>
     */
    private array $isoToAlpha2 = array(
        Application_Countries::COUNTRY_UK => Application_Countries::COUNTRY_GB
    );

   /**
    * Retrieves the Alpha 2 ISO code for the country.
    * @return string
    * @see https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2
    */
    public function getAlpha2() : string
    {
        $iso = $this->getISO();

        if(isset($this->isoToAlpha2[$iso]))
        {
            return $this->isoToAlpha2[$iso];
        }

        return $iso;
    }
   
   /**
    * Primary language by country
    * @var array<string,string>
    * @see https://wiki.openstreetmap.org/wiki/Nominatim/Country_Codes
    */
    public const COUNTRY_LANGUAGES = array(
        Application_Countries::COUNTRY_AT => Languages::LANG_DE,
        Application_Countries::COUNTRY_CA => Languages::LANG_EN,
        Application_Countries::COUNTRY_DE => Languages::LANG_DE,
        Application_Countries::COUNTRY_ES => Languages::LANG_ES,
        Application_Countries::COUNTRY_FR => Languages::LANG_FR,
        Application_Countries::COUNTRY_IT => Languages::LANG_IT,
        Application_Countries::COUNTRY_MX => Languages::LANG_ES,
        Application_Countries::COUNTRY_PL => Languages::LANG_PL,
        Application_Countries::COUNTRY_RO => Languages::LANG_RO,
        Application_Countries::COUNTRY_UK => Languages::LANG_EN,
        Application_Countries::COUNTRY_GB => Languages::LANG_EN,
        Application_Countries::COUNTRY_US => Languages::LANG_EN
    );

   /**
    * Retrieves the lowercase two-letter language code for
    * the country. Note that this only returns the main 
    * language used in the country, if it has several
    * official ones.
    * 
    * @throws Application_Exception
    * @return string
    */
    public function getLanguageCode() : string
    {
        $iso = $this->getISO();
        if(isset(self::COUNTRY_LANGUAGES[$iso])) {
            return self::COUNTRY_LANGUAGES[$iso];
        }
        
        throw new Application_Exception(
            sprintf('Unknown language code for country [%s]', $iso),
            '',
            self::ERROR_UNKNOWN_LANGUAGE_CODE
        );
    }
    
   /**
    * Retrieves the full locale code with country and language codes.
    * 
    * @return string The locale code, e.g. "en_US".
    */
    public function getLocaleCode() : string
    {
        return $this->getLanguageCode().'_'.strtoupper($this->getAlpha2());
    }

    public function getLocale() : \Application\Locales\Locale
    {
        return AppFactory::createLocales()->getByID($this->getLocaleCode());
    }
    
   /**
    * Retrieves the human-readable label of the country's
    * main language (translated to the current app locale).
    *  
    * @throws LanguageException
    * @return string
    */
    public function getLanguageLabel() : string
    {
        return $this->getLanguage()->getLabel();
    }

    public function getLanguage() : Language
    {
        return AppFactory::createLanguages()->getByISO($this->getLanguageCode());
    }
    
   /**
    * The currency used in this country.
    * @return Localization_Currency
    */
    public function getCurrency() : Localization_Currency
    {
        return $this->country->getCurrency();
    }
    
    /**
     * {@inheritDoc}
     * @see DBHelper_BaseRecord::recordRegisteredKeyModified()
     */
    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue)
    {
    }
    
   /**
    * Whether this is the country-independent country.
    * Alias for the {@link isInvariant()} method.
    *
    * @return boolean
    */
    public function isInvariant() : bool
    {
        return $this->getID() === self::COUNTRY_INDEPENDENT_ID;
    }
    
   /**
    * Whether this is the country-independent country.
    * @return boolean
    */
    public function isCountryIndependent() : bool
    {
        return $this->isInvariant();
    }
}
