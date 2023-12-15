<?php
/**
 * Bootstrap file used to initialize the application environment.
 *
 * @package Application
 * @subpackage TestDriver
 */

declare(strict_types=1);

const APP_ROOT = __DIR__;
const APP_INSTALL_FOLDER = __DIR__ . '/../../src';
const APP_VENDOR_PATH = __DIR__ . '/../../vendor';

$configFile = __DIR__ . '/config/test-ui-config.php';

if(!file_exists($configFile))
{
    die(sprintf('%s file does not exist.', basename($configFile)));
}

require_once $configFile;

// Require classes needed for the test application.
require_once APP_INSTALL_FOLDER.'/classes/Application/Bootstrap.php';

// The initialization includes the local configuration files,
// and defines all global application settings.
Application_Bootstrap::init();

// Select the session class to use for the application.
Application_Bootstrap_Screen::setSessionClass('\TestDriver\Session\TestSession'.TESTS_SESSION_TYPE);
