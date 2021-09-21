<?php

declare(strict_types=1);

class UI_PropertiesGrid_Property_Regular extends UI_PropertiesGrid_Property
{
    protected function filterValue($value) : UI_StringBuilder
    {
        return sb()->add(toString($value));
    }
}
