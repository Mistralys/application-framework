<?php
/**
 * Deployment callback script that handles the one-time
 * tasks that need to be done after a deployment.
 *
 * @package Application
 * @subpackage TestDriver
 * @see \Application\Bootstrap\DeployCallbackBootstrap
 */

declare(strict_types=1);

namespace Maileditor;

use Application_Bootstrap;
use Application\Bootstrap\DeployCallbackBootstrap;

/**
 * The bootstrapper that starts the target application screen.
 * @see Application_Bootstrap
 */
require_once __DIR__ . '/bootstrap.php';

Application_Bootstrap::bootClass(DeployCallbackBootstrap::class);
