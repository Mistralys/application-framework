<?php
/**
 * Ajax dispatcher: determines the ajax method to run
 * and displays the result in the requested format.
 *
 * @package Application
 * @subpackage TestDriver
 * @see Application_Bootstrap_Screen_Ajax
 */

declare(strict_types=1);

/**
 * The bootstrapper that starts the target application screen.
 * @see Application_Bootstrap
 */
require_once __DIR__ . '/../bootstrap.php';

Application_Bootstrap::bootClass(Application_Bootstrap_Screen_Ajax::class);
