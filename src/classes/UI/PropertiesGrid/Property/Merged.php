<?php

class UI_PropertiesGrid_Property_Merged extends UI_PropertiesGrid_Property
{
    public function render()
    {
        $html =
        '<tr class="prop-merged">'.
            '<td colspan="2">'.$this->label.'</td>'.
        '</tr>';

        return $html;
    }
    
    protected function filterValue($text) : UI_StringBuilder
    {
        return sb();
    }
}
