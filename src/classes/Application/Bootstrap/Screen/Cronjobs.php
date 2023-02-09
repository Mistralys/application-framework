<?php


use Application\AppFactory;

class Application_Bootstrap_Screen_Cronjobs extends Application_Bootstrap_Screen
{
    public function getDispatcher()
    {
        return 'cronjobs.php';
    }
    
    protected function _boot()
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
    protected function isDebug()
    {
        if(isset($_REQUEST['debug']) && $_REQUEST['debug']=='yes') {
            return true;
        }
        
        return false;
    }
}