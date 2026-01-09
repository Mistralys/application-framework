<?php

declare(strict_types=1);

use Application\Application;

class Application_Bootstrap_Screen_Ajax extends Application_Bootstrap_Screen
{
    public const DISPATCHER = 'ajax/';

    public function getDispatcher() : string
    {
        return self::DISPATCHER;
    }
    
    protected function _boot() : void
    {
        $this->enableScriptMode();
        $this->checkKeepAlive();
        $this->createEnvironment();
        
        $ajax = $this->driver->getAjaxHandler();

        try
        {
            $ajax->process();
        }
        catch(Exception $e)
        {
            $ajax->displayException($e);
        }
    }

    /**
     * Handle keep-alive requests before the application is bootstrapped.
     * Used for testing purposes.
     *
     * @return void
     * @throws JsonException
     */
    private function checkKeepAlive() : void
    {
        if(!isset($_REQUEST['keep-alive'])) {
            return;
        }

        $config = $_REQUEST['keep-alive'];

        if(isset($config['target']) && $config['target'] === 'redirect')
        {
            header('Location: '.APP_URL);
            Application::exit('KeepAlive Test | Redirect');
        }
    }
}