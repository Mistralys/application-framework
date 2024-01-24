<?php

require_once 'UI/Bootstrap.php';

class UI_Bootstrap_ButtonGroup extends UI_Bootstrap
{
   /**
    * @var UI_Button[]|UI_Bootstrap_ButtonDropdown[]
    */
    protected $buttons = array();
    
   /**
    * Adds a button to the group.
    * @param UI_Button|UI_Bootstrap_ButtonDropdown $button
    * @return UI_Bootstrap_ButtonGroup
    */
    public function addButton($button)
    {
        $this->buttons[] = $button;
        return $this;
    }
    
    protected $size = null;
    
    public function makeSmall()
    {
        return $this->makeSize('small');
    }

    public function makeMini()
    {
        return $this->makeSize('mini');
    }

    public function makeLarge()
    {
        return $this->makeSize('large');
    }
    
    protected function makeSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @param UI_Button[] $buttons
     * @return self
     */
    public function addButtons(array $buttons) : self
    {
        foreach($buttons as $button) {
            $this->addButton($button);
        }

        return $this;
    }

    protected function _render() : string
    {
        if(empty($this->buttons)) {
            return '';
        }
        
        $this->addClass('btn-group');
        
        $html =
        '<div'.$this->renderAttributes().'>';
            foreach($this->buttons as $button) {
                
                if($this->size) {
                    $button->makeSize($this->size);
                }
                
                $html .= $button->render();
            } 
            $html .=
        '</div>';
            
        return $html;
    }
}