<?php

use UI\AdminURLs\AdminURLInterface;

/**
 * A dropdown button with a submenu.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Page_Sidebar_Item_DropdownButton extends UI_Page_Sidebar_Item_Button
{
    public const string MODE_DROPDOWN_MENU = 'dropmenu';
    protected UI_Bootstrap_DropdownMenu $menu;
    protected bool $caret = true;

    protected function init() : void
    {
        $this->menu = $this->ui->createDropdownMenu();
    }

    /**
     * Adds a link to the dropdown menu.
     *
     * @param string|number|UI_Renderable_Interface|NULL $label
     * @param string|AdminURLInterface $url
     * @return UI_Bootstrap_DropdownAnchor
     * @throws UI_Exception
     */
    public function addLink($label, $url) : UI_Bootstrap_DropdownAnchor
    {
        return $this->menu->addLink($label, $url);
    }
    
   /**
    * Adds a header to the dropdown menu.
    * 
    * @param string|number|UI_Renderable_Interface|NULL $label
    * @return UI_Bootstrap_DropdownHeader
    */
    public function addHeader($label) : UI_Bootstrap_DropdownHeader
    {
        return $this->menu->addHeader($label);
    }
    
    public function getMenu() : UI_Bootstrap_DropdownMenu
    {
        return $this->menu;
    }
    
    protected function _render() : string
    {
        $this->mode = self::MODE_DROPDOWN_MENU;
        
        return parent::_render();
    }
    
    public function hasCaret() : bool
    {
        return $this->caret;
    }

    /**
     * @return $this
     */
    public function noCaret() : self
    {
        $this->caret = false;
        return $this;
    }
}
