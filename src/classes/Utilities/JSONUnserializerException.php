<?php
/**
 * @package AppFramework
 * @subpackage Helpers
 */

declare(strict_types=1);

namespace Mistralys\AppFramework\Helpers;

use Application_Exception;
use Throwable;

/**
 * Special exception for JSON unserialization errors.
 *
 * @package AppFramework
 * @subpackage Helpers
 */
class JSONUnserializerException extends Application_Exception
{
    public const int ERROR_CANNOT_UNSERIALIZE_RESPONSE = 182001;

    public function __construct(string $errorContext, ?int $errorCode=null, string $serialized='', ?Throwable $previous=null)
    {
        // Log the exception so it is visible in the error log,
        // including the serialized data for debugging purposes.

        // NOTE: The JSON Converter cuts the data if it is too long,
        // so we log it separately as well.
        parent::__construct(
            'Cannot unserialize the JSON data.',
            sprintf(
                'Error context: %s '.PHP_EOL.
                'Serialized response data: '.PHP_EOL.
                '[RESPONSE]%s[RESPONSE]',
                $errorContext,
                str_replace('\/', '/', $serialized) // For better readability
            ),
            $errorCode ?? self::ERROR_CANNOT_UNSERIALIZE_RESPONSE,
            $previous
        );
    }
}
