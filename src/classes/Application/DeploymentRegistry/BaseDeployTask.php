<?php
/**
 * @package Application
 * @subpackage DeploymentRegistry
 */

declare(strict_types=1);

namespace Application\DeploymentRegistry;

use Application_Driver;
use Application_Traits_Loggable;

/**
 * Abstract base class for tasks in the deployment registry.
 *
 * @package Application
 * @subpackage DeploymentRegistry
 */
abstract class BaseDeployTask implements DeploymentTaskInterface
{
    use Application_Traits_Loggable;

    protected Application_Driver $driver;
    private string $logIdentifier;

    public function __construct()
    {
        $this->driver = Application_Driver::getInstance();
        $this->logIdentifier = sprintf('DeployTask [%s]', $this->getID());
    }

    public function process() : void
    {
        $this->log('Processing task...');
        $this->log(strip_tags($this->getDescription()));

        $this->_process();
    }

    abstract protected function _process() : void;

    public function getLogIdentifier(): string
    {
        return $this->logIdentifier;
    }
}
