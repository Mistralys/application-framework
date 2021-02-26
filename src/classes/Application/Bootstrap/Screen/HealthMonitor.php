<?php

/**
 * The health monitor class used to retrieve the system health data.
 * @see Application_HealthMonitor
 */
require_once 'Application/HealthMonitor.php';

class Application_Bootstrap_Screen_HealthMonitor extends Application_Bootstrap_Screen
{
    public function getDispatcher()
    {
        return 'xml/monitor/';
    }
    
    protected function _boot()
    {
        $this->disableAuthentication();
        
        $this->enableScriptMode();
        
        $this->createEnvironment();
        
        $monitor = new Application_HealthMonitor();
        $monitor->serveContent();
    }
}