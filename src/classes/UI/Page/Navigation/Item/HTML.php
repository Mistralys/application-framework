<?php

class UI_Page_Navigation_Item_HTML extends UI_Page_Navigation_Item
{
   /**
    * @var string
    */
    protected $html = '';
    
    public function __construct(UI_Page_Navigation $nav, $id, $html)
    {
        parent::__construct($nav, $id);
        $this->html = $html;
    }
    
    public function getType()
    {
        return 'html';
    }
    
    public function render(array $attributes = array()) : string
    {
        if(!$this->isValid())
        {
            return '';
        }

        return $this->html;
    }
}