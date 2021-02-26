<?php

declare(strict_types=1);

abstract class UI_Bootstrap_Tab_Renderer extends UI_Renderable
{
   /**
    * @var UI_Bootstrap_Tab
    */
    protected $tab;
    
    public function __construct(UI_Bootstrap_Tab $tab)
    {
        $this->tab = $tab;
        
        parent::__construct($tab->getUI()->getPage());
    }
    
    protected function renderLabel() : string
    {
        $label = $this->tab->getLabel();
        
        if($this->tab->hasIcon()) {
            $label = $this->tab->getIcon().' '.$label;
        }
        
        return $label;
    }
}
