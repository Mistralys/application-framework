<?php
/**
* Changelog view for developers: text-based with only the
* dev changelog entries from the WHATSNEW.xml file.
*
* @package Application
* @subpackage TestDriver
* @see Application_Bootstrap_Screen_Changelog
*/

declare(strict_types=1);

/**
 * The bootstrapper that starts the target application screen.
 * @see Application_Bootstrap
 */
require_once __DIR__.'/bootstrap.php';

Application_Bootstrap::bootClass(Application_Bootstrap_Screen_Changelog::class);
