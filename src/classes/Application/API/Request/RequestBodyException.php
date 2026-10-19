<?php
/**
 * @package API
 * @subpackage Request
 */

declare(strict_types=1);

namespace Application\API\Request;

use Application\API\APIException;

/**
 * Internal exception thrown by {@see RequestBodyReader} while reading the
 * raw request body. It is never meant to reach a client directly: the
 * intended caller, `Application\API\BaseMethods\BaseAPIMethod::getRequestBody()`,
 * is expected to convert it into a client-facing error response once that
 * wiring lands.
 *
 * @package API
 * @subpackage Request
 */
class RequestBodyException extends APIException
{
    /**
     * Thrown when the declared (`Content-Length`) or actually read body
     * length exceeds the configured ceiling.
     */
    public const int ERROR_BODY_TOO_LARGE = 59213012;

    /**
     * Thrown when the body reads as an empty string despite a positive
     * declared length — PHP silently discarded it because it exceeded
     * `post_max_size`.
     */
    public const int ERROR_BODY_DISCARDED = 59213013;

    /**
     * Thrown when the underlying stream could not be read at all.
     */
    public const int ERROR_BODY_READ_FAILED = 59213014;
}
