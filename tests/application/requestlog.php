<?php
/**
 * Request log viewer and management screen.
 *
 * @package Application
 * @subpackage TestDriver
 * @see Application_Bootstrap_Screen_RequestLog
 * @see Application_RequestLog
 */

declare(strict_types=1);

/**
 * The bootstrapper that starts the target application screen.
 * @see Application_Bootstrap
 */
require_once __DIR__.'/bootstrap.php';

Application_Bootstrap::bootClass(Application_Bootstrap_Screen_RequestLog::class);
