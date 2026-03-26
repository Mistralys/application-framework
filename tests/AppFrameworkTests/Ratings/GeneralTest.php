<?php

declare(strict_types=1);

namespace AppFrameworkTests\Ratings;

use Application_Driver;
use Application_Exception;
use PHPUnit\Framework\TestCase;

final class GeneralTest extends TestCase
{
    public function test_exception()
    {
        $driver = Application_Driver::getInstance();
        
        $this->expectException(Application_Exception::class);
        
        $driver->parseURL('/relative/path');
    }
}
