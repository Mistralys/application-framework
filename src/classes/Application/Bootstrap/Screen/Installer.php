<?php

declare(strict_types=1);

use Application\AppFactory;

class Application_Bootstrap_Screen_Installer extends Application_Bootstrap_Screen
{
    public function getDispatcher()
    {
        return 'install.php';
    }

    protected function _boot()
    {
        $this->disableAuthentication();
        $this->enableScriptMode();
        $this->createEnvironment();

        $installer = Application::createInstaller();
        $installer->process();

        AppFactory::createLogger()->printLog(true);
    }
}