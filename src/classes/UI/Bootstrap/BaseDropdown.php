<?php

abstract class UI_Bootstrap_BaseDropdown extends UI_Bootstrap implements Application_Interfaces_Iconizable
{
    use Application_Traits_Iconizable;
    
    /**
     * @var UI_Bootstrap_DropdownMenu
     */
    protected $menu;
    
    protected $label;
    
    protected $caret = true;

    protected $isLink = false;
    
    protected $inNavigation = false;
    
    public function __construct($ui)
    {
        parent::__construct($ui);
        $this->menu = $this->ui->createDropdownMenu();
        $this->init();
    }
    
    protected function init()
    {
        
    }
    
   /**
    * Replaces the menu of the dropdown with the specified one.
    * 
    * @param UI_Bootstrap_DropdownMenu $menu
    * @return UI_Bootstrap_BaseDropdown
    */
    public function setMenu(UI_Bootstrap_DropdownMenu $menu)
    {
        $this->menu = $menu;
        return $this;
    }
    
    public function makeNavItem()
    {
        $this->inNavigation = true;
        return $this;
    }

    public function setLabel(string $label)
    {
        $this->label = $label;
        return $this;
    }
    
   /**
    * @return UI_Bootstrap_DropdownMenu
    */
    public function getMenu()
    {
        return $this->menu;
    }
    
   /**
    * Creates and adds a new anchor menu item.
    * @param string $label
    * @param string $url
    * @return UI_Bootstrap_DropdownAnchor
    */
    public function addLink($label, $url)
    {
        return $this->menu->addLink($label, $url);
    }
    
   /**
    * Creates and adds a new anchor menu item that is
    * linked to the specified javascript statement.
    * 
    * @param string $label
    * @param string $statement
    * @return UI_Bootstrap_DropdownAnchor
    */
    public function addClickable($label, $statement)
    {
        return $this->menu->addClickable($label, $statement);
    }
    
   /**
    * Adds a header to the dropdown, to group items.
    * @param string $label
    * @return UI_Bootstrap_DropdownHeader
    */
    public function addHeader($label)
    {
        return $this->menu->addHeader($label);
    }
    
    public function addSeparator()
    {
        return $this->menu->addSeparator();
    }
    
    public function addStatic($content)
    {
        return $this->menu->addStatic($content);
    }
    
    public function noCaret()
    {
        $this->caret = false;
        return $this;
    }
    
    protected $layout = 'default';
    
    public function setType($type)
    {
        $this->layout = $type;
        return $this;
    }
    
    public function makeSuccess()
    {
        return $this->setType('success');
    }
    
    public function makeInfo()
    {
        return $this->setType('info');
    }
    
    public function render() : string
    {
        if(!$this->menu->hasItems()) {
            return '';
        }
        
        return $this->_render();
    }
    
    public function moveAfter($whichItem, $afterItem)
    {
        $this->menu->moveAfter($whichItem, $afterItem);
        return $this;
    }
    
   /**
    * Attempts to retrieve an item by its name.
    * @param string $name
    * @return UI_Interfaces_Bootstrap|NULL
    */
    public function getItemByName($name)
    {
        return $this->menu->getItemByName($name);
    }
    
    protected function renderCaret()
    {
        return '<span class="caret"></span>';
    }
}
