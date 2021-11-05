<?php

use AppUtils\ConvertHelper;

class UI_Page_Navigation_Item_Search extends UI_Page_Navigation_Item
{
    public const ERROR_INVALID_CALLBACK = 22101;
    public const ERROR_INVALID_SCOPE = 22102;

    /**
     * @var callable
     */
    protected $callback;
    
    protected $scopes = array();

    protected $countries = array();
    
   /**
    * @var Application_Request
    */
    protected $request;
    
   /**
    * @var array<string,string>
    */
    protected $hiddens = array();

    /**
     * @var string|NULL
     */
    protected $name = null;

    /**
     * @var bool
     */
    private $fullWidth = false;

    /**
     * @var int
     */
    protected $minLength = 2;

    /**
     * @var string
     */
    private $preSelectedSearchTerms;

    /**
     * @var string
     */
    private $preSelectedScope;

    /**
     * @param UI_Page_Navigation $nav
     * @param string $id
     * @param callable $callback
     *
     * @throws Application_Exception
     * @see UI_Page_Navigation_Item_Search::ERROR_INVALID_CALLBACK
     */
    public function __construct(UI_Page_Navigation $nav, string $id, $callback)
    {
        // ensure it contains no special characters
        $id = ConvertHelper::transliterate($id);

        parent::__construct($nav, $id);
        $this->callback = $callback;
        
        Application::requireCallableValid($callback, self::ERROR_INVALID_CALLBACK);
    }

    public function getTemplateName() : string
    {
        if($this->isFullWidth())
        {
            return 'ui/nav/search.full-width';
        }

        return 'ui/nav/search.inline';
    }

    public function getPosition() : string
    {
        if($this->isFullWidth())
        {
            return self::ITEM_POSITION_BELOW;
        }

        return self::ITEM_POSITION_INLINE;
    }

    private function createTemplate() : UI_Page_Template
    {
        return $this->ui->createTemplate($this->getTemplateName())
            ->setVar('search', $this);
    }
    
    public function initDone() : void
    {
        if($this->isSubmitted()) {
            $this->handleSubmitted();
        }
    }

    /**
     * Retrieves the name of the currently selected
     * scope, if any. When not using scopes, this
     * will always return an empty string.
     *
     * @return string
     */
    public function getSelectedScopeID() : string
    {
        $scope = strval($this->resolveScope());

        if(!empty($scope))
        {
            return $scope;
        }

        if(empty($this->scopes))
        {
            return '';
        }

        return $this->scopes[0]['name'];
    }

    /**
     * Retrieves the current search terms, if any.
     *
     * @param string $scopeID
     * @return string
     */
    public function getSearchTerms(string $scopeID='') : string
    {
        if(empty($scopeID))
        {
            $scopeID = $this->getSelectedScopeID();
        }

        return $this->resolveTerms($scopeID);
    }

    public function getSelectedCountryID(string $scopeID='') : string
    {
        return $this->resolveCountry($scopeID);
    }
    
    public function getType()
    {
        return 'search';
    }
    
    public function getName() : string
    {
        if(!isset($this->name))
        {
            $driver = Application_Driver::getInstance();
            $this->name = str_replace('.', '_', $driver->getActiveScreen()->getURLPath().'_navsearch_'.$this->nav->getID());
        }
        
        return $this->name;
    }

    /**
     * @param array<string,string> $attributes (Unused)
     * @return string
     *
     * @see template_default_ui_nav_search_inline
     * @see template_default_ui_nav_search_full_width
     */
    public function render(array $attributes = array()) : string
    {
        if(!$this->isValid())
        {
            return '';
        }

        $this->addHiddenVar($this->getSubmitElementName(), 'yes');
        
        return $this->createTemplate()->render();
    }

    public function getSubmitElementName() : string
    {
        return $this->getName().'_submit';
    }

    public function getSearchElementName(string $scope = '') : string
    {
        $name = $this->getName().'_input';

        if($this->isFullWidth())
        {
            if(empty($scope))
            {
                return $name;
            }

            return $name.'_'.$scope;
        }

        return $name;
    }

    public function getScopeElementName() : string
    {
        return $this->getName().'_scope';
    }

    public function getCountrySelectionElementName(string $scope) : string
    {
        $name = $this->getName().'_country';

        if($this->isFullWidth())
        {
            if(empty($scope))
            {
                return $name;
            }

            return $name.'_'.$scope;
        }

        return $name;
    }

    /**
     * Retrieves all variables needed to persist the
     * current search settings, when it is needed to
     * inject these into another form for example.
     *
     * @return array<string,string>
     */
    public function getPersistVars() : array
    {
        $vars = array();
        $vars[$this->getScopeElementName()] = $this->getSelectedScopeID();

        if(!empty($this->scopes))
        {
            foreach ($this->scopes as $scope)
            {
                $vars[$this->getSearchElementName($scope['name'])] = $this->resolveTerms($scope['name']);
                $vars[$this->getCountrySelectionElementName($scope['name'])] = $this->resolveCountry($scope['name']);
            }
        }
        else
        {
            $vars[$this->getSearchElementName('')] = $this->resolveTerms('');
            $vars[$this->getCountrySelectionElementName('')] = $this->resolveCountry('');
        }

        if($this->isSubmitted())
        {
            $vars[$this->getSubmitElementName()] = 'yes';
        }

        return $vars;
    }

    public function isSubmitted() : bool
    {
        return $this->request->getBool($this->getSubmitElementName());
    }
    
   /**
    * Makes the search appear on the right hand side of the
    * navigation bar.
    * 
    * @return $this
    */
    public function makeRightAligned()
    {
        return $this->addContainerClass('pull-right');
    }

    /**
     * Makes the search bar appear in full width right below the navigation.
     *
     * @return $this
     */
    public function makeFullWidth()
    {
        $this->fullWidth = true;
        return $this;
    }

    public function isFullWidth() : bool
    {
        return $this->fullWidth;
    }

    /**
     * @return array<string,string>
     */
    public function getHiddenVars() : array
    {
        return $this->hiddens;
    }

    public function getScopes() : array
    {
        return $this->scopes;
    }
    
   /**
    * Adds a hidden variable to the search form.
    * 
    * @param string $name
    * @param string $value
    * @return UI_Page_Navigation_Item_Search
    */
    public function addHiddenVar(string $name, $value)
    {
        $this->hiddens[$name] = strval($value);
        return $this;
    }
    
   /**
    * Adds a collection of hidden variables, from an
    * associative array with variable name => value pairs.
    * 
    * @param array $vars
    * @return UI_Page_Navigation_Item_Search
    */
    public function addHiddenVars($vars)
    {
        foreach($vars as $name => $value) {
            $this->addHiddenVar($name, $value);
        }
        
        return $this;
    }
    
   /**
    * Adds a search scope: this will be added to a select
    * element to allow the user to select a subset of the
    * items that are searchable. The selected scope name
    * is passed on to the search callback function.
    * 
    * @param string $name
    * @param string $label
    * @return UI_Page_Navigation_Item_Search
    */
    public function addScope($name, $label)
    {
        $this->scopes[] = array(
            'name' => $name,
            'label' => $label
        );
        
        return $this;
    }

    public function getCountries() : array
    {
        return $this->countries;
    }

    public function addCountry($name, $label): UI_Page_Navigation_Item_Search
    {
        $this->countries[] = array(
            'name' => $name,
            'label' => $label
        );

        return $this;
    }

    public function hasCountries() : bool
    {
        return !empty($this->countries);
    }

   /**
    * Sets the minimum amount of characters for a search to be valid.
    * @param int $length
    * @return UI_Page_Navigation_Item_Search
    */
    public function setMinSearchLength($length)
    {
        $length = intval($length);

        if($length < 0) {
            $length = 0;
        }
        
        $this->minLength = $length;
        return $this;
    }

    public function hasScopes() : bool
    {
        return !empty($this->scopes);
    }
    
    protected function handleSubmitted() : void
    {
        $this->log('The search has been submitted.');

        $scope = $this->resolveScope();
        $this->log(sprintf(
            'Selected scope is [%s] (using scopes: %s).',
            $scope,
            ConvertHelper::bool2string($this->hasScopes(), true))
        );

        $terms = $this->resolveTerms($scope);

        $country = $this->resolveCountry($scope);
        $this->log(sprintf(
                'Selected country is [%s] (country selection was enabled: %s).',
                $country,
                ConvertHelper::bool2string($this->hasCountries(), true))
        );

        $this->log(sprintf('Calling the search callback with search terms [%s].', $terms));

        call_user_func(
            $this->callback,
            $this,
            $terms,
            $scope,
            $country
        );
    }

    /**
     * @param string $scopeID
     * @return string
     */
    protected function resolveTerms(string $scopeID) : string
    {
        //If the search terms where pre-set for a specific scope
        if(!empty($this->preSelectedScope) && !empty($this->preSelectedSearchTerms) && $this->preSelectedScope == $scopeID)
        {
            $terms = $this->preSelectedSearchTerms;
        }else{
            $paramName = $this->getSearchElementName($scopeID);

            $terms = (string)$this->request->registerParam($paramName)
                ->addFilterTrim()
                ->addStripTagsFilter()
                ->get('');
        }
        
        if(!empty($terms) || mb_strlen($terms) >= $this->minLength) {
            return $terms;
        }
        
        return '';
    }

    /**
     * @return string
     */
    protected function resolveScope() : string
    {
        //If we pre-set the selected scope
        if(!empty($this->preSelectedScope))
        {
            return $this->preSelectedScope;
        }

        if(empty($this->scopes)) {
            return '';
        }
        
        $scopeID = $this->request->getParam($this->getScopeElementName());
        foreach($this->scopes as $def) {
            if($def['name'] === $scopeID) {
                return $scopeID;
            }
        }
        
        return '';
    }

    /**
     * @param string $scopeID
     * @return string
     */
    protected function resolveCountry(string $scopeID) : string
    {
        if(!$this->hasCountries()) {
            return '';
        }

        $countryID = $this->request->getParam($this->getCountrySelectionElementName($scopeID));

        foreach($this->countries as $def) {
            if($def['name'] === $countryID) {
                return $countryID;
            }
        }

        return '';
    }

    public function setPreSelectedScope(string $preSelectedScope)
    {
        if(in_array($preSelectedScope, array_column($this->scopes, 'name')))
        {
            $this->preSelectedScope = $preSelectedScope;
            return;
        }

        throw new Application_Exception(
            'Can\'t set the pre selected scope!',
            sprintf(
                'The pre selected scope [%s] must be part of the available scopes [%s].',
                $preSelectedScope,
                print_r($this->scopes, true)
            ),
            self::ERROR_INVALID_SCOPE
        );
    }

    public function setPreSelectedSearchTerms(string $preSelectedSearchTerms)
    {
        $this->preSelectedSearchTerms = $preSelectedSearchTerms;
    }
}
