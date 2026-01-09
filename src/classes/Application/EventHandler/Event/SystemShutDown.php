<?php

use Application\Application;

class Application_EventHandler_Event_SystemShutDown extends Application_EventHandler_Event
{
    /**
     * @return Application
     */
    public function getApplication()
    {
        return $this->getDriver()->getApplication();
    }
    
    /**
     * @return Application_Driver
     */
    public function getDriver()
    {
        return $this->getArgument(1);
    }
}