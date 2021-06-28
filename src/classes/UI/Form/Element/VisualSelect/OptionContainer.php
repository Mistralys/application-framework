<?php

class HTML_QuickForm2_Element_VisualSelect_OptionContainer extends HTML_QuickForm2_Element_Select_OptionContainer
{
    public function addOptgroup($label, $attributes = null)
    {
        $optgroup = new HTML_QuickForm2_Element_VisualSelect_Optgroup(
            $this->values, $this->possibleValues, $label, $attributes
        );
        $this->options[] = $optgroup;
        return $optgroup;
    }
}
