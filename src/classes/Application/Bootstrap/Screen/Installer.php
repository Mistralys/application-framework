<?php

declare(strict_types=1);

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

        Application::getLogger()->printLog(true);
    }
}