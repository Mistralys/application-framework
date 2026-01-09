<?php

declare(strict_types=1);

use Application\AppFactory;
use Application\Application;

class Application_Bootstrap_Screen_Installer extends Application_Bootstrap_Screen
{
    public function getDispatcher() : string
    {
        return 'install.php';
    }

    protected function _boot() : void
    {
        $this->disableAuthentication();
        $this->enableScriptMode();
        $this->createEnvironment();

        $installer = Application::createInstaller();
        $installer->process();

        AppFactory::createLogger()->printLog(true);
    }
}