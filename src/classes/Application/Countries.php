<?php
/**
 * File containing the {@link Application_Countries} class.
 * @package Maileditor
 * @subpackage Countries
 */

use function AppUtils\parseVariable;

/**
 * Country management class, used to retrieve information
 * about available countries and add or delete individiual
 * countries.
 *
 * @package Maileditor
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @method Application_Countries_Country getByID(int $country_id)
 * @method Application_Countries_FilterCriteria getFilterCriteria()
 * @method Application_Countries_Country|NULL getByRequest()
 */
class Application_Countries extends DBHelper_BaseCollection
{
    public const ERROR_UNKNOWN_ISO_CODE = 21901;
    public const ERROR_INVALID_COUNTRY_ID = 21902;
    public const ERROR_UNKNOWN_LOCALE_CODE = 21903;
    const PRIMARY_NAME = 'country_id';
    const TABLE_NAME = 'countries';

    /**
     * @var Application_Countries
     */
    protected static $instance;

    public function getRecordDefaultSortKey() : string
    {
        return 'label';
    }
    
    public function getRecordClassName() : string
    {
        return Application_Countries_Country::class;
    }
    
    public function getRecordFiltersClassName() : string
    {
        return Application_Countries_FilterCriteria::class;
    }
    
    public function getRecordFilterSettingsClassName() : string
    {
        return '';
    }
    
    public function getRecordSearchableColumns() : array
    {
        return array(
            'label' => t('Label'), 
            'iso' => t('Two-letter country code')
        );
    }
    
    public function getRecordPrimaryName() : string
    {
        return self::PRIMARY_NAME;
    }
    
    public function getRecordTypeName() : string
    {
        return 'country';
    }
    
    public function getRecordTableName() : string
    {
        return self::TABLE_NAME;
    }
    
    /**
     * Returns the global instance of the countries manager,
     * creating it as needed.
     *
     * @return Application_Countries
     */
    public static function getInstance() : Application_Countries
    {
        if (!isset(self::$instance)) {
            self::$instance = new Application_Countries();
        }

        return self::$instance;
    }

   /**
    * Retrieves a country by its ID.
    * 
    * @param integer $country_id
    * @return Application_Countries_Country
    */
    public function getCountryByID(int $country_id) : Application_Countries_Country
    {
        return $this->getByID($country_id);
    }
    
   /**
    * Retrieves the country independent meta entry.
    * @return Application_Countries_Country
    */
    public function getInvariantCountry()
    {
        return $this->getByID(Application_Countries_Country::COUNTRY_INDEPENDENT_ID);
    }

    /**
     * Creates and adds a country selection element to the specified form,
     * and returns the created form element.
     *
     * @param UI_Form $form
     * @param string $fieldName
     * @param string $fieldLabel
     * @return HTML_QuickForm2_Element_Select
     */
    public function injectCountrySelector(UI_Form $form, $fieldName=null, $fieldLabel = null, $required = true, $pleaseSelect = true, $withInvariant=true)
    {
        /* @var $country Application_Countries_Country */

        if(empty($fieldName)) {
           $fieldName = self::PRIMARY_NAME;
        }
        
        if (empty($fieldLabel)) {
            $fieldLabel = t('Country');
        }

        $element = $form->getForm()->addSelect($fieldName);
        $element->setLabel($fieldLabel);
        if ($required) {
            $form->makeRequired($element);
        }
        if ($pleaseSelect) {
            $element->addOption(t('Please select...'), '');
        }

        $countries = $this->getAll();
        foreach ($countries as $country) {
            if(!$withInvariant && $country->isCountryIndependent()) {
                continue;
            }
            
            $element->addOption(
                $country->getLocalizedLabel(), 
                (string)$country->getID()
            );
        }

        return $element;
    }
    
    protected $cachedCountries;
    
   /**
    * @see DBHelper_BaseCollection::getAll()
    * @return Application_Countries_Country[]
    */
    public function getAll(bool $includeInvariant=true) : array
    {
        if(!isset($this->cachedCountries)) 
        {
            $this->cachedCountries = parent::getAll();
            
            // sort by the localized label, which is not in the database.
            usort($this->cachedCountries, array($this, 'handle_sortCountries'));
        }
        
        if(!$includeInvariant)
        {
            $result = array();
            
            foreach($this->cachedCountries as $country) 
            {
                if(!$country->isCountryIndependent()) {
                    $result[] = $country;
                }
            }
            
            return $result;
        }
        
        return $this->cachedCountries;
    }

    /**
     * @param bool $includeInvariant
     * @return int[]
     */
    public function getIDs(bool $includeInvariant=true) : array
    {
        $result = array();

        $all = $this->getAll($includeInvariant);

        foreach($all as $country)
        {
            $result[] = $country->getID();
        }

        return $result;
    }
    
    public function handle_sortCountries(Application_Countries_Country $a, Application_Countries_Country $b)
    {
        if($a->isCountryIndependent()) {
            return -1;
        }
        
        return strnatcasecmp($a->getLocalizedLabel(), $b->getLocalizedLabel());
    }
    
   /**
    * @deprecated
    * @param boolean $simulation
    */
    public function refreshCache($simulation)
    {
        // obsolete
    }
    
    public function injectJS()
    {
        $ui = UI::getInstance();
        $ui->addJavascript('countries.js');
        
        $countries = $this->getAll();
        foreach($countries as $country) {
            $ui->addJavascriptHeadStatement(
                'Countries.Register',
                $country->getID(),
                $country->getISO(),
                $country->getLocalizedLabel()
            );
        }
    }
    
   /**
    * Checks whether the two-letter country ISO code
    * exists. In case of the UK, both "uk" and "gb"
    * are supported.
    *  
    * @param string $iso
    * @return bool
    */
    public function isoExists(string $iso) : bool
    {
        $iso = strtolower($iso);
        
        return in_array($iso, $this->getSupportedISOs());
    }

   /**
    * Gets a list of all country ISO codes supported by
    * the countries management.
    * 
    * @return array
    */
    public function getSupportedISOs() : array
    {
        $countries = $this->getAll();
        
        $isos = array();
        
        foreach($countries as $country)
        {
            $isos[] = $country->getISO();
            $isos[] = $country->getAlpha2();
        }
        
        return array_unique($isos);
    }
    
    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getCollectionLabel()
     */
    public function getCollectionLabel() : string
    {
        return t('Countries');
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordLabel()
     */
    public function getRecordLabel() : string
    {
        return t('Country');
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordProperties()
     */
    public function getRecordProperties() : array
    {
        return array();
    }
    
   /**
    * Retrieves a country by its two-letter ISO code, e.g. "de".
    * 
    * @param string $iso
    * @throws Application_Exception
    * @return Application_Countries_Country
    * 
    * @see Application_Countries::ERROR_UNKNOWN_ISO_CODE
    */
    public function getByISO(string $iso) : Application_Countries_Country
    {
        $iso = strtolower($iso);
        $all = $this->getAll();
        
        foreach($all as $country)
        {
            if($country->getISO() === $iso || $country->getAlpha2() === $iso)
            {
                return $country;
            }
        }
        
        throw new Application_Exception(
            'Unknown country ISO code',
            sprintf(
                'The ISO code [%s] does not match any valid countries.',
                $iso
            ),
            self::ERROR_UNKNOWN_ISO_CODE
        );
    }

    /**
     * @param string $code The locale code, e.g. "de_DE"
     * @return Application_Countries_Country
     * @throws Application_Countries_Exception
     */
    public function getByLocaleCode(string $code) : Application_Countries_Country
    {
        return $this->parseLocaleCode($code)->getCountry();
    }

    /**
     * Parses a locale code to access information on its
     * constituent parts.
     *
     * @param string $code The locale code, e.g. "de_DE"
     * @return Application_Countries_LocaleCode
     * @throws Application_Countries_Exception
     */
    public function parseLocaleCode(string $code) : Application_Countries_LocaleCode
    {
        return new Application_Countries_LocaleCode($code);
    }

   /**
    * The navigator can be used to create a navigation
    * element to switch between countries.
    * 
    * @return Application_Countries_Navigator
    */
    public function createCountryNavigator()
    {
        require_once 'Application/Countries/Navigator.php';
        return new Application_Countries_Navigator($this);
    }
    
   /**
    * For a list of string or integer IDs, returns 
    * all matching countries by their ID.
    * 
    * @param string[]|int[] $ids
    * @throws Application_Countries_Exception
    * @return Application_Countries_Country[]
    */
    public function getInstancesByIDs(array $ids) : array
    {
        $result = array();
        
        foreach($ids as $id)
        {
            $countryID = intval($id);
            
            if($countryID === 0 || !$this->idExists($countryID)) 
            {
                throw new Application_Countries_Exception(
                    'Invalid or unknown country ID.',
                    sprintf(
                        'The country ID [%s] is not a valid ID, or could not be found in the database.',
                        parseVariable($id)->enableType()->toString()
                    ),
                    self::ERROR_INVALID_COUNTRY_ID
                );
            }
            
            $result[$countryID] = $this->getByID($countryID);
        }
        
        return array_values($result);
    }
    
   /**
    * Creates a country selector helper instance: this can
    * be used to add a country select element to the target
    * formable, with a number of customization options.
    * 
    * @param Application_Formable $formable
    * @return Application_Countries_Selector
    */
    public static function createSelector(Application_Formable $formable) : Application_Countries_Selector
    {
        return new Application_Countries_Selector($formable);
    }
    
   /**
    * Creates a button bar to select a country,
    * whose selection is persisted in the user
    * settings.
    * 
    * @param string $id A freeform ID to tie the country selection to: used to namespace the setting under which the country is stored.
    * @param string $baseURL The base URL to use, to which the country selection parameter will be appended.
    * @return Application_Countries_ButtonBar
    */
    public static  function createButtonBar(string $id, string $baseURL) : Application_Countries_ButtonBar
    {
        return new Application_Countries_ButtonBar($id, $baseURL);
    }
}
