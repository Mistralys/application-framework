<?php


class Application_Bootstrap_Screen_API extends Application_Bootstrap_Screen
{
    public function getDispatcher()
    {
        return 'api/';
    }
    
    protected function _boot()
    {
        $this->enableScriptMode();
        
        $this->disableAuthentication();
        
        $this->createEnvironment();
        
        $api = Application::createAPI();
        $api->process();
    }
}