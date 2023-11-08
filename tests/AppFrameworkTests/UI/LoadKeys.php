<?php

use PHPUnit\Framework\TestCase;

final class Application_LoadKeysTest extends TestCase
{
   /**
    * @var UI
    */
    protected $ui;
    
    protected function setUp() : void
    {
        if(!isset($this->ui))
        {
            $this->ui = UI::getInstance();
        }
        
        $this->ui->getResourceManager()->clearLoadkeys();
    }

    public function test_loadScript()
    {
        $this->assertEmpty($this->ui->getResourceManager()->getLoadedResourceKeys());
    }

    public function test_notAvoidable()
    {
        $this->assertFalse($this->ui->addJavascript('application.js')->isAvoidable());
    }
    
    public function test_avoidable()
    {
        $resourceManager = $this->ui->getResourceManager();
        
        $loadkey = $this->ui->addJavascript('application.js')->getKey();
        
        $resourceManager->clearLoadkeys();
        
        $_REQUEST[UI_ResourceManager::LOADKEYS_REQUEST_VARIABLE] = $loadkey;
        
        $this->assertNotEmpty($resourceManager->getLoadedResourceKeys());
        $this->assertTrue($this->ui->addJavascript('application.js')->isAvoidable());
    }
}
