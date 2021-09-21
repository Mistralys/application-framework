<?php

declare(strict_types=1);

class UI_PropertiesGrid_Property_Amount extends UI_PropertiesGrid_Property
{
    protected function init() : void
    {
        $this->ifEmpty(sb()->muted('('.t('Empty').')'));
    }
    
    protected function filterValue($value) : UI_StringBuilder
    {
        return sb()->code($value);
    }
}
