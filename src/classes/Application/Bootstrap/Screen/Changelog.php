<?php

declare(strict_types=1);

class Application_Bootstrap_Screen_Changelog extends Application_Bootstrap_Screen
{
   /**
    * @var string
    */
    protected string $langID = 'dev';
    
    public function getDispatcher() : string
    {
        return 'changelog.php';
    }
    
    protected function _boot() : void
    {
        $this->enableScriptMode();
        $this->disableAuthentication();
        $this->createEnvironment();

        header('Content-Type:text/plain; encoding=utf-8');
        
        echo Application_Driver::createWhatsnew()->toPlainText($this->langID);

        Application::exit('Shown the changelog.');
    }
}
