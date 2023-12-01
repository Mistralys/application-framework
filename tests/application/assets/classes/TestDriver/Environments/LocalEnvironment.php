<?php

declare(strict_types=1);

namespace TestDriver\Environments;

use Application\Environments;
use Application\Environments\EnvironmentSetup\BaseEnvironmentConfig;

class LocalEnvironment extends BaseEnvironmentConfig
{
    public const ENVIRONMENT_ID = 'test-application';

    public function getID(): string
    {
        return self::ENVIRONMENT_ID;
    }

    public function getType(): string
    {
        return Environments::TYPE_DEV;
    }

    protected function configureCustomSettings(): void
    {
    }

    protected function setUpEnvironment(): void
    {
        $this->environment
            ->includeFile($this->configFolder.'/test-db-config.php')
            ->includeFile($this->configFolder.'/test-ui-config.php')
            ->includeFile($this->configFolder.'/test-cas-config.php', true);
    }
}