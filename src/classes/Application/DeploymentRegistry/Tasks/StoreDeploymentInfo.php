<?php

declare(strict_types=1);

namespace Application\DeploymentRegistry\Tasks;

use Application\DeploymentRegistry;
use Application_Driver;
use AppUtils\Microtime;
use Application\DeploymentRegistry\BaseDeployTask;
use Application\DeploymentRegistry\DeploymentInfo;

class StoreDeploymentInfo extends BaseDeployTask
{
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
