<?php

declare(strict_types=1);

use Application\AppFactory;

class Application_Bootstrap_Screen_Cronjobs extends Application_Bootstrap_Screen
{
    public const DISPATCHER = 'cronjobs.php';

    public function getDispatcher() : string
    {
        return self::DISPATCHER;
    }
    
    protected function _boot() : void
    {
        $this->enableScriptMode();
        
        $this->disableAuthentication();
        
        $this->createEnvironment();
        
        // if we show something, it's plaintext
        header('Content-Type:text/plain; charset=UTF-8');
        
        // in debug mode, show all application log messages as well as the output
        if($this->isDebug()) 
        {
            AppFactory::createLogger()->logModeEcho();
            $_REQUEST['output'] = 'yes';
        }
        
        // FIXME run cronjobs
    }
    
    /**
     * Checks whether debug mode is active.
     * @return boolean
     */
    protected function isDebug() : bool
    {
        return isset($_REQUEST['debug']) && $_REQUEST['debug'] === 'yes';
    }
}