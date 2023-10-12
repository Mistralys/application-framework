<?php

declare(strict_types=1);

class Application_Bootstrap_Screen_HealthMonitor extends Application_Bootstrap_Screen
{
    public const DISPATCHER = 'xml/monitor/';

    public function getDispatcher() : string
    {
        return self::DISPATCHER;
    }
    
    protected function _boot() : void
    {
        $this->disableAuthentication();
        
        $this->enableScriptMode();
        
        $this->createEnvironment();
        
        $monitor = new Application_HealthMonitor();
        $monitor->serveContent();
    }
}
