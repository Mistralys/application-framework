<?php

class Application_Bootstrap_Screen_Media extends Application_Bootstrap_Screen
{
    public function getDispatcher() : string
    {
        return 'media.php';
    }
    
    protected function _boot() : void
    {
        $this->enableScriptMode();
        $this->createEnvironment();
        
        $delivery = Application_Media_Delivery::getInstance();
        $delivery->serveFromRequest();
    }
}
