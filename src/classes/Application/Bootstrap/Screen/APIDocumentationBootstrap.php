<?php
/**
 * @package API
 * @subpackage Bootstrap
 */

declare(strict_types=1);

namespace Application\Bootstrap\Screen;

use Application;
use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\Documentation\APIDocumentation;
use Application\API\Documentation\MethodDocumentation;
use Application_Bootstrap_Screen;

/**
 * Bootstrapper for the API documentation screens.
 *
 * @package API
 * @subpackage Bootstrap
 */
class APIDocumentationBootstrap extends Application_Bootstrap_Screen
{
    public const string DISPATCHER = 'api/documentation.php';

    public function getDispatcher() : string
    {
        return self::DISPATCHER;
    }

    protected function _boot() : void
    {
        $this->disableAuthentication();
        $this->createEnvironment();
        $this->disableKeepAlive();

        $manager = APIManager::getInstance();
        $method = $manager->getRequestedMethodName();
        if($method !== null) {
            $doc = new MethodDocumentation($manager->getMethodByName($method));
        } else {
            $doc = new APIDocumentation();
        }

        displayHTML($doc->render());

        Application::exit();
    }
}
