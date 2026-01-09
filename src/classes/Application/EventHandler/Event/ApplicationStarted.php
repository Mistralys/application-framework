<?php

use Application\Application;

class Application_EventHandler_Event_ApplicationStarted extends Application_EventHandler_Event
{
   /**
    * @return Application
    */
    public function getApplication()
    {
        return $this->getArgument(0);
    }
    
   /**
    * @return Application_Driver
    */
    public function getDriver()
    {
        return $this->getArgument(1);
    }
}