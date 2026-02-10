<?php

declare(strict_types=1);

class Application_Bootstrap_Screen_Main extends Application_Bootstrap_Screen
{
    public function getDispatcher() : string
    {
        return '';
    }
    
    protected function _boot() : void
    {
        $this->createEnvironment();
        
        $this->app->display();
    }
}
