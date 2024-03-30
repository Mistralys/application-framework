<?php

declare(strict_types=1);

use AppUtils\ConvertHelper;

class UI_PropertiesGrid_Property_ByteSize extends UI_PropertiesGrid_Property_Regular
{
    protected function filterValue($value) : UI_StringBuilder
    {
        $bytes = (int)$value;
        
        return sb()->add(ConvertHelper::bytes2readable($bytes));
    }
}
