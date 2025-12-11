<?php
/**
 * File containing the class {@see Application_Bootstrap_Screen_Changelog}.
 *
 * @package Application
 * @subpackage Bootstrap1
 * @see Application_Bootstrap_Screen_Changelog
 */

declare(strict_types=1);

use Application\AppFactory;
use Application\WhatsNew\WhatsNew;

/**
 * Bootstrap screen used to display the application's changelog
 * in plain text format.
 *
 * @package Application
 * @subpackage Bootstrap
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Bootstrap_Screen_Changelog extends Application_Bootstrap_Screen
{
    public const DISPATCHER = 'changelog.php';

    public function getDispatcher() : string
    {
        return self::DISPATCHER;
    }
    
    protected function _boot() : void
    {
        $this->enableScriptMode();
        $this->disableAuthentication();
        $this->createEnvironment();

        header('Content-Type:text/plain; encoding=utf-8');
        
        echo AppFactory::createWhatsNew()->toPlainText(WhatsNew::getDeveloperLangID());

        Application::exit('Shown the changelog.');
    }
}
