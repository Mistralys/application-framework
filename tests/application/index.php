<?php
/**
* Main dispatcher file, displays the framework test UI.
*
* @package Application
* @subpackage TestDriver
* @see Application_Bootstrap_Screen_Main
*/

declare(strict_types=1);

require_once __DIR__.'/bootstrap.php';

Application_Bootstrap::bootClass(Application_Bootstrap_Screen_Main::class);
