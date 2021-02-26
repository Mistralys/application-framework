<?php

interface Application_Interfaces_Validatable
{
    public function isValid() : bool;
    
    public function getValidationMessage() : ?string;
    
    public function setValidationOption($name, $value) : void;
    
    public function getValidationOption(string $name, $default=null);
    
    public function getValidationOptionDefaults() : array;
}
