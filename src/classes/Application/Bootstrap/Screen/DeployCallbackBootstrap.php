<?php
/**
 * @package Application
 * @subpackage Bootstrap
 * @see \Application\Bootstrap\DeployCallbackBootstrap
 */

declare(strict_types=1);

namespace Application\Bootstrap;

use Application;
use Application\AppFactory;
use Application\DeploymentRegistry;
use Application_Bootstrap_Screen;
use DBHelper;
use Throwable;

/**
 * Deployment callback bootstrapper: Creates an instance of
 * the {@see DeploymentRegistry}, and lets it process all
 * tasks required after a deployment.
 *
 * @package Application
 * @subpackage Bootstrap
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DeployCallbackBootstrap extends Application_Bootstrap_Screen
{
    public const DISPATCHER_NAME = 'deploy-callback.php';
    public const REQUEST_PARAM_ENABLE_OUTPUT = 'enable-output';

    public function getDispatcher() : string
    {
        return self::DISPATCHER_NAME;
    }

    protected function _boot() : void
    {
        try
        {
            $this->enableScriptMode();
            $this->disableAuthentication();
            $this->createEnvironment();

            $output = AppFactory::createRequest()->getBool(self::REQUEST_PARAM_ENABLE_OUTPUT);

            DBHelper::startTransaction();

            if ($output)
            {
                header('Content-Type: text/plain; charset=UTF-8');
                AppFactory::createLogger()->logModeEcho();
            }

            $registry = AppFactory::createDeploymentRegistry();
            $registry->registerDeployment();

            AppFactory::createLogger()->logModeNone();

            DBHelper::commitTransaction();

            http_response_code(200);
        }
        catch (Throwable $e)
        {
            http_response_code(500);
        }
    }
}
