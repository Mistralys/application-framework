<?php
/**
* Cronjob script that performs regular cleanup operations.
* Does not generate any output by default, except in case of an error.
*
* Parameters:
*
* - debug [yes/no]
*   Whether to enable the debug mode.
*   No DB transactions are committed, and application
*   log messages are displayed.
*
* @package Application
* @subpackage TestDriver
* @see Application_Bootstrap_Screen_Cronjobs
*/

declare(strict_types=1);

/**
 * The bootstrapper that starts the target application screen.
 * @see Application_Bootstrap
 */
require_once __DIR__.'/bootstrap.php';

Application_Bootstrap::bootClass(Application_Bootstrap_Screen_Cronjobs::class);
