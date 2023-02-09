<?php

declare(strict_types=1);

use Application\AppFactory;

class Application_Installer_Task_InitSystemUsers extends Application_Installer_Task
{
    protected function _process() : void
    {
        AppFactory::createUsers()->initSystemUsers();
    }

    public function getTaskDependencies(): array
    {
        return array();
    }
}
