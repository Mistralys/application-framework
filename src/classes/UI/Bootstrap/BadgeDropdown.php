<?php

class UI_Bootstrap_BadgeDropdown extends UI_Bootstrap_BaseDropdown implements UI_Interfaces_Badge
{
   /**
    * @var UI_Badge|UI_Label
    */
    protected $badge;
    
    protected function init() : void
    {
        $this->badge = UI::badge('');
    }
    
    public function makeLabel() : self
    {
        $this->badge = UI::label($this->getLabel());
        return $this;
    }
    
    protected function _render()
    {
        $label = $this->badge->getLabel();
        
        if($this->caret) 
        {
            $label .= ' '.$this->renderCaret();
        }

        $this->badge
        ->addClass('clickable')
        ->setLabel($label)
        ->setAttribute('data-toggle', 'dropdown');
        
        $this->addClass('badge-dropdown');
        
        return '<div'.$this->renderAttributes().'>'.$this->badge->render().''.$this->menu->render().'</div>';
    }

    /**
     * @param string|number|UI_Renderable_Interface|NULL $label
     * @return $this
     * @throws UI_Exception
     */
    public function setLabel($label) : self
    {
        $this->badge->setLabel($label);
        return $this;
    }
    
    public function getLabel() : string
    {
        return $this->badge->getLabel();
    }

    /**
     * @param string|number|UI_Renderable_Interface|NULL $code
     * @return $this
     * @throws Application_Exception
     */
    public function setWrapper($code) : self
    {
        $this->badge->setWrapper($code);
        return $this;
    }

    /**
     * @return $this
     */
    public function makeDangerous() : self
    {
        $this->badge->makeDangerous();
        return $this;
    }

    /**
     * @return $this
     */
    public function makeInfo() : self
    {
        $this->badge->makeInfo();
        return $this;
    }

    /**
     * @return $this
     */
    public function makeSuccess() : self
    {
        $this->badge->makeSuccess();
        return $this;
    }

    /**
     * @return $this
     */
    public function makeWarning() : self
    {
        $this->badge->makeWarning();
        return $this;
    }

    /**
     * @return $this
     */
    public function makeInverse() : self
    {
        $this->badge->makeInverse();
        return $this;
    }

    /**
     * @return $this
     * @throws Application_Exception
     */
    public function makeInactive() : self
    {
        $this->badge->makeInactive();
        return $this;
    }

    /**
     * @return $this
     */
    public function cursorHelp() : self
    {
        $this->badge->cursorHelp();
        return $this;
    }

    /**
     * @return $this
     */
    public function makeLarge() : self
    {
        $this->badge->makeLarge();
        return $this;
    }

    /**
     * @param UI_Icon|NULL $icon
     * @return $this
     */
    public function setIcon(?UI_Icon $icon) : self
    {
        $this->badge->setIcon($icon);
        return $this;
    }
    
    public function hasIcon() : bool
    {
        return $this->badge->hasIcon();
    }
    
    public function getIcon() : ?UI_Icon
    {
        return $this->badge->getIcon();
    }
}
