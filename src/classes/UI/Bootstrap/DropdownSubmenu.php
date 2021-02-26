<?php

require_once 'UI/Bootstrap/DropdownMenu.php';

class UI_Bootstrap_DropdownSubmenu extends UI_Bootstrap_DropdownMenu
{
    protected $title = '';
    
   /**
    * @var UI_Bootstrap_DropdownMenu
    */
    protected $menu;
    
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
    
    public function setMenu(UI_Bootstrap_DropdownMenu $menu)
    {
        $this->menu = $menu;
        return $this;
    }
    
    protected function _render()
    {
        $html = parent::render();
        
        $this->addLIClass('dropdown-submenu');
        
        return 
        '<li class="'.implode(' ', $this->liClasses).'">'.
            '<a tabindex="-1" href="javascript:void(0)">'.$this->title.'</a>'.
            $html.
        '</li>';
    }
    
    protected $liClasses = array();
    
    public function addLIClass($class)
    {
        if(!in_array($class, $this->liClasses)) {
            $this->liClasses[] = $class;
        }
        
        return $this;
    }
    
    public function makeOpenUp()
    {
        $this->menu->addClass('dropup');
        return $this;
    }
    
    public function makeOpenLeft()
    {
        $this->addLIClass('pull-left');
        return $this;
    }
}