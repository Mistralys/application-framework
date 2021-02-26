<?php

interface UI_Interfaces_Badge
{
    public function setLabel(string $label);
    
    public function setWrapper(string $code);
    
    public function makeDangerous();
    
    public function makeInfo();
    
    public function makeSuccess();
    
    public function makeWarning();
    
    public function makeInverse();
    
    public function makeInactive();
    
    public function cursorHelp();
    
    public function makeLarge();
    
    public function getLabel() : string;
}
