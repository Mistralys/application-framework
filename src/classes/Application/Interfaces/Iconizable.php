<?php
/**
 * @package Application
 * @subpackage UserInterface
 * @see Application_Interfaces_Iconizable
 */

declare(strict_types=1);

/**
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Traits_Iconizable
 */
interface Application_Interfaces_Iconizable
{
   /**
    * @param UI_Icon|NULL $icon
    * @return $this
    */
    public function setIcon(?UI_Icon $icon) : self;
    
    public function hasIcon() : bool;
    
    public function getIcon() : ?UI_Icon;
}
