<?php

class UI_Page_Navigation_Item_Search extends UI_Page_Navigation_Item
{
    const ERROR_INVALID_CALLBACK = 22101;
    
    protected $callback;
    
    protected $scopes = array();
    
   /**
    * @var Application_Request
    */
    protected $request;
    
   /**
    * @var array[string]string
    */
    protected $hiddens = array();
    
    public function __construct(UI_Page_Navigation $nav, $id, $callback)
    {
        parent::__construct($nav, $id);
        $this->callback = $callback;
        
        Application::requireCallableValid($callback, self::ERROR_INVALID_CALLBACK);
    }
    
    public function initDone()
    {
        if($this->request->getParam('navsearch-name') == $this->getName()) {
            $this->handleSubmitted();
        }
    }
    
    public function getType()
    {
        return 'search';
    }
    
    protected $name;
    
    public function getName()
    {
        if(!isset($this->name)) {
            $driver = Application_Driver::getInstance();
            $this->name = $driver->getActiveScreen()->getURLPath().'.nav.'.$this->nav->getID().'.search';
        }
        
        return $this->name;
    }
    
    public function render($attributes = array())
    {
        if(!$this->isValid())
        {
            return '';
        }

        $this->ui->addStylesheet('ui-nav-search.css');
        
        $this->addHiddenVar('navsearch-name', $this->getName());
        
        $html =
        '<form method="post" class="nav-search '.implode(' ', $this->classes).'">'.
            $this->renderHiddens().
            '<div class="search-inputs">'.
                '<input name="search" type="text" class="search-input search-input-terms" placeholder="'.t('Search...').'"/>';
                if(!empty($this->scopes)) {
                    $html .= 
                    '<select name="scope" class="search-input search-input-scope">';
                        foreach($this->scopes as $scope) {
                            $html .= 
                            '<option value="'.$scope['name'].'">'.
                                $scope['label'].
                            '</option>';
                        }
                        $html .=
                    '</select>';
                }
                $html .=
            '</div>'.
            '<div class="search-button">'.
                UI::button()
                ->setIcon(UI::icon()->search())
                ->makeSubmit('run_search', 'yes').
            '</div>'.
        '</form>';
        
        return $html;
    }
    
    protected function renderHiddens()
    {
        $html = 
        '<div class="form-hiddens" style="display:none">';
            foreach($this->hiddens as $name => $value) {
                $html .= sprintf(
                    '<input type="hidden" name="%s" value="%s"/>',
                    $name,
                    $value
                );
            }
            $html .=
        '</div>';
        
        return $html;
    }
    
   /**
    * Makes the search appear on the right hand side of the
    * navigation bar.
    * 
    * @return UI_Page_Navigation_Item_Search
    */
    public function makeRightAligned()
    {
        return $this->addContainerClass('pull-right');
    }
    
   /**
    * Adds a hidden variable to the search form.
    * 
    * @param string $name
    * @param string $value
    * @return UI_Page_Navigation_Item_Search
    */
    public function addHiddenVar($name, $value)
    {
        $this->hiddens[$name] = $value;
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
    
    protected $minLength = 2;
    
   /**
    * Sets the minimum amount of characters for a search to be valid.
    * @param string $length
    * @return UI_Page_Navigation_Item_Search
    */
    public function setMinSearchLength($length)
    {
        if($length < 0) {
            $length = 0;
        }
        
        $this->minLength = $length;
        return $this;
    }
    
    protected function handleSubmitted()
    {
        $terms = $this->resolveTerms();
        if(!empty($terms)) {
            call_user_func(
                $this->callback, 
                $this, 
                $terms, 
                $this->resolveScope()
            );
        }
    }
    
    protected function resolveTerms()
    {
        $terms = $this->request->registerParam('search')
        ->addFilterTrim()
        ->addStripTagsFilter()
        ->addHTMLSpecialcharsFilter()
        ->get('');
        
        if(!empty($terms) || mb_strlen($terms) >= $this->minLength) {
            return $terms;
        }
        
        return null;
    }
    
    protected function resolveScope()
    {
        if(empty($this->scopes)) {
            return null;
        }
        
        $scope = $this->request->getParam('scope');
        foreach($this->scopes as $def) {
            if($def['name'] == $scope) {
                return $scope;
            }
        }
        
        return null;
    }
}