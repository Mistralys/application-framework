<?php

declare(strict_types=1);

namespace AppFrameworkTests\Application;

use Application\AppFactory;
use Application\Application;
use DBHelper;
use PHPUnit\Framework\TestCase;

final class MessagelogsTest extends TestCase
{
    public function test_addLog()
    {
        DBHelper::startTransaction();
        
        $log = AppFactory::createMessageLog()->addInfo('My message', 'category');

        $user = Application::getUser();
        
        $this->assertEquals('My message', $log->getMessage());
        $this->assertEquals('category', $log->getCategory());
        $this->assertTrue($log->isInfo());
        $this->assertSame((int)$user->getID(), $log->getUserID());
        
        DBHelper::rollbackTransaction();
    }
}
