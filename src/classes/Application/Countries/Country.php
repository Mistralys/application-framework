<?php
/**
 * @package Maileditor
 * @subpackage Countries
 */

declare(strict_types=1);

use Application\AppFactory;
use Application\Countries\Admin\CountryAdminURLs;
use Application\Languages;
use Application\Languages\Language;
use Application\Languages\LanguageException;
use AppLocalize\Localization;
use AppLocalize\Localization\Countries\CountryCollection;
use AppLocalize\Localization\Countries\CountryInterface;
use AppLocalize\Localization\Country\CountryGB;
use AppLocalize\Localization\Currencies\CurrencyInterface;

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

    /**
     * @deprecated Use the ISO instead, which is more reliable: {@see self::COUNTRY_INDEPENDENT_ISO}
     */
    public const COUNTRY_INDEPENDENT_ID = 9999;
    public const COUNTRY_INDEPENDENT_ISO = 'zz';
    public const COL_ISO = 'iso';
    public const COL_LABEL = 'label';

    protected CountryInterface $country;

    protected function init() : void
    {
        $this->country = Localization::createCountries()->getByISO($this->getISO());
    }
    
    public function getLabel() : string
    {
        $label = $this->getRecordKey(self::COL_LABEL);

        // If the label matches the invariant one, use the translated label instead.
        if($label === $this->country->getLabelInvariant()) {
            return $this->country->getLabel();
        }
        
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
        $iso = $this->getRecordKey(self::COL_ISO);

        if($emptyIfInvariant && $iso === self::COUNTRY_INDEPENDENT_ISO) {
            return '';
        }

        return $iso;
    }

    /**
     * @var array<string,string>
     */
    private array $isoToAlpha2 = array(
        CountryGB::ISO_ALIAS_UK => CountryGB::ISO_CODE
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
    * Retrieves the lowercase two-letter language code for
    * the country. Note that this only returns the main 
    * language used in the country if it has several
    * official ones.
    * 
    * @return string
    */
    public function getLanguageCode() : string
    {
        return $this->getLocalizationLocale()->getLanguageCode();
    }
    
   /**
    * Retrieves the full locale code with country and language codes.
    * 
    * @return string The locale code, e.g. "en_US".
    */
    public function getLocaleCode() : string
    {
        return $this->getLocalizationLocale()->getName();

    }

    public function getLocale() : \Application\Locales\Locale
    {
        return AppFactory::createLocales()->getByID($this->getLocaleCode());
    }

    private ?Localization\Locales\LocaleInterface $localizationLocale = null;

    public function getLocalizationLocale() : Localization\Locales\LocaleInterface
    {
        if(!isset($this->localizationLocale)) {
            $this->localizationLocale = CountryCollection::getInstance()
                ->getByISO($this->getISO())
                ->getMainLocale();
        }

        return $this->localizationLocale;
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
    * @return CurrencyInterface
    */
    public function getCurrency() : CurrencyInterface
    {
        return $this->country->getCurrency();
    }
    
    /**
     * {@inheritDoc}
     * @see DBHelper_BaseRecord::recordRegisteredKeyModified()
     */
    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue) : void
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
        return $this->getISO() === self::COUNTRY_INDEPENDENT_ISO;
    }
    
   /**
    * Whether this is the country-independent country.
    * @return boolean
    */
    public function isCountryIndependent() : bool
    {
        return $this->isInvariant();
    }

    private ?CountryAdminURLs $adminURLs = null;

    public function adminURL() : CountryAdminURLs
    {
        if(!isset($this->adminURLs)) {
            $this->adminURLs = new CountryAdminURLs($this);
        }

        return $this->adminURLs;
    }
}
