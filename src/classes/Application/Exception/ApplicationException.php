<?php
/**
 * @package Application
 * @subpackage Core
 */

declare(strict_types=1);

namespace Application\Exception;

use Application_Exception;

/**
 * Simple exception extension that allows adding developer-oriented
 * debug information that only get displayed in error messages of the
 * application is in developer mode to protect that kind of sensitive
 * information.
 *
 * @package Application
 * @subpackage Core
 */
class ApplicationException extends Application_Exception
{

}
