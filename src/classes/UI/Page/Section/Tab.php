<?php

declare(strict_types=1);

use AppUtils\ConvertHelper;
use AppUtils\Interfaces\OptionableInterface;
use AppUtils\Interfaces\StringableInterface;
use AppUtils\Traits\OptionableTrait;

class UI_Page_Section_Tab
    extends UI_Renderable
    implements
    OptionableInterface,
    Application_Interfaces_Iconizable
{
    use OptionableTrait;
    use Application_Traits_Iconizable;
    
    protected UI_Page_Section $section;
    protected string $name;
    protected string $label;

    /**
     * @param UI_Page_Section $section
     * @param string $name
     * @param string|number|StringableInterface $label
     * @throws UI_Exception
     */
    public function __construct(UI_Page_Section $section, string $name, $label)
    {
        parent::__construct($section->getPage());

        $this->section = $section;
        $this->name = $name;
        $this->label = toString($label);
    }

    public function getDefaultOptions() : array
    {
        return array();
    }

   /**
    * @return string
    */
    public function getLabel() : string
    {
        return $this->label;
    }
    
    public function getName() : string
    {
        return $this->name;
    }
    
    public function getSection() : UI_Page_Section
    {
        return $this->section;
    }
    
    protected function _render() : string
    {
        return '';
    }

    public const TARGET_LINK = 'link';
    
   /**
    * Specifies a URL to have the tab link to.
    * 
    * @param string $url
    * @param string|NULL $target The target window to load the URL in
    * @return $this
    */
    public function link(string $url, ?string $target=null) : self
    {
        return $this->setTarget(
            self::TARGET_LINK, 
            array(
                'url' => str_replace('&amp;', '&', $url),
                'target' => $target
            )
        );
    }

    /**
     * @var array{type:string,config:array<string,mixed>}|null
     */
    protected ?array $target = null;

    /**
     * @param string $type
     * @param array<string,mixed> $config
     * @return $this
     */
    protected function setTarget(string $type, array $config = array()) : self
    {
        $this->target = array(
            'type' => $type,
            'config' => $config
        );
        
        return $this;
    }
    
    public function isActive() : bool
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
                
                $params = ConvertHelper::parseQueryString($info['query']);
                
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
            return $this->target['config']['url'] ?? '';
        }
        
        return '';
    }

    public function getURLTarget() : string
    {
        if($this->isLink()) {
            return $this->target['config']['target'] ?? '';
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
    
    public function renderLabel() : string
    {
        $label = $this->getLabel();
        
        if($this->hasIcon()) {
            $label = $this->icon.' '.$label;
        }
        
        return $label;
    }
}
