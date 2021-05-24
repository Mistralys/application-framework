<?php

declare(strict_types=1);

class Application_Bootstrap_Screen_Updaters extends Application_Bootstrap_Screen
{
    public function getDispatcher()
    {
        return 'upgrade.php';
    }
    
    protected function _boot()
    {
        $this->createEnvironment();
        
        $updaters = new Application_Updaters();
        $updaters->start();
    }
}