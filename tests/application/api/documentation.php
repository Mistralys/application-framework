<?php
/**
 * Dispatcher for the API documentation interface.
 *
 * @package Application
 * @subpackage Dispatchers
 * @see APIDocumentationBootstrap
 */

declare(strict_types=1);

use Application\Bootstrap\Screen\APIDocumentationBootstrap;

/**
 * The bootstrapper that starts the target application screen.
 * @see Application_Bootstrap
 */
require_once __DIR__ . '/../bootstrap.php';

Application_Bootstrap::bootClass(APIDocumentationBootstrap::class);
