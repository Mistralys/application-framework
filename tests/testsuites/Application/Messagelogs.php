<?php

use PHPUnit\Framework\TestCase;

final class Application_MessagelogsTest extends TestCase
{
    public function test_addLog()
    {
        DBHelper::startTransaction();
        
        $log = Application::getMessageLog()->addInfo('My message', 'category');
        
        $user = Application::getUser();
        
        $this->assertEquals('My message', $log->getMessage());
        $this->assertEquals('category', $log->getCategory());
        $this->assertTrue($log->isInfo());
        $this->assertSame((int)$user->getID(), $log->getUserID());
        
        DBHelper::rollbackTransaction();
    }
}
