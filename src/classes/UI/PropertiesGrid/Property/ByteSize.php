<?php

class UI_PropertiesGrid_Property_ByteSize extends UI_PropertiesGrid_Property_Regular
{
    protected function filterValue($value) : UI_StringBuilder
    {
        $bytes = intval($value);
        
        return sb()->add(\AppUtils\ConvertHelper::bytes2readable($value));
    }
}