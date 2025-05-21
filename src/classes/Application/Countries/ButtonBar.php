<?php
/**
 * @package Maileditor
 * @subpackage Countries
 */

declare(strict_types=1);

use Application\Exception\DisposableDisposedException;
use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\Interfaces\ClassableInterface;
use AppUtils\Interfaces\OptionableInterface;
use AppUtils\Traits\ClassableTrait;
use AppUtils\Traits\OptionableTrait;
use AppUtils\URLInfo;
use function AppUtils\parseURL;

/**
 * UI widget to create and display a country selection
 * that persists the user's choice in their user settings.
 *
 * @package Maileditor
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Countries_ButtonBar extends UI_Renderable implements ClassableInterface, OptionableInterface
{
    public const ERROR_INVALID_COUNTRY_FOR_LINK = 54601;

    public const REQUEST_PARAM_SELECT_COUNTRY = 'select_country';
    public const OPTION_ENABLE_STORAGE = 'enableStorage';
    public const OPTION_DISPLAY_THRESHOLD = 'displayThreshold';
    public const OPTION_ENABLE_LABEL = 'enableLabel';
    public const OPTION_LABEL = 'label';

    use ClassableTrait;
    use OptionableTrait;
    
    protected Application_Countries $collection;
    protected bool $withInvariant = false;
    protected string $id;
    protected Application_User $user;
    protected Application_Request $request;
    protected int $countryID = 0;
    protected ?Application_Countries_Country $country = null;
    protected Application_Countries_FilterCriteria $filters;
    protected URLInfo $baseURL;
    protected string $storageKey;
    protected bool $loaded = false;

   /**
    * @var Application_Countries_Country[]
    */
    protected array $countries = array();

    /**
     * @param string $id A freeform ID to tie the country selection to: Used to namespace the setting under which the country is stored.
     * @param string $baseURL
     * @param int[] $limitToCountries List of country IDs to limit the selection to.
     *
     * @throws DisposableDisposedException
     * @throws DBHelper_Exception
     * @throws BaseClassHelperException
     */
    public function __construct(string $id, string $baseURL, array $limitToCountries = array())
    {
        parent::__construct();

        $this->id = $id;
        $this->storageKey = self::getStorageName($id);
        $this->collection = Application_Countries::getInstance();
        $this->user = $this->driver->getUser();
        $this->request = $this->driver->getRequest();
        $this->filters = $this->collection->getFilterCriteria();
        $this->baseURL = parseURL($baseURL);

        if(!empty($limitToCountries)) {
            $this->filters->selectCountryIDs($limitToCountries);
        }
    }

    /**
     * Gets the setting name under which the country is stored in the user's settings.
     * @param string $barID The button bar ID.
     * @return string
     */
    public static function getStorageName(string $barID) : string
    {
        return md5('countries-button-bar-'.$barID);
    }
    
    public function getDefaultOptions() : array
    {
        return array(
            self::OPTION_DISPLAY_THRESHOLD => 2,
            self::OPTION_ENABLE_LABEL => false,
            self::OPTION_LABEL => t('Country selection'),
            self::OPTION_ENABLE_STORAGE => true
        );
    }
    
   /**
    * Sets the minimum number of items that have to be
    * present for the bar to be displayed. Below this
    * number, it will not be displayed.
    * 
    * @param int $amount Set to 0 to disable hiding.
    * @return Application_Countries_ButtonBar
    */
    public function setDisplayThreshold(int $amount) : Application_Countries_ButtonBar
    {
        return $this->setOption(self::OPTION_DISPLAY_THRESHOLD, $amount);
    }
    
   /**
    * Whether to display the label next to the buttons.
    * Defaults to false.
    * 
    * @param bool $enable
    * @return Application_Countries_ButtonBar
    */
    public function enableLabel(bool $enable=true) : Application_Countries_ButtonBar
    {
        return $this->setOption(self::OPTION_ENABLE_LABEL, $enable);
    }

    /**
     * Sets the label of the selector, which is shown beside the buttons.
     *
     * @param string|number|UI_Renderable_Interface $label
     * @return Application_Countries_ButtonBar
     * @throws UI_Exception
     */
    public function setLabel($label) : Application_Countries_ButtonBar
    {
        return $this->setOption(self::OPTION_LABEL, toString($label));
    }

    /**
     * Sets whether the storage of the selected country is enabled.
     *
     * If turned off, the country will not be stored in the user's
     * settings and must be selected manually via {@see self::selectCountry()}
     * or the request variable {@see self::REQUEST_PARAM_SELECT_COUNTRY}.
     *
     * @param bool $enabled
     * @return self
     */
    public function setStorageEnabled(bool $enabled) : self
    {
        return $this->setOption(self::OPTION_ENABLE_STORAGE, $enabled);
    }

    public function isStorageEnabled() : bool
    {
        return $this->getBoolOption(self::OPTION_ENABLE_STORAGE);
    }

   /**
    * Prepares the URL so only the country ID needs to be appended.
    * 
    * @param string $baseURL
    * @return string
    */
    protected function parseBaseURL(string $baseURL) : string
    {
        if(strpos($baseURL, '?') === false) {
            $baseURL .= '?';
        } else {
            $baseURL .= '&';
        }
        
        $baseURL .= self::REQUEST_PARAM_SELECT_COUNTRY.'=';
        
        return $baseURL;
    }
    
   /**
    * Retrieves the button bar ID, as specified on its creation.
    * @return string
    */
    public function getID() : string
    {
        return $this->id;
    }
    
   /**
    * Retrieves the country filter settings to be 
    * able to customize the selection as needed.
    * 
    * @return Application_Countries_FilterCriteria
    */
    public function getFilters() : Application_Countries_FilterCriteria
    {
        return $this->filters;
    }
    
   /**
    * Retrieves the selected country ID.
    * @return int
    */
    public function getCountryID() : int
    {
        $this->load();
        
        return $this->countryID;
    }
    
   /**
    * Retrieves the selected country.
    * @return Application_Countries_Country|NULL
    */
    public function getCountry() : ?Application_Countries_Country
    {
        $this->load();
        
        return $this->country;
    }
    
   /**
    * Checks whether the specified country is the currently selected one.
    * 
    * @param Application_Countries_Country $country
    * @return boolean
    */
    public function isSelected(Application_Countries_Country $country) : bool
    {
        $this->load();
        
        return $country->getID() === $this->countryID;
    }
    
    protected function load() : void
    {
        if($this->loaded) {
            return;
        }

        $this->loaded = true;
        
        $this->filters->excludeInvariant(!$this->withInvariant);
        
        $this->countries = $this->filters->getItemsObjects();
        
        if(count($this->countries) === 0) {
            return;
        }

        $countryID = $this->resolveCountryID();

        if($countryID === null)
        {
            $country = $this->countries[0];
        }
        else 
        {
            $country = $this->collection->getByID($countryID);
        }

        $this->selectCountry($country);
    }

    private function resolveCountryID() : ?int
    {
        return $this->getIDFromRequest() ?? $this->getIDFromUser();
    }

    public function getIDFromUser() : ?int
    {
        if(!$this->isStorageEnabled()) {
            return null;
        }

        $countryID = $this->user->getIntSetting($this->storageKey);

        if (in_array($countryID, $this->getCountryIDs())) {
            return $countryID;
        }

        return null;
    }

    public function getIDFromRequest() : ?int
    {
        $countryID = (int)$this->request->registerParam(self::REQUEST_PARAM_SELECT_COUNTRY)
            ->setInteger()
            ->get();

        if($countryID !== 0 && in_array($countryID, $this->getCountryIDs())) {
            return $countryID;
        }

        return null;
    }

   /**
    * Retrieves all countries selectable in the button bar.
    * 
    * @return Application_Countries_Country[]
    */
    public function getCountries() : array
    {
        $this->load();
        
        return $this->countries;
    }
    
   /**
    * Retrieves the URL that can be used to select the specified country.
    * 
    * @param Application_Countries_Country $country
    * @throws Application_Exception
    * @return string
    * 
    * @see Application_Countries_ButtonBar::ERROR_INVALID_COUNTRY_FOR_LINK
    */
    public function getCountryLink(Application_Countries_Country $country) : string
    {
        $this->load();
        
        if($this->isSelectable($country))
        {
            $this->baseURL->setParam(self::REQUEST_PARAM_SELECT_COUNTRY, (string)$country->getID());
            return $this->baseURL->getNormalized();
        }
        
        throw new Application_Exception(
            'Invalid country for link.',
            sprintf(
                'Cannot get link for country [%s], it is not selectable in the button bar.',
                $country->getISO()
            ),
            self::ERROR_INVALID_COUNTRY_FOR_LINK
        );
    }
    
   /**
    * Checks whether the specified country is selectable
    * in the button bar.
    * 
    * @param Application_Countries_Country $country
    * @return bool
    */
    public function isSelectable(Application_Countries_Country $country) : bool
    {
        return in_array($country->getID(), $this->getCountryIDs());
    }
    
   /**
    * Retrieves the IDs of all countries selectable in the button bar.
    * 
    * @return int[]
    */
    public function getCountryIDs() : array
    {
        $this->load();
        
        $result = array();
        $countries = $this->getCountries();
        
        foreach($countries as $country)
        {
            $result[] = $country->getID();
        }
        
        return $result;
    }
    
    protected function _render() : string
    {
        $this->load();
        $this->save();
        
        if(count($this->countries) < $this->getIntOption(self::OPTION_DISPLAY_THRESHOLD)) {
            return '';
        }
        
        return $this->ui->createTemplate('ui/countries/button-bar')
            ->setVar('bar', $this)
            ->render();
    }
    
    public function isLabelEnabled() : bool
    {
        return $this->getBoolOption(self::OPTION_ENABLE_LABEL);
    }
    
    public function getLabel() : string
    {
        return $this->getStringOption(self::OPTION_LABEL);
    }

    /**
     * Manually selects the country, overriding the user's settings
     * and the current request.
     *
     * @param Application_Countries_Country $country
     * @return $this
     */
    public function selectCountry(Application_Countries_Country $country) : self
    {
        $this->load();

        $this->country = $country;
        $this->countryID = $country->getID();

        return $this;
    }

    /**
     * Saves the currently selected country in the user's settings.
     *
     * NOTE: Only used when storage is enabled. This is called
     * automatically when the bar is rendered.
     *
     * @return $this
     */
    public function save() : self
    {
        if($this->isStorageEnabled()) {
            $this->user->setIntSetting($this->storageKey, $this->countryID);
            $this->user->saveSettings();
        }

        return $this;
    }
}
