<?php
/**
 * Monitoring service script used to check the health status
 * of the system. Used by the shop monitoring to include SPIN
 * in the global monitoring. Serves XML and Plain text reports
 * according to the accept-headers that the client sends.
 *
 * text/xml sends XML,
 * text/html sends plain text
 * text/plain sends plain text
 *
 * @package Application
 * @subpackage TestDriver
 * @see Application_Bootstrap_Screen_HealthMonitor
 */

declare(strict_types=1);

/**
 * The bootstrapper that starts the target application screen.
 * @see Application_Bootstrap
 */
require_once __DIR__ . '/../../bootstrap.php';

Application_Bootstrap::bootClass(Application_Bootstrap_Screen_HealthMonitor::class);
