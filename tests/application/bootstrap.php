<?php
/**
 * Bootstrap file used to initialize the application environment.
 *
 * @package Application
 * @subpackage TestDriver
 */

const APP_ROOT = __DIR__;
const APP_INSTALL_FOLDER = __DIR__.'/../../src';
const APP_VENDOR_PATH = __DIR__ . '/../../vendor';

$configFile = __DIR__.'/config/test-ui.php';

if(!file_exists($configFile))
{
    die('config/test-ui.php file does not exist.');
}

require_once $configFile;

const APP_URL = TESTS_BASE_URL.'/tests/application';
const APP_VENDOR_URL = TESTS_BASE_URL.'/vendor';

// Require classes needed for the test application.
require_once APP_INSTALL_FOLDER.'/classes/Application/Bootstrap.php';

// The initialization includes the local configuration files,
// and defines all global application settings.
Application_Bootstrap::init();

// Select the session class to use for the application.
Application_Bootstrap_Screen::setSessionClass('\TestDriver\Session\TestSession'.TESTS_SESSION_TYPE);

$environments = Application_Environments::getInstance();
$environments->registerDev('test-application');
$environments->detect('test-application');
