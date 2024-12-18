<?php
/**
 * @package DBHelper
 * @subpackage Admin Screens
 */

declare(strict_types=1);

namespace DBHelper\Admin;

use Application_Exception;

/**
 * @package DBHelper
 * @subpackage Admin Screens
 */
class DBHelperAdminException extends Application_Exception
{
    public const ERROR_NO_RECORD_IN_REQUEST = 169701;
}
