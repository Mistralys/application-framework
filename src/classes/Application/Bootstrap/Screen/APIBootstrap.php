<?php
/**
 * @package API
 * @subpackage Bootstrap
 */

declare(strict_types=1);

namespace Application\Bootstrap\Screen;

use Application\Application;
use Application_Bootstrap_Screen;

/**
 * Bootstrapper for the API methods.
 *
 * @package API
 * @subpackage Bootstrap
 */
class APIBootstrap extends Application_Bootstrap_Screen
{
    public const string DISPATCHER = 'api/';

    public function getDispatcher(): string
    {
        return self::DISPATCHER;
    }

    protected function _boot(): void
    {
        $this->enableScriptMode();

        $this->disableAuthentication();

        $this->createEnvironment();

        $api = Application::createAPI();
        $api->process();
    }
}