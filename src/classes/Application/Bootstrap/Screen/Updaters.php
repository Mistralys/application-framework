<?php

declare(strict_types=1);

class Application_Bootstrap_Screen_Updaters extends Application_Bootstrap_Screen
{
    public const DISPATCHER_NAME = 'upgrade.php';

    public function getDispatcher() : string
    {
        return self::DISPATCHER_NAME;
    }
    
    protected function _boot() : void
    {
        $this->createEnvironment();
        
        $updaters = new Application_Updaters();
        $updaters->start();
    }
}