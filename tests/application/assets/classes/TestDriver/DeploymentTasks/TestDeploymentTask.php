<?php

declare(strict_types=1);

namespace TestDriver\DeploymentTasks;

use Application\DeploymentRegistry\BaseDeployTask;

class TestDeploymentTask extends BaseDeployTask
{
    public const string TASK_ID = 'TestDeployment';

    protected function _process(): void
    {
    }

    public function getDescription(): string
    {
        return 'This is a test deployment task.';
    }

    public function getPriority(): int
    {
        return 0;
    }

    public function getID(): string
    {
        return self::TASK_ID;
    }
}
