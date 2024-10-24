<?php
/**
 * @package Application
 * @subpackage Deployment Registry
 */

declare(strict_types=1);

namespace Application\DeploymentRegistry\Tasks;

use Application\DeploymentRegistry\BaseDeployTask;
use AppLocalize\Localization;

/**
 * Writes the localization files to disk.
 *
 * @package Application
 * @subpackage Deployment Registry
 */
class WriteLocalizationFiles extends BaseDeployTask
{
    protected function _process(): void
    {
        $this->log('Writing localization files to disk.');

        Localization::createGenerator()->writeFiles();
    }
}
