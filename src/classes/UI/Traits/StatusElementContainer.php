<?php
/**
 * File containing the trait {@see UI_Traits_StatusElementContainer}.
 *
 * @package Application
 * @subpackage UserInterface
 * @see UI_Traits_StatusElementContainer
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
 * @see UI_Interfaces_StatusElementContainer
 */
trait UI_Traits_StatusElementContainer
{
    /**
     * @var UI_Renderable_Interface[]
     */
    private $statusElements = array();

    /**
     * @param UI_Icon $icon
     * @return $this
     */
    public function addStatusIcon(UI_Icon $icon)
    {
        return $this->addStatusElement($icon);
    }

    /**
     * @param UI_Renderable_Interface $element
     * @return $this
     */
    public function addStatusElement(UI_Renderable_Interface $element)
    {
        $this->statusElements[] = $element;
        return $this;
    }

    public function hasStatusElements() : bool
    {
        return !empty($this->statusElements);
    }

    /**
     * @return UI_Renderable_Interface[]
     */
    public function getStatusElements() : array
    {
        return $this->statusElements;
    }
}
