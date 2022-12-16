<?php
/**
 * @package Application
 * @subpackage Bootstrap
 * @see \Application\Bootstrap\DeployCallbackBootstrap
 */

declare(strict_types=1);

namespace Application\Bootstrap;

use Application;
use Application\DeploymentRegistry;
use Application_Bootstrap_Screen;
use DBHelper;

/**
 * Deployment callback bootstrapper: Creates an instance of
 * the {@see DeploymentRegistry}, and lets it process all
 * tasks required after a deployment.
 *
 * @package Application
 * @subpackage Bootstrap
 * @author Sebastian Mordziol <s.mordziol@mistralys,eu>
 */
class DeployCallbackBootstrap extends Application_Bootstrap_Screen
{
    public const DISPATCHER_NAME = 'deploy-callback.php';

    public function getDispatcher() : string
    {
        return self::DISPATCHER_NAME;
    }

    protected function _boot() : void
    {
        $this->enableScriptMode();
        $this->disableAuthentication();
        $this->createEnvironment();

        DBHelper::startTransaction();

        header('Content-Type: text/plain; charset=UTF-8');

        Application::getLogger()->logModeEcho();

        $registry = new DeploymentRegistry();
        $registry->registerDeployment();

        Application::getLogger()->logModeNone();

        DBHelper::commitTransaction();
    }
}
