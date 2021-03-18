<?php
/**
 * File containing the {@link Application_Countries_Country} class.
 * @package Maileditor
 * @subpackage Countries
 */

/**
 * Country data type; handles an individual country and its information.
 *
 * @package Maileditor
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Countries_Country extends DBHelper_BaseRecord
{
    const ERROR_UNKNOWN_LANGUAGE_CODE = 37801;
    const ERROR_UNKNOWN_LANGUAGE_LABEL = 37802;
    
    const COUNTRY_INDEPENDENT_ID = 9999;
    const COUNTRY_INDEPENDENT_ISO = 'zz';
    
   /**
    * @var \AppLocalize\Localization_Country
    */
    protected $country;

    protected function init()
    {
        $this->country = \AppLocalize\Localization::createCountry($this->getISO());
    }
    
    public function getLabel() : string
    {
        $label = $this->getRecordKey('label');
        
        if($this->isInvariant()) {
            $label = '('.$label.')';
        }
        
        return $label;
    }

    public function getLocalizedLabel()
    {
        $label = $this->country->getLabel();
        
        if($this->isInvariant()) {
            $label = '('.$label.')';
        }
        
        return $label;
    }
    
    public function getIconLabel()
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
        
        return $this->getRecordKey('iso');
    }
    
   /**
    * Retrieves the Alpha 2 ISO code for the country.
    * @return string
    * @see https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2
    */
    public function getAlpha2()
    {
        $iso = $this->getISO();
        
        if($iso == 'uk') {
            return 'gb';
        }
        
        return $iso;
    }
   
   /**
    * Primary language by country
    * @var array
    * @see https://wiki.openstreetmap.org/wiki/Nominatim/Country_Codes
    */
    protected $languages = array(
        'at' => 'de',
        'ca' => 'en',
        'de' => 'de',
        'es' => 'es',
        'fr' => 'fr',
        'it' => 'it',
        'mx' => 'es', 
        'pl' => 'pl',
        'ro' => 'ro',
        'uk' => 'en',
        'us' => 'en'
    );
    
    protected $languageLabels;
    
    protected function initLanguages()
    {
        if(isset($this->languageLabels)) {
            return;
        }
        
        $this->languageLabels = array(
            'de' => t('German'),
            'en' => t('English'),
            'fr' => t('French'),
            'it' => t('Italian'),
            'es' => t('Spanish'),
            'pl' => t('Polish'),
            'ro' => t('Romanian')
        );
    }
    
   /**
    * Retrieves the lowercase two-letter language code for
    * the country. Note that this only returns the main 
    * language used in the the country, if it has several
    * official ones.
    * 
    * @throws Application_Exception
    * @return string
    */
    public function getLanguageCode()
    {
        $iso = $this->getISO();
        if(isset($this->languages[$iso])) {
            return $this->languages[$iso];
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
    
   /**
    * Retrieves the human readable label of the country's
    * main language (translated to the current app locale).
    *  
    * @throws Application_Exception
    * @return string
    */
    public function getLanguageLabel()
    {
        $this->initLanguages();
        
        $iso = $this->getLanguageCode();
        if(isset($this->languageLabels[$iso])) {
            return $this->languageLabels[$iso];
        }
        
        throw new Application_Exception(
            sprintf('Unknown language label for language [%s]', $iso),
            '',
            self::ERROR_UNKNOWN_LANGUAGE_LABEL
        );
    }
    
   /**
    * The currency used in this country.
    * @return \AppLocalize\Localization_Currency
    */
    public function getCurrency()
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
    * Whether this is the country independent special country.
    * Alias for the {@link isInvariant()} method.
    * @return boolean
    */
    public function isInvariant()
    {
        if($this->getID() == self::COUNTRY_INDEPENDENT_ID) {
            return true;
        }
        
        return false;
    }
    
   /**
    * Whether this is the country independent special country.
    * @return boolean
    */
    public function isCountryIndependent()
    {
        return $this->isInvariant();
    }
} 
