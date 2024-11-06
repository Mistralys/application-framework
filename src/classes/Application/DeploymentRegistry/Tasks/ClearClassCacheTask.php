<?php
/**
 * @package Application
 * @subpackage Deployment Registry
 */

declare(strict_types=1);

namespace Application\DeploymentRegistry\Tasks;

use Application\AppFactory\ClassCacheHandler;
use Application\DeploymentRegistry\BaseDeployTask;

/**
 * Clears the class cache. While a new deployment will
 * automatically create new class cache files (tied to the
 * application version), this task frees up some disk space.
 *
 * @package Application
 * @subpackage Deployment Registry
 */
class ClearClassCacheTask extends BaseDeployTask
{
    public const TASK_NAME = 'ClearClassCache';

    public function getID() : string
    {
        return self::TASK_NAME;
    }

    public function getDescription() : string
    {
        return t('Clears the cache of PHP classes loaded dynamically from the file system.');
    }

    protected function _process(): void
    {
        ClassCacheHandler::clearClassCache();
    }
}
