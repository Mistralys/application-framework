<?php

class UI_PropertiesGrid_Property_Amount extends UI_PropertiesGrid_Property
{
    protected function init()
    {
        $this->ifEmpty(sb()->muted('('.t('Empty').')'));
    }
    
    protected function filterValue($value) : UI_StringBuilder
    {
        return sb()->code($value);
    }
}
