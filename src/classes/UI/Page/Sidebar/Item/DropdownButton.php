<?php

/**
 * A dropdown button with a submenu.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @method UI_Page_Sidebar_Item_DropdownButton requireChanging(Application_Revisionable $revisionable)
 * @method UI_Page_Sidebar_Item_DropdownButton requireTrue(mixed $condition, string $reason=null)
 * @method UI_Page_Sidebar_Item_DropdownButton requireFalse(mixed $condition, string $reason=null)
 * @method UI_Page_Sidebar_Item_DropdownButton setIcon($icon)
 */
class UI_Page_Sidebar_Item_DropdownButton extends UI_Page_Sidebar_Item_Button
{
   /**
    * @var UI_Bootstrap_DropdownMenu
    */
    protected $menu;
    
    protected function init()
    {
        $this->menu = $this->ui->createDropdownMenu();
    }
    
   /**
    * Adds a link to the dropdown menu.
    * 
    * @param string $label
    * @param string $url
    * @return UI_Bootstrap_DropdownAnchor
    */
    public function addLink($label, $url)
    {
        return $this->menu->addLink($label, $url);
    }
    
   /**
    * Adds a header to the dropdown menu.
    * 
    * @param string $label
    * @return UI_Bootstrap_DropdownHeader
    */
    public function addHeader($label)
    {
        return $this->menu->addHeader($label);
    }
    
    public function getMenu()
    {
        return $this->menu;
    }
    
    protected function _render()
    {
        $this->mode = 'dropmenu';
        
        return parent::_render();
    }
    
    protected $caret = true;
    
    public function hasCaret()
    {
        return $this->caret;
    }
    
    public function noCaret()
    {
        $this->caret = false;
        return $this;
    }
}