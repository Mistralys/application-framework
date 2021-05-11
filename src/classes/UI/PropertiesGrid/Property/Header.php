<?php

class UI_PropertiesGrid_Property_Header extends UI_PropertiesGrid_Property
{
    public function render() : string
    {
        $html =
        '<tr class="prop-header">'.
            '<th colspan="2">'.$this->label.'</th>'.
        '</tr>';

        return $html;
    }
    
    protected function filterValue($text) : UI_StringBuilder
    {
        return sb();
    }
}
