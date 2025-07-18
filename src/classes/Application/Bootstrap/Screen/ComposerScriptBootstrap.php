<?php
/**
 * @package Application
 * @subpackage Bootstrap
 */

declare(strict_types=1);

namespace Application\Bootstrap;

use Application_Bootstrap_Screen;

/**
 * Bootstrapper for the composer script actions,
 * which sets up the necessary environment for the
 * scripts to run.
 *
 * @package Application
 * @subpackage Bootstrap
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ComposerScriptBootstrap extends Application_Bootstrap_Screen
{
    public function getDispatcher() : string
    {
        return '';
    }

    protected function _boot() : void
    {
        $this->enableScriptMode();
        $this->disableAuthentication();
        $this->createEnvironment();
    }
}
