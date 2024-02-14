<?php

class UI_Page_Help_Item_Para extends UI_Page_Help_Item implements Application_Interfaces_Iconizable
{
    use Application_Traits_Iconizable;
    
    protected function _render() : string
    {
        $this->addClass('help-para');
        
        $text = $this->getStringOption('text');
        
        if($this->hasIcon()) 
        {
            $text = $this->getIcon().' '.$text;    
        }
       
        return sprintf(
            '<p class="%s">%s</p>',
            $this->classesToString(),
            $text
        );
    }
    
    public function getDefaultOptions() : array
    {
        return array(
            'text' => ''
        );
    }
    
   /**
    * Sets and replaces the paragraph's text.
    * 
    * @param string|number|UI_Renderable_Interface $text
    * @return UI_Page_Help_Item_Para
    */
    public function setText($text)
    {
        return $this->setOption('text', toString($text));
    }
    
    public function makeHint() : UI_Page_Help_Item_Para
    {
        $this->addClass('help-para-hint');
        $this->setIcon(UI::icon()->information()->makeInformation());
        
        return $this;
    }
}
