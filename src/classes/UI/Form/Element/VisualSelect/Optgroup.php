<?php
/**
 * File containing the class {@see HTML_QuickForm2_Element_VisualSelect_Optgroup}.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @see HTML_QuickForm2_Element_VisualSelect_Optgroup
 */

declare(strict_types=1);

/**
 * Custom option group that adds methods specific to the
 * visual selection element. Use the {@see HTML_QuickForm2_Element_VisualSelect_Optgroup::addImage()}
 * method to add images, instead of the regular `addOption()`
 * method.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class HTML_QuickForm2_Element_VisualSelect_Optgroup extends HTML_QuickForm2_Element_Select_Optgroup
{
    /**
     * @param string|number|UI_Renderable_Interface|NULL $label
     * @param string $value
     * @param string $url
     * @return $this
     * @throws UI_Exception
     */
    public function addImage($label, string $value, string $url) : self
    {
        $this->addOption(toString($label), $value, array('image-url' => $url));
        return $this;
    }
}
