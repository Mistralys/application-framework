<?php

declare(strict_types=1);

class Application_Countries_Country_Icon extends UI_Renderable
{
    protected Application_Countries_Country $country;

    /**
     * @var array<int,bool>
     */
    protected static array $cssLoaded = array();
    
    public function __construct(Application_Countries_Country $country)
    {
        parent::__construct();
        
        $this->country = $country;
        
        if(!isset(self::$cssLoaded[$this->uiKey])) {
            self::$cssLoaded[$this->uiKey] = false;
        }
    }
 
    protected function _render() : string
    {
        if($this->country->isInvariant()) {
            return '';
        }
        
        if(!self::$cssLoaded[$this->uiKey]) {
            $this->ui->addVendorStylesheet('lipis/flag-icons', 'css/flag-icons.min.css');
        }
        
        return '<span class="fi fi-'.$this->country->getAlpha2().'"></span>';
    }
}
