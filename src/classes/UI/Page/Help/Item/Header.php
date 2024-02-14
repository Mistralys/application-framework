<?php

class UI_Page_Help_Item_Header extends UI_Page_Help_Item
{
    protected function _render() : string
    {
        $this->addClass('help-header');
        
        $text = $this->getStringOption('text');
        
        return sprintf(
            '<h3 class="%s">%s</h3>',
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
    * Sets and replaces the header's text.
    * 
    * @param string|number|UI_Renderable_Interface $text
    * @return UI_Page_Help_Item_Header
    */
    public function setText($text)
    {
        return $this->setOption('text', toString($text));
    }
}
