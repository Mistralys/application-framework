<?php

declare(strict_types=1);

class Application_Installer_Task_InitSystemUsers extends Application_Installer_Task
{
    protected function _process() : void
    {
        Application_Driver::createUsers()->initSystemUsers();
    }

    public function getTaskDependencies(): array
    {
        return array();
    }
}
