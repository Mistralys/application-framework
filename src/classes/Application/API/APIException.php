<?php
/**
 * @package API
 * @subpackage Core
 */

declare(strict_types=1);

namespace Application\API;

use Application_Exception;

/**
 * Exception class for API-related errors.
 *
 * @package API
 * @subpackage Core
 */
class APIException extends Application_Exception
{
    public const int ERROR_METHOD_NOT_IN_INDEX = 59213005;
    public const int ERROR_INTERNAL = 59213006;
    public const int ERROR_CANNOT_MODIFY_AFTER_VALIDATION = 59213007;
    public const int ERROR_INVALID_API_VERSION = 59213008;
}
