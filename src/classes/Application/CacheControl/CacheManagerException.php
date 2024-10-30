<?php
/**
 * @package Application
 * @subpackage CacheControl
 */

declare(strict_types=1);

namespace Application\CacheControl;

use Application_Exception;

/**
 * @package Application
 * @subpackage CacheControl
 */
class CacheManagerException extends Application_Exception
{
    public const ERROR_FAILED_TO_TRIGGER_REGISTRATION_EVENT = 166701;
}
