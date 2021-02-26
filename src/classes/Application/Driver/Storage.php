<?php

abstract class Application_Driver_Storage
{
    public function __construct()
    {
        $this->init();
    }
    
    protected function init()
    {
        
    }
    
    abstract public function get($name);
    
    abstract public function set($name, $value, $role);
    
    abstract public function delete($name);
}