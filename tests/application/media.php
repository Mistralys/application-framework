<?php
/**
* Media script for displaying media files.
*
* @package Application
* @subpackage TestDriver
* @see Application_Bootstrap_Screen_Media
*/

declare(strict_types=1);

/**
 * The bootstrapper that starts the target application screen.
 * @see Application_Bootstrap
 */
require_once __DIR__.'/bootstrap.php';

Application_Bootstrap::bootClass(Application_Bootstrap_Screen_Media::class);
