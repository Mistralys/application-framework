<?php
/**
 * @package DeeplHelper
 * @see \DeeplHelper\DeeplHelperException
 */

declare(strict_types=1);

namespace DeeplHelper;

use Application_Exception;

/**
 * @package DeeplHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DeeplHelperException extends Application_Exception
{

    public const int ERROR_DEEPL_PROXY_URL_EMPTY = 109602;
    public const int ERROR_DEEPL_API_KEY_NOT_SET = 109601;
}
