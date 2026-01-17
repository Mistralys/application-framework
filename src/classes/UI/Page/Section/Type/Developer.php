<?php

class UI_Page_Section_Type_Developer extends UI_Page_Section
{
    public function __construct(UI_Page $page)
    {
        parent::__construct($page);
        
        $this->addClass('developer');
        $this->setTitle(t('Developer panel'));
        $this->makeCollapsible(true);
    }
    
   /**
    * Adds a button to the developer panel.
    * 
    * @param UI_Button $button
    * @return UI_Page_Section_Type_Developer
    */
    public function addButton(UI_Button $button)
    {
        $button->makeBlock();
        $this->addRenderable($button);
        return $this;
    }
}