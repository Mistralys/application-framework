<?php 

use PHPUnit\Framework\TestCase;

final class Ratings_GeneralTest extends TestCase
{
    public function test_exception()
    {
        $driver = Application_Driver::getInstance();
        
        $this->expectException(Application_Exception::class);
        
        $driver->parseURL('/relative/path');
    }
}