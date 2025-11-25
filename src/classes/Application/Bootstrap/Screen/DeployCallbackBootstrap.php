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
use Application\DeploymentRegistry\DeploymentRegistry;
use Application_Bootstrap;
use Application_Bootstrap_Screen;
use DBHelper;
use Throwable;
use function AppUtils\parseThrowable;

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
        $exception = null;
        $logger = AppFactory::createLogger();

        try
        {
            $this->enableScriptMode();
            $this->disableAuthentication();
            $this->createEnvironment();

            $logger->setMemoryStorageEnabled(true);

            $output = AppFactory::createRequest()->getBool(self::REQUEST_PARAM_ENABLE_OUTPUT);

            DBHelper::startTransaction();

            if ($output)
            {
                header('Content-Type: text/plain; charset=UTF-8');
                $logger->logModeEcho();
            }

            $registry = AppFactory::createDeploymentRegistry();
            $registry->registerDeployment();

            DBHelper::commitTransaction();

            $code = 200;
        }
        catch (Throwable $e)
        {
            $code = 500;
            $exception = $e;

            $this->logError('An exception occurred: #%s "%s"', $e->getCode(), $e->getMessage());
        }

        // Send an email with the status of the operation
        try {
            if ($exception !== null) {
                $this->sendMailError($exception);
            } else {
                $this->sendMailSuccess();
            }
        }
        catch (Throwable $e)
        {
            $this->logError('An exception occurred while sending the email: #%s "%s"', $e->getCode(), $e->getMessage());

            // We cannot do more than log the exception.
            Application_Bootstrap::convertException($e)->log();
        }

        $this->log('Sending status code [%s].', $code);

        http_response_code($code);

        Application::exit('Bootstrap callback complete');
    }

    private function sendMailError(Throwable $e) : void
    {
        $mail = AppFactory::createSystemMailer()->createMail();

        $mail->setSubject(sprintf('%1$s deployment callback error', $this->driver->getAppNameShort()))
            ->para('The deployment callback encountered an error.');

        $this->addThrowable($mail, $e);

        $mail->send();
    }

    private function addThrowable(Application\SystemMails\SystemMail $mail, Throwable $e) : void
    {
        $mail
            ->para('<strong>Exception: '.get_class($e).'</strong>')
            ->para('Message: '.$e->getMessage())
            ->para('Code: '.$e->getCode())
            ->para('File: '.$e->getFile().':'.$e->getLine())
            ->para('')
            ->para('Stack trace:')
            ->preformatted(parseThrowable($e)->toString());

        $previous = $e->getPrevious();
        if($previous !== null) {
            $mail->separator();
            $this->addThrowable($mail, $previous);
        }
    }

    private function sendMailSuccess() : void
    {
        $mail = AppFactory::createSystemMailer()->createMail();

        $mail->setSubject(sprintf('%1$s deployment successful', $this->driver->getAppNameShort()))
            ->para('The deployment callback has been executed successfully.')
            ->para('Completion time: <strong>'.date('Y-m-d H:i:s').'</strong>');

        $mail->send();
    }
}
