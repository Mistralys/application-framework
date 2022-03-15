<?php

declare(strict_types=1);

class UI_Page_Navigation_Item_HTML extends UI_Page_Navigation_Item
{
   /**
    * @var string|number|UI_Renderable_Interface|NULL
    */
    protected $html;
    
    public function __construct(UI_Page_Navigation $nav, string $id, $html)
    {
        parent::__construct($nav, $id);

        $this->html = $html;
    }
    
    public function getType() : string
    {
        return 'html';
    }
    
    public function render(array $attributes = array()) : string
    {
        if(!$this->isValid())
        {
            return '';
        }

        return toString($this->html);
    }
}
