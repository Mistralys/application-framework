<?php
/**
 * @package Application
 * @subpackage Deployment Registry
 */

declare(strict_types=1);

namespace Application\DeploymentRegistry\Tasks;

use Application\AppFactory;
use Application\DeploymentRegistry\BaseDeployTask;
use Application\DeploymentRegistry\DeploymentTaskInterface;
use Application\Driver\VersionInfo;

/**
 * Creates the {@see VersionInfo::FILE_NAME} file with the
 * current application version.
 *
 * @package Application
 * @subpackage Deployment Registry
 *
 * @see VersionInfo
 */
class StoreCurrentVersionTask extends BaseDeployTask
{
    public const string TASK_NAME = 'StoreCurrentVersion';

    protected function _process(): void
    {
        AppFactory::createVersionInfo()->storeCurrentVersion();
    }

    public function getPriority(): int
    {
        // Uses the highest priority to ensure it runs first,
        // as many cache operations and checks depend on the
        // framework version.
        return DeploymentTaskInterface::SYSTEM_BASE_PRIORITY + 9000;
    }

    public function getDescription(): string
    {
        return t(
            'Stores the current application version in the %1$s file.',
            VersionInfo::FILE_NAME
        );
    }

    public function getID(): string
    {
        return self::TASK_NAME;
    }
}
