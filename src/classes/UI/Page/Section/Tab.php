<?php

use AppUtils\Interface_Optionable;
use AppUtils\Traits_Optionable;

/**
 * 
 * @method UI_Page_Section_Tab setIcon($icon)
 */
class UI_Page_Section_Tab extends UI_Renderable implements UI_Renderable_Interface, Interface_Optionable, Application_Interfaces_Iconizable
{
    use Traits_Optionable;
    use Application_Traits_Iconizable;
    
    protected $section;
    
    protected $name;
    
    protected $label;
    
    public function __construct(UI_Page_Section $section, $name, $label)
    {
        $this->section = $section;
        $this->name = $name;
        $this->label = $label;
    }

    public function getDefaultOptions() : array
    {
        return array();
    }

   /**
    * @return string
    */
    public function getLabel()
    {
        return $this->label;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
   /**
    * @return UI_Page_Section
    */
    public function getSection()
    {
        return $this->section;
    }
    
    protected function _render()
    {
        return '';
    }

    const TARGET_LINK = 'link';
    
   /**
    * Specifies a URL to have the tab link to.
    * 
    * @param string $url
    * @param string|NULL $target The target window to load the URL in
    * @return UI_Page_Section_Tab
    */
    public function link($url, $target=null)
    {
        return $this->setTarget(
            self::TARGET_LINK, 
            array(
                'url' => str_replace('&amp;', '&', $url),
                'target' => $target
            )
        );
    }
        
    protected $target;
    
    protected function setTarget($type, $config)
    {
        $this->target = array(
            'type' => $type,
            'config' => $config
        );
        
        return $this;
    }
    
    public function isActive()
    {
        if(!isset($this->target)) {
            return false;
        }
        
        switch($this->target['type']) 
        {
            case self::TARGET_LINK:
                $url = $this->getURL();
                $info = parse_url($url);
                
                if(!isset($info['query'])) {
                    return false;
                }
                
                $params = \AppUtils\ConvertHelper::parseQueryString($info['query']);
                
                $request = Application_Request::getInstance();
                
                $match = true;
                foreach($params as $param => $value) {
                    if($request->getParam($param) != $value) {
                        $match = false;
                        break;
                    }
                }
                
                return $match;
        }
        
        return false;
    }
    
    public function getURL() : string
    {
        if($this->isLink()) {
            return strval($this->target['config']['url']);
        }
        
        return '';
    }

    public function getURLTarget() : string
    {
        if($this->isLink()) {
            return strval($this->target['config']['target']);
        }

        return '';
    }

    public function isTargetType(string $type) : bool
    {
        return isset($this->target) && $this->target['type'] === $type;
    }
    
    public function isLink() : bool
    {
        return $this->isTargetType(self::TARGET_LINK);
    }
    
    public function renderLabel()
    {
        $label = $this->getLabel();
        
        if($this->hasIcon()) {
            $label = $this->icon.' '.$label;
        }
        
        return $label;
    }
}
