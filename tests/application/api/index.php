<?php
/**
 * Dispatcher for API calls: instantiates the API manager
 * which resolves the method to call and which content to
 * serve.
 *
 * @package Application
 * @subpackage TestDriver
 * @see APIBootstrap
 * @see APIManager
 */

declare(strict_types=1);

/**
 * The bootstrapper that starts the target application screen.
 * @see Application_Bootstrap
 */

use Application\API\APIManager;
use Application\Bootstrap\Screen\APIBootstrap;

require_once __DIR__ . '/../bootstrap.php';

Application_Bootstrap::bootClass(APIBootstrap::class);
