<?php

declare(strict_types=1);

namespace Application\Environments\EnvironmentSetup;

use Application\ConfigSettings\BaseConfigSettings;
use Application\Environments;
use Application\Environments\Environment;
use AppUtils\FileHelper\FolderInfo;

abstract class BaseEnvironmentConfig
{
    protected FolderInfo $configFolder;
    protected BaseConfigSettings $config;
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

    public function __construct(BaseConfigSettings $config, FolderInfo $configFolder)
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
}
