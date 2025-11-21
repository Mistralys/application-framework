<?php

declare(strict_types=1);

namespace TestDriver\ExternalSources\DeploymentTasks;

use Application\DeploymentRegistry\BaseDeployTask;

class ExternalDeploymentTask extends BaseDeployTask
{
    public const string TASK_ID = 'ExternalDeployment';

    protected function _process(): void
    {
    }

    public function getDescription(): string
    {
        return '';
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
