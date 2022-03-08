<?php

class UI_Bootstrap_BadgeDropdown extends UI_Bootstrap_BaseDropdown implements UI_Interfaces_Badge
{
   /**
    * @var UI_Badge
    */
    protected $badge;
    
    protected function init() : void
    {
        $this->badge = $this->ui->badge('');
    }
    
    public function makeLabel()
    {
        $this->badge = $this->ui->label('');
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
    
    public function setLabel(string $label)
    {
        $this->badge->setLabel($label);
        return $this;
    }
    
    public function getLabel() : string
    {
        return $this->badge->getLabel();
    }
    
    public function setWrapper(string $code)
    {
        $this->badge->setWrapper($code);
        return $this;
    }
    
    public function makeDangerous()
    {
        $this->badge->makeDangerous();
        return $this;
    }
    
    public function makeInfo()
    {
        $this->badge->makeInfo();
        return $this;
    }
    
    public function makeSuccess()
    {
        $this->badge->makeSuccess();
        return $this;
    }
    
    public function makeWarning()
    {
        $this->badge->makeWarning();
        return $this;
    }
    
    public function makeInverse()
    {
        $this->badge->makeInverse();
        return $this;
    }
    
    public function makeInactive()
    {
        $this->badge->makeInactive();
        return $this;
    }
    
    public function cursorHelp()
    {
        $this->badge->cursorHelp();
        return $this;
    }
    
    public function makeLarge()
    {
        $this->badge->makeLarge();
        return $this;
    }
    
    public function setIcon(UI_Icon $icon)
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
