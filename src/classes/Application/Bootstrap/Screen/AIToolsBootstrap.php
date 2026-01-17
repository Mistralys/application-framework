<?php
/**
 * @package AI
 * @subpackage Bootstrap
 */

declare(strict_types=1);

namespace Application\Bootstrap\Screen;

use Application_Bootstrap_Screen;

/**
 * Bootstrapper for the AI MCP Server methods.
 *
 * @package AI
 * @subpackage Bootstrap
 */
class AIToolsBootstrap extends Application_Bootstrap_Screen
{
    public function getDispatcher(): string
    {
        return '';
    }

    protected function _boot(): void
    {
        $this->enableScriptMode();
        $this->disableAuthentication();
        $this->createEnvironment();
    }
}
