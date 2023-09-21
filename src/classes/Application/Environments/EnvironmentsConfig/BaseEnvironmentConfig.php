<?php

declare(strict_types=1);

namespace Application\Environments\EnvironmentSetup;

use Application\ConfigSettings\BaseConfigRegistry;
use Application\Environments;
use Application\Environments\Environment;
use AppUtils\FileHelper\FolderInfo;

abstract class BaseEnvironmentConfig
{
    protected FolderInfo $configFolder;
    protected BaseConfigRegistry $config;
    protected Environment $environment;

    abstract public function getID() : string;

    /**
     * @return string
     * @see Environments::TYPE_DEV
     * @see Environments::TYPE_PROD
     */
    abstract public function getType() : string;
    abstract protected function configureCustomSettings() : void;
    abstract protected function setUpEnvironment() : void;

    public function __construct(BaseConfigRegistry $config, FolderInfo $configFolder)
    {
        $environments = Environments::getInstance();

        $this->configFolder = $configFolder;
        $this->config = $config;

        $this->environment = $environments->register(
            $this->getID(),
            $this->getType(),
            function () {
                $this->configureCustomSettings();
            }
        );

        $this->setUpEnvironment();
    }

    /**
     * @return string[]
     */
    public function getDevHosts(): array
    {
        $hostsFile = $this->configFolder . '/dev-hosts.txt';

        if (!file_exists($hostsFile)) {
            return array();
        }

        $hosts = explode("\n", file_get_contents($hostsFile));
        $hosts = array_map('trim', $hosts);

        $result = array();

        foreach ($hosts as $host) {
            if (!empty($host)) {
                $result[] = $host;
            }
        }

        return $result;
    }
}
