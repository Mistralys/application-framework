<?php
/**
 * File containing the class {@see Application_Countries_Navigator}.
 *
 * @package Application
 * @subpackage Countries
 * @see Application_Countries_Navigator
 */

declare(strict_types=1);

/**
 * Utility class used to render a country navigation element,
 * using a button bar to easily select a language.
 *
 * @package Application
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Countries_Navigator extends UI_Renderable
{
   /**
    * @var Application_Countries
    */
    protected $collection;
    
   /**
    * @var Application_Countries_FilterCriteria
    */
    protected $filters;
    
   /**
    * @var Application_Countries_Country|NULL
    */
    protected $activeCountry;
    
   /**
    * @var Application_Countries_Country[]
    */
    protected $countries;

    /**
     * @var bool
     */
    protected $autoSelect = true;

    /**
     * @var array<string,string|number>
     */
    protected $urlParams = array();

    /**
     * @var bool
     */
    private $autoSelected = false;

    /**
     * @var bool
     */
    private $savingEnabled = false;

    /**
     * @var string
     */
    private $storageName;

    public function __construct(Application_Countries $collection)
    {
        parent::__construct();
        
        $this->collection = $collection;
        $this->filters = $collection->getFilterCriteria();
    }
    
    public function getCollection() : Application_Countries
    {
        return $this->collection;
    }
    
    public function getFilterCriteria() : Application_Countries_FilterCriteria
    {
        return $this->filters;
    }

    /**
     * @return Application_Countries_Country[]
     */
    public function getCountries() : array
    {
        if(!isset($this->countries)) {
            $this->countries = $this->filters->getItemsObjects();
        }
        
        return $this->countries;
    }
    
    public function setAutoSelect(bool $enabled=true) : Application_Countries_Navigator
    {
        $this->autoSelect = $enabled;
        return $this;
    }
    
    public function setActiveCountry(Application_Countries_Country $country) : Application_Countries_Navigator
    {
        $this->activeCountry = $country;
        return $this;
    }

    /**
     * @param string $name
     * @param string|number|NULL $value
     * @return $this
     */
    public function setURLParam(string $name, $value) : Application_Countries_Navigator
    {
        $param = (string)$value;

        if(!empty($param))
        {
            $this->urlParams[$name] = $param;
        }

        return $this;
    }
    
    public function setURLParamsByArea(Application_Admin_Skeleton $area) : Application_Countries_Navigator
    {
        return $this->setURLParams($area->getPageParams());
    }

    /**
     * @param array<string,string|number|NULL> $params
     * @return $this
     */
    public function setURLParams(array $params)  : Application_Countries_Navigator
    {
        foreach($params as $name => $value)
        {
            $this->setURLParam($name, $value);
        }
        
        return $this;
    }

    private function autoSelect() : void
    {
        if($this->autoSelected || isset($this->activeCountry))
        {
            return;
        }

        // A country has been specified in the request, use that.
        $this->activeCountry = $this->collection->getByRequest();
        if(isset($this->activeCountry))
        {
            $this->saveActiveCountry($this->activeCountry);
            return;
        }

        // Use the saved country, if any.
        $country = $this->getSavedCountry();
        if($country !== null)
        {
            $this->activeCountry = $country;
            return;
        }

        // Use the first country in the list as final fallback.
        $countries = $this->getCountries();
        $this->activeCountry = array_shift($countries);
    }

    private function saveActiveCountry(Application_Countries_Country $country) : void
    {
        if($this->savingEnabled === true)
        {
            Application_Driver::setSetting($this->storageName, (string)$country->getID());
        }
    }

    private function getSavedCountry() : ?Application_Countries_Country
    {
        if($this->savingEnabled === false)
        {
            return null;
        }

        $id = (int)Application_Driver::getSetting($this->storageName);

        if($this->collection->idExists($id))
        {
            return $this->collection->getByID($id);
        }

        return null;
    }
    
    public function getActiveCountry() : Application_Countries_Country
    {
        $this->autoSelect();
        
        return $this->activeCountry;
    }

    /**
     * @return string
     */
    protected function _render()
    {
        $this->autoSelect();
        
        $group = $this->ui->createButtonGroup();
        
        $countries = $this->getCountries();
        $activeID = null;
        if(isset($this->activeCountry)) {
            $activeID = $this->activeCountry->getID();
        }
        
        $request = $this->driver->getRequest();
        
        foreach($countries as $country)
        {
            if($country->isCountryIndependent()) {
                continue;
            }

            $id = $country->getID();
            
            $params = $this->urlParams;
            $params['country_id'] = $id;
            
            $btn = UI::button(strtoupper($country->getISO()))
            ->link($request->buildURL($params));
            
            if($id === $activeID) {
                $btn->makePrimary();
            }
            
            $group->addButton($btn);
        }
        
        return $group->render();
    }

    /**
     * Enables storing the active country ID, to restore it
     * automatically if no country is specifically selected
     * in the request parameters.
     *
     * @param string $storageName Used to identify the navigator: all instances using the same name share the stored country setting.
     * @return $this
     */
    public function enableCountryStorage(string $storageName) : Application_Countries_Navigator
    {
        $this->savingEnabled = true;
        $this->storageName = 'countries_navigator_'.$storageName;
        return $this;
    }
}
