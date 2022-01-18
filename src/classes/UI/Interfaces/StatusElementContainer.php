<?php
/**
 * File containing the interface {@see UI_Interfaces_StatusElementContainer}.
 *
 * @package Application
 * @subpackage UserInterface
 * @see UI_Interfaces_StatusElementContainer
 */

declare(strict_types=1);

/**
 * Interface for UI elements that allow status elements
 * to be added, like warning icons and the like.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see UI_Traits_StatusElementContainer
 */
interface UI_Interfaces_StatusElementContainer
{
    /**
     * @param UI_Icon $icon
     * @return UI_Interfaces_StatusElementContainer
     */
    public function addStatusIcon(UI_Icon $icon);

    /**
     * @param UI_Renderable_Interface $element
     * @return UI_Interfaces_StatusElementContainer
     */
    public function addStatusElement(UI_Renderable_Interface $element);

    public function hasStatusElements() : bool;

    /**
     * @return UI_Renderable_Interface[]
     */
    public function getStatusElements() : array;
}
