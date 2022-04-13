<?php
/**
 * Upload dispatcher: backend used to upload media files
 * to be added to the media library.
 *
 * @package Application
 * @subpackage TestDriver
 * @see Application_Bootstrap_Screen_Upload
 */

declare(strict_types=1);

/**
 * The bootstrapper that starts the target application screen.
 * @see Application_Bootstrap
 */
require_once __DIR__ . '/../bootstrap.php';

Application_Bootstrap::bootClass(Application_Bootstrap_Screen_Upload::class);
