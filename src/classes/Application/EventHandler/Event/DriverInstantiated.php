<?php

declare(strict_types=1);

use Application\Application;

class Application_EventHandler_Event_DriverInstantiated extends Application_EventHandler_Event
{
   /**
    * @return Application
    */
    public function getApplication() : Application
    {
        return $this->getArgument(0);
    }
    
   /**
    * @return Application_Driver
    */
    public function getDriver() : Application_Driver
    {
        return $this->getArgument(1);
    }
}
