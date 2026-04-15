<?php
/**
 * @package API
 * @subpackage Cache
 */

declare(strict_types=1);

namespace Application\API\Cache;

use Application\API\APIException;

/**
 * Exception class for API cache-related errors.
 *
 * @package API
 * @subpackage Cache
 */
class APICacheException extends APIException
{
    /**
     * Thrown when a user-scoped API method returns an empty cache identifier.
     */
    public const int ERROR_EMPTY_USER_CACHE_IDENTIFIER = 59213009;

    /**
     * Thrown when an empty or path-traversal-containing method name is passed
     * to {@see APICacheManager::getMethodCacheFolder()}.
     */
    public const int ERROR_INVALID_METHOD_NAME = 59213010;

    /**
     * Reserved for logging context when a corrupt cache file is encountered.
     * Not thrown directly — the resilience path deletes the file and returns null.
     */
    public const int ERROR_CACHE_FILE_CORRUPT = 59213011;
}
