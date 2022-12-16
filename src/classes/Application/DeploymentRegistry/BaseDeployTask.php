<?php

declare(strict_types=1);

namespace Application\DeploymentRegistry;

use Application_Driver;
use Application_Interfaces_Loggable;
use Application_Traits_Loggable;

abstract class BaseDeployTask implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    protected Application_Driver $driver;

    public function __construct()
    {
        $this->driver = Application_Driver::getInstance();
    }

    public function process() : void
    {
        $this->_process();
    }

    abstract protected function _process() : void;

    public function getLogIdentifier(): string
    {
        return $this->getIdentifierFromSelf('DeployTask');
    }
}
