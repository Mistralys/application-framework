<?php
/**
 * @package User Interface
 * @subpackage Admin URLs
 */

declare(strict_types=1);

namespace UI\AdminURLs;

use Application_Exception;

/**
 * @package User Interface
 * @subpackage Admin URLs
 */
class AdminURLException extends Application_Exception
{
    public const int ERROR_INVALID_HOST = 169601;
}
