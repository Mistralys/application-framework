<?php
/**
 * @package Maileditor
 * @subpackage Countries
 */

declare(strict_types=1);

use Application\Countries\Admin\MainAdminURLs;
use Application\Countries\CountriesCollection;
use Application\Countries\CountryException;
use Application\Countries\CountrySettingsManager;
use Application\Countries\FilterSettings;
use Application\Languages\Language;
use AppLocalize\Localization\Countries\CountryCollection;
use AppLocalize\Localization\Country\CountryGB;
use AppUtils\NamedClosure;
use function AppUtils\parseVariable;

/**
 * Country management class, used to retrieve information
 * about available countries and add or delete individual
 * countries.
 *
 * @package Maileditor
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @method Application_Countries_Country getByID(int $country_id)
 * @method Application_Countries_FilterCriteria getFilterCriteria()
 * @method Application_Countries_Country|NULL getByRequest()
 * @method Application_Countries_Country createNewRecord(array $data = array(), bool $silent = false, array $options = array())
 */
class Application_Countries extends DBHelper_BaseCollection
{
    public const ERROR_UNKNOWN_ISO_CODE = 21901;
    public const ERROR_INVALID_COUNTRY_ID = 21902;
    public const ERROR_CANNOT_USE_ALIAS_FOR_CREATION = 21903;
    public const ERROR_ISO_ALREADY_EXISTS = 21904;

    public const PRIMARY_NAME = 'country_id';
    public const TABLE_NAME = 'countries';
    public const REQUEST_PARAM_ID = self::PRIMARY_NAME;
    public const RECORD_TYPE_NAME = 'country';

    protected static ?Application_Countries $instance = null;

    public function getRecordDefaultSortKey() : string
    {
        return Application_Countries_Country::COL_LABEL;
    }

    public function getRecordRequestPrimaryName() : string
    {
        return self::REQUEST_PARAM_ID;
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
        return FilterSettings::class;
    }
    
    public function getRecordSearchableColumns() : array
    {
        return array(
            Application_Countries_Country::COL_LABEL => t('Label'),
            Application_Countries_Country::COL_ISO => t('Two-letter country code')
        );
    }
    
    public function getRecordPrimaryName() : string
    {
        return self::PRIMARY_NAME;
    }
    
    public function getRecordTypeName() : string
    {
        return self::RECORD_TYPE_NAME;
    }
    
    public function getRecordTableName() : string
    {
        return self::TABLE_NAME;
    }
    
    /**
     * Returns the global instance of the country manager,
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
    * Retrieves the country independent meta-entry.
    * @return Application_Countries_Country
    */
    public function getInvariantCountry() : Application_Countries_Country
    {
        return $this->getByISO(Application_Countries_Country::COUNTRY_INDEPENDENT_ISO);
    }

    /**
     * Creates and adds a country selection element to the specified form,
     * and returns the created form element.
     *
     * @param UI_Form $form
     * @param string|null $fieldName
     * @param string|null $fieldLabel
     * @param bool $required
     * @param bool $pleaseSelect
     * @param bool $withInvariant
     * @return HTML_QuickForm2_Element_Select
     * @throws HTML_QuickForm2_Exception
     * @throws HTML_QuickForm2_InvalidArgumentException
     * @throws UI_Exception
     */
    public function injectCountrySelector(UI_Form $form, ?string $fieldName=null, ?string $fieldLabel = null, bool $required = true, bool $pleaseSelect = true, bool $withInvariant=true): HTML_QuickForm2_Element_Select
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

   /**
    * @return Application_Countries_Country[]
    */
    public function getAll(bool $includeInvariant=true) : array
    {
        /**
         * @var Application_Countries_Country[] $countries
         */
        $countries = parent::getAll();
            
        // sort by the localized label, which is not in the database.
        usort(
            $countries,
            NamedClosure::fromClosure(
                Closure::fromCallable(array($this, 'handle_sortCountries')),
                array($this, 'handle_sortCountries')
            )
        );

        $result = array();

        foreach($countries as $country)
        {
            if($includeInvariant === false && $country->isCountryIndependent()) {
                continue;
            }

            $result[] = $country;
        }

        return $result;
    }

    public function getCollection() : CountriesCollection
    {
        return CountriesCollection::create($this->getAll());
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

    public function getByLanguage(Language $language) : array
    {
        $countries = $this->getAll();
        $iso = $language->getISO();
        $result = array();

        foreach($countries as $country) {
            if($country->getLanguageCode() === $iso) {
                $result[] = $country;
            }
        }

        usort($result, Closure::fromCallable(array($this, 'handle_sortCountries')));

        return $result;
    }

    private function handle_sortCountries(Application_Countries_Country $a, Application_Countries_Country $b): int
    {
        if($a->isCountryIndependent()) {
            return -1;
        }
        
        return strnatcasecmp($a->getLocalizedLabel(), $b->getLocalizedLabel());
    }
    
    public function injectJS() : void
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
    * exists. In the case of the UK, both "uk" and "gb"
    * are supported.
    *  
    * @param string $iso
    * @return bool
    */
    public function isoExists(string $iso) : bool
    {
        $iso = CountryCollection::getInstance()->filterCode($iso);

        foreach($this->getAll() as $country)
        {
            if($country->getISO() === $iso || $country->getAlpha2() === $iso)
            {
                return true;
            }
        }

        return false;
    }

   /**
    * Gets a list of all country ISO codes supported by
    * the country management.
    *
    * @param bool $includeInvariant
    * @return string[]
    */
    public function getSupportedISOs(bool $includeInvariant=true) : array
    {
        $countries = $this->getAll($includeInvariant);
        
        $result = array();
        
        foreach($countries as $country)
        {
            $result[] = $country->getISO();
            $result[] = $country->getAlpha2();
        }
        
        return array_unique($result);
    }
    
    public function getCollectionLabel() : string
    {
        return t('Countries');
    }

    public function getRecordLabel() : string
    {
        return t('Country');
    }

    public function getRecordProperties() : array
    {
        return array();
    }
    
   /**
    * Retrieves a country by its two-letter ISO code, e.g. "de".
    * 
    * @param string $iso
    * @throws CountryException
    * @return Application_Countries_Country
    * 
    * @see Application_Countries::ERROR_UNKNOWN_ISO_CODE
    */
    public function getByISO(string $iso) : Application_Countries_Country
    {
        $iso = CountryCollection::getInstance()->filterCode($iso);
        $all = $this->getAll();

        foreach($all as $country)
        {
            if($country->getISO() === $iso || $country->getAlpha2() === $iso)
            {
                return $country;
            }
        }
        
        throw new CountryException(
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
     * @throws CountryException
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
     * @throws CountryException
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
    public function createCountryNavigator() : Application_Countries_Navigator
    {
        return new Application_Countries_Navigator($this);
    }
    
   /**
    * For a list of string or integer IDs, returns 
    * all matching countries by their ID.
    * 
    * @param string[]|int[] $ids
    * @throws CountryException
    * @return Application_Countries_Country[]
    */
    public function getInstancesByIDs(array $ids) : array
    {
        $result = array();
        
        foreach($ids as $id)
        {
            $countryID = (int)$id;
            
            if($countryID === 0 || !$this->idExists($countryID)) 
            {
                throw new CountryException(
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
    * @param string $id A freeform ID to tie the country selection to: This is used to namespace the setting under which the country is stored.
    * @param string $baseURL The base URL to use, to which the country selection parameter will be appended.
    * @param int[] $limitToCountries List of country IDs to limit the selection to.
    * @return Application_Countries_ButtonBar
    */
    public static function createButtonBar(string $id, string $baseURL, array $limitToCountries=array()) : Application_Countries_ButtonBar
    {
        return new Application_Countries_ButtonBar($id, $baseURL, $limitToCountries);
    }

    public function createInvariantCountry() : Application_Countries_Country
    {
        if($this->isoExists(Application_Countries_Country::COUNTRY_INDEPENDENT_ISO)) {
            return $this->getByISO(Application_Countries_Country::COUNTRY_INDEPENDENT_ISO);
        }

        return $this->createNewCountry(
            Application_Countries_Country::COUNTRY_INDEPENDENT_ISO,
            'Country independent'
        );
    }

    public function createNewCountry(string $iso, string $label) : Application_Countries_Country
    {
        if($this->isoExists($iso)) {
            throw new CountryException(
                sprintf('Cannot add country [%s], it already exists.', $iso),
                '',
                self::ERROR_ISO_ALREADY_EXISTS
            );
        }

        $collection = CountryCollection::getInstance();
        $iso = $collection->filterCode($iso);

        $this->validateISO($iso);

        return $this->createNewRecord(
            array(
                Application_Countries_Country::COL_ISO => $iso,
                Application_Countries_Country::COL_LABEL => $label
            )
        );
    }

    protected function _registerKeys() : void
    {
        $this->keys->register(Application_Countries_Country::COL_ISO)
            ->makeRequired()
            ->setValidation(Closure::fromCallable(array($this, 'validateISO')));

        $this->keys->register(Application_Countries_Country::COL_LABEL)
            ->makeRequired();
    }

    private function validateISO(string $iso) : void
    {
        $iso = strtolower($iso);
        $collection = CountryCollection::getInstance();
        $aliases = $collection->getAliases();

        if(isset($aliases[$iso]))
        {
            throw new CountryException(
                'Cannot use specified ISO code for a country.',
                sprintf(
                    'Use the code [%s] instead, which supports being accessed as [%s] as well.',
                    $aliases[$iso],
                    $iso
                ),
                self::ERROR_CANNOT_USE_ALIAS_FOR_CREATION
            );
        }

        if(!$collection->isoExists($iso)) {
            throw new CountryException(
                'Unsupported country ISO code',
                sprintf(
                    'The ISO code [%s] is not on the list of supported system countries. '.PHP_EOL.
                    'Available countries are: '.PHP_EOL.
                    '- %s',
                    $iso,
                    implode(PHP_EOL.'- ', $collection->getIDs())
                ),
                self::ERROR_UNKNOWN_ISO_CODE
            );
        }
    }

    /**
     * @var array<string,string>
     */
    private array $isoConversions = array(
        CountryGB::ISO_ALIAS_UK => CountryGB::ISO_CODE
    );

    public function convertISO(string $iso) : string
    {
        if(isset($this->isoConversions[$iso]))
        {
            return $this->isoConversions[$iso];
        }

        return $iso;
    }

    public function isValidISO(string $iso) : bool
    {
        return strlen($iso) === 2 && ctype_alpha($iso);
    }

    private ?MainAdminURLs $adminURLs = null;

    public function adminURL() : MainAdminURLs
    {
        if(!isset($this->adminURLs)) {
            $this->adminURLs = new MainAdminURLs();
        }

        return $this->adminURLs;
    }

    public function createSettingsManager(Application_Formable $formable, ?Application_Countries_Country $country) : CountrySettingsManager
    {
        return new CountrySettingsManager($formable, $country);
    }
}
