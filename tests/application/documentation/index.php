<?php
/**
 * Documentation viewer, which collates the application's
 * documentation files with those from the framework.
 *
 * @package Application
 * @subpackage Core
 */

/**
 * The bootstrapper that starts the target application screen.
 * @see Application_Bootstrap
 */
require_once __DIR__.'/../bootstrap.php';

const APP_BUNDLED_DOCUMENTATION = true;

Application_Bootstrap::bootClass(Application_Bootstrap_Screen_Documentation::class);
