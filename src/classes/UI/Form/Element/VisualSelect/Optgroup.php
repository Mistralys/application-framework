<?php

class HTML_QuickForm2_Element_VisualSelect_Optgroup extends HTML_QuickForm2_Element_Select_Optgroup
{
    public function addImage($label, $value, $url)
    {
        $this->addOption($label, $value, array('image-url' => $url));
    }
}
