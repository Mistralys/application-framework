<?php
/**
 * File containing the {@link UI_Label} class.
 *
 * @package Application
 * @subpackage UI
 * @see UI_Label
 */

/**
 * UI helper class for creating colored labels.
 * 
 * @package Application
 * @subpackage UI
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Label extends UI_Badge
{
    /**
     * @param string|number|UI_Renderable_Interface $label
     * @throws UI_Exception
     */
    public function __construct($label)
    {
        parent::__construct($label);
        $this->classType = 'label';
    }
}
