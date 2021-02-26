<?php

use PHPUnit\Framework\TestCase;

abstract class Application_TestCase_AdminAreas extends TestCase
{
    abstract protected function getRequiredAreas() : array;
    
    protected function createInfo() : Application_Driver_AdminInfo
    {
        static $info;
        
        if(!isset($info)) 
        {
            $driver = Application_Driver::getInstance();
            $info = $driver->describeAdminAreas();
            $info->analyzeFiles();
        }
        
        return $info;
    }
    
    public function test_analyzeClasses()
    {
        $info = $this->createInfo();
        
        $data = $info->toArray();
        
        $this->assertNotEmpty($data, 'There should be some admin areas present.');
    }
    
    public function test_requiredAreas()
    {
        $info = $this->createInfo();
        
        $data = $info->toArray();
        
        $areas = $this->getRequiredAreas();
        
        foreach($areas as $area) {
            $this->assertTrue(isset($data[$area]), 'The admin area ['.$area.'] is not present.');
        }
    }
}
