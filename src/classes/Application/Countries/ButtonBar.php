<?php
/**
 * File containing the {@link Application_Countries_ButtonBar} class.
 * @package Maileditor
 * @subpackage Countries
 * @see Application_Countries_ButtonBar
 */

declare(strict_types=1);

use AppUtils\Traits_Classable;
use AppUtils\Traits_Optionable;
use AppUtils\Interface_Classable;
use AppUtils\Interface_Optionable;

/**
 * UI widget to create and display a country selection
 * that persists the user's choice in their user settings.
 *
 * @package Maileditor
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Countries_ButtonBar extends UI_Renderable implements Interface_Classable, Interface_Optionable
{
    public const ERROR_INVALID_COUNTRY_FOR_LINK = 54601;
    
    use Traits_Classable;
    use Traits_Optionable;
    
   /**
    * @var Application_Countries
    */
    protected $collection;
    
   /**
    * @var bool
    */
    protected $withInvariant = false;
    
   /**
    * @var string
    */
    protected $id;
    
   /**
    * @var Application_User
    */
    protected $user;
    
   /**
    * @var Application_Request
    */
    protected $request;
    
   /**
    * @var int
    */
    protected $countryID = 0;
    
   /**
    * @var Application_Countries_Country|NULL
    */
    protected $country = null;
    
   /**
    * @var Application_Countries_FilterCriteria
    */
    protected $filters;
    
   /**
    * @var Application_Countries_Country[]
    */
    protected $countries = array();
    
   /**
    * @var string
    */
    protected $baseURL;
    
   /**
    * @var string
    */
    protected $storageKey;
    
   /**
    * @var string
    */
    protected $urlParamName = 'select_country';
    
    protected $loaded = false;
    
   /**
    * @param string $id A freeform ID to tie the country selection to: used to namespace the setting under which the country is stored.
    */
    public function __construct(string $id, string $baseURL)
    {
        parent::__construct();

        $this->id = $id;
        $this->storageKey = md5('countries-button-bar-'.$id);
        $this->collection = Application_Countries::getInstance();
        $this->user = $this->driver->getUser();
        $this->request = $this->driver->getRequest();
        $this->filters = $this->collection->getFilterCriteria();
        $this->baseURL = $this->parseBaseURL($baseURL);
    }
    
    public function getDefaultOptions() : array
    {
        return array(
            'displayThreshold' => 2,
            'enableLabel' => false,
            'label' => t('Country selection')
        );
    }
    
   /**
    * Sets the minimum amount of items that have to be
    * present for the bar to be displayed. Below this
    * number, it will not be displayed.
    * 
    * @param int $amount Set to 0 to disable hiding.
    * @return Application_Countries_ButtonBar
    */
    public function setDisplayThreshold(int $amount) : Application_Countries_ButtonBar
    {
        return $this->setOption('displayThreshold', $amount);
    }
    
   /**
    * Whether to display the label beside the buttons. 
    * Defaults to false.
    * 
    * @param bool $enable
    * @return Application_Countries_ButtonBar
    */
    public function enableLabel(bool $enable=true) : Application_Countries_ButtonBar
    {
        return $this->setOption('enableLabel', $enable);
    }
    
   /**
    * Sets the label of the selector, which is shown beside the buttons.
    * 
    * @param string|number|UI_Renderable_Interface $label
    * @return Application_Countries_ButtonBar
    */
    public function setLabel($label) : Application_Countries_ButtonBar
    {
        return $this->setOption('label', toString($label));
    }
    
   /**
    * Prepares the URL so only the country ID needs to be appended.
    * 
    * @param string $baseURL
    * @return string
    */
    protected function parseBaseURL(string $baseURL) : string
    {
        if(!strstr($baseURL, '?')) {
            $baseURL .= '?';
        } else {
            $baseURL .= '&';
        }
        
        $baseURL .= $this->urlParamName.'=';
        
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
    public function isSelected(Application_Countries_Country $country)
    {
        $this->load();
        
        return $country->getID() == $this->countryID;
    }
    
    protected function load() : void
    {
        if($this->loaded)
        {
            return;
        }
        
        $this->filters->excludeInvariant(!$this->withInvariant);
        
        $this->countries = $this->filters->getItemsObjects();
        
        if(count($this->countries) == 0) {
            return;
        }
        
        $countryID = intval($this->request->registerParam('select_country')
        ->setInteger()
        ->setEnum($this->filters->getIDs())
        ->get($this->user->getIntSetting($this->storageKey)));
        
        if(empty($countryID))
        {
            $country = $this->countries[0];
        }
        else 
        {
            $country = $this->collection->getByID($countryID);
        }

        $this->country = $country;
        $this->countryID = $country->getID();
        
        $this->user->setIntSetting($this->storageKey, $this->countryID);
    }
    
   /**
    * Retrieves all countries selectable in the button bar.
    * 
    * @return Application_Countries_Country[]
    */
    public function getCountries()
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
            return $this->baseURL.$country->getID();
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
    public function getCountryIDs()
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
    
    protected function _render()
    {
        $this->load();
        
        if(count($this->countries) < $this->getIntOption('displayThreshold')) {
            return '';
        }
        
        return $this->ui->createTemplate('ui/countries/button-bar')
        ->setVar('bar', $this)
        ->render();
    }
    
    public function isLabelEnabled() : bool
    {
        return $this->getBoolOption('enableLabel');
    }
    
    public function getLabel() : string
    {
        return $this->getStringOption('label');
    }
}
