<?php

class Application_Bootstrap_Screen_Main extends Application_Bootstrap_Screen
{
    public function getDispatcher()
    {
        return '';
    }
    
    protected function _boot()
    {
        $this->createEnvironment();
        
        $this->app->display();
    }
}