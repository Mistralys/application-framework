<?php

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
    
    protected $autoSelect = true;
    
    protected $urlParams = array();
    
    public function __construct(Application_Countries $collection)
    {
        parent::__construct();
        
        $this->collection = $collection;
        $this->filters = $collection->getFilterCriteria();
    }
    
    public function getCollection()
    {
        return $this->collection;
    }
    
    public function getFilterCriteria()
    {
        return $this->filters;
    }
    
    public function getCountries()
    {
        if(!isset($this->countries)) {
            $this->countries = $this->filters->getItemsObjects();
        }
        
        return $this->countries;
    }
    
    public function setAutoSelect($enabled=true)
    {
        $this->autoSelect = $enabled;
        return $this;
    }
    
    public function setActiveCountry(Application_Countries_Country $country)
    {
        $this->activeCountry = $country;
        return $this;
    }
    
    public function setURLParam($name, $value)
    {
        $this->urlParams[$name] = $value;
        return $this;
    }
    
    public function setURLParamsByArea(Application_Admin_Skeleton $area)
    {
        return $this->setURLParams($area->getPageParams());
    }
    
    public function setURLParams($params)
    {
        foreach($params as $name => $value) {
            $this->setURLParam($name, $value);
        }
        
        return $this;
    }
    
    protected $autoSelected = false;
    
    protected function autoSelect()
    {
        if($this->autoSelected || isset($this->activeCountry)) {
            return;
        }
        
        $this->activeCountry = $this->collection->getByRequest();
        if(isset($this->activeCountry)) {
            return;
        }
        
        $countries = $this->getCountries();
        $this->activeCountry = array_shift($countries);
    }
    
    public function getActiveCountry()
    {
        $this->autoSelect();
        
        return $this->activeCountry;
    }
    
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
            
            if($id == $activeID) {
                $btn->makePrimary();
            }
            
            $group->addButton($btn);
        }
        
        return $group->render();
    }
}