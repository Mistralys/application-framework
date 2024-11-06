<?php
/**
 * @package Application
 * @subpackage Deployment Registry
 */

declare(strict_types=1);

namespace Application\DeploymentRegistry;

use Application_Interfaces_Loggable;
use AppUtils\Interfaces\StringPrimaryRecordInterface;

/**
 * Interface for deployment tasks. The base class skeleton
 * is provided by {@see BaseDeployTask}.
 *
 * @package Application
 * @subpackage Deployment Registry
 */
interface DeploymentTaskInterface
    extends
    StringPrimaryRecordInterface,
    Application_Interfaces_Loggable
{
    public function process() : void;

    /**
     * A human-readable description of the task.
     * HTML is allowed for basic formatting.
     *
     * @return string
     */
    public function getDescription() : string;
}
