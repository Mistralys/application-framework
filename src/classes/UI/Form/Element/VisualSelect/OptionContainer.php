<?php
/**
 * File containing the class {@see HTML_QuickForm2_Element_VisualSelect_OptionContainer}.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @see HTML_QuickForm2_Element_VisualSelect_OptionContainer
 */

/**
 * Custom option container used to ensure that any option
 * groups added use the custom visual select implementation.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class HTML_QuickForm2_Element_VisualSelect_OptionContainer extends HTML_QuickForm2_Element_Select_OptionContainer
{
    public function addOptgroup($label, $attributes = null) : HTML_QuickForm2_Element_VisualSelect_Optgroup
    {
        $optgroup = new HTML_QuickForm2_Element_VisualSelect_Optgroup(
            $this->values, $this->possibleValues, $label, $attributes
        );

        $this->options[] = $optgroup;

        return $optgroup;
    }
}
