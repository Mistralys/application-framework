<?php
/**
 * File containing the {@see Application_Traits_Iconizable} trait.
 * 
 * @package Application
 * @subpackage UserInterface 
 * @see Application_Traits_Iconizable
 */

/**
 * Trait for elements that can have an icon set.
 * 
 * Usage:
 * 
 * - Add the use statement
 * - Implement the iconizable interface
 * 
 * @package Application
 * @subpackage UserInterface 
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Interfaces_Iconizable
 */
trait Application_Traits_Iconizable
{
   /**
    * @var UI_Icon|NULL
    */
    protected ?UI_Icon $icon = null;

    /**
     * @param UI_Icon|NULL $icon
     * @return $this
     */
    public function setIcon(?UI_Icon $icon) : self
    {
        $this->icon = $icon;
        return $this;
    }
    
    public function hasIcon() : bool
    {
        return isset($this->icon);
    }
    
    public function getIcon() : ?UI_Icon
    {
        return $this->icon;
    }

    public function renderIconLabel(string $label) : string
    {
        $icon = $this->getIcon();
        $result = sb();

        if($icon !== null)
        {
            $result->icon($icon);
        }

        return (string)$result->add($label);
    }
}
