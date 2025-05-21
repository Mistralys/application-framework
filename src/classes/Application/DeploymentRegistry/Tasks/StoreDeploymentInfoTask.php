<?php
/**
 * @package Application
 * @subpackage Deployment Registry
 */

declare(strict_types=1);

namespace Application\DeploymentRegistry\Tasks;

use Application\DeploymentRegistry;
use Application\DeploymentRegistry\DeploymentTaskInterface;
use Application_Driver;
use AppUtils\Microtime;
use Application\DeploymentRegistry\BaseDeployTask;
use Application\DeploymentRegistry\DeploymentInfo;

/**
 * Stores the deployment date for the current version in the deployment history.
 *
 * @package Application
 * @subpackage Deployment Registry
 */
class StoreDeploymentInfoTask extends BaseDeployTask
{
    public const TASK_NAME = 'StoreDeploymentInfo';

    public function getID() : string
    {
        return self::TASK_NAME;
    }

    public function getPriority(): int
    {
        return DeploymentTaskInterface::SYSTEM_BASE_PRIORITY;
    }

    public function getDescription(): string
    {
        return t('Stores the deployment date for the current version in the deployment history.');
    }

    protected function _process(): void
    {
        $this->log('Storing release date for version [%s].', $this->driver->getVersion());

        $settings = Application_Driver::createSettings();

        $items = $settings->getArray(DeploymentRegistry::SETTING_DEPLOYMENT_HISTORY);

        $items[] = array(
            DeploymentInfo::KEY_VERSION => $this->driver->getVersion(),
            DeploymentInfo::KEY_DATE => Microtime::createNow()->getISODate()
        );

        $settings->setArray(DeploymentRegistry::SETTING_DEPLOYMENT_HISTORY, $items);
    }
}
