<?php

declare(strict_types=1);

class UI_PropertiesGrid_Property_Amount extends UI_PropertiesGrid_Property
{
    protected function filterValue($value) : UI_StringBuilder
    {
        if(is_numeric($value)) {
            return sb()->code($value);
        }

        return sb();
    }
}
