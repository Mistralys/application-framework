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
class WriteLocalizationFilesTask extends BaseDeployTask
{
    public const TASK_NAME = 'WriteLocalizationFiles';

    public function getID() : string
    {
        return self::TASK_NAME;
    }

    public function getDescription() : string
    {
        return t('Writes the client-side localization files to disk, so they are ready when the application starts.');
    }

    protected function _process(): void
    {
        Localization::createGenerator()->writeFiles();
    }
}
