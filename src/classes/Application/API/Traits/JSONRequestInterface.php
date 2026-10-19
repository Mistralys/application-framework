<?php
/**
 * @package API
 * @subpackage Traits
 */

declare(strict_types=1);

namespace Application\API\Traits;

use Application\API\APIMethodInterface;
use AppUtils\ArrayDataCollection;

/**
 * @package API
 * @subpackage Traits
 * @see JSONRequestTrait
 */
interface JSONRequestInterface extends APIMethodInterface
{
    public const int ERROR_FAILED_TO_READ_INPUT = 182801;

    /**
     * Thrown by {@see \Application\API\BaseMethods\BaseAPIMethod::getRequestBody()}
     * when the request body exceeds the configured ceiling
     * ({@see \Application\ConfigSettings\AppConfig::getAPIMaxRequestBodySize()}),
     * whether the body was rejected outright (declared or actual length over
     * the ceiling) or PHP silently discarded it (`post_max_size`). Both
     * causes map to this single code — and HTTP 413 via
     * {@see \Application\API\ErrorResponse::makePayloadTooLarge()} — since the
     * client action is identical in either case: send a smaller body.
     */
    public const int ERROR_REQUEST_BODY_TOO_LARGE = 182802;

    /**
     * Error-data key carrying the configured byte ceiling
     * ({@see \Application\ConfigSettings\AppConfig::getAPIMaxRequestBodySize()})
     * alongside {@see self::ERROR_REQUEST_BODY_TOO_LARGE}, so a client can
     * tell by how much its request needs to shrink without parsing the
     * error message.
     */
    public const string RESPONSE_KEY_ERROR_MAX_REQUEST_BODY_SIZE = 'maxRequestBodySize';

    public const string RESPONSE_KEY_ERROR_JSON_REQUEST_DATA = 'JSONRequest';

    /**
     * Sub-key of {@see self::RESPONSE_KEY_ERROR_JSON_REQUEST_DATA} used when
     * the request body could not be parsed (invalid JSON, or a top level
     * that is not an object): carries the echo form of the raw body
     * recorded once by {@see JSONRequestTrait::getRequestData()} — the raw
     * body verbatim when it is at most `ECHO_MAX_STRING_LENGTH` characters,
     * otherwise `[N characters truncated]`. Never re-reads or re-parses
     * the body, so it cannot re-enter the parse on a repeated error response.
     */
    public const string RESPONSE_KEY_ERROR_UNPARSEABLE_BODY = 'unparseableBody';

    public function getRequestData() : ArrayDataCollection;
}