<?php
/**
 * @package API
 * @subpackage Response
 */

declare(strict_types=1);

namespace Application\API\Response;

use Application\API\APIMethodInterface;
use Application\API\Traits\JSONResponseTrait;

/**
 * Helper class that serializes the information on an API method
 * to an array that is included in JSON responses.
 *
 * @package API
 * @subpackage Response
 * @see JSONResponseTrait::_sendJSONData()
 */
class JSONInfoSerializer
{
    public const string KEY_REQUEST_MIME = 'requestMime';
    public const string KEY_SELECTED_VERSION = 'selectedVersion';
    public const string KEY_METHOD_NAME = 'methodName';
    public const string KEY_REQUEST_TIME = 'requestTime';
    public const string KEY_RESPONSE_MIME = 'responseMime';
    public const string KEY_DESCRIPTION = 'description';
    public const string KEY_AVAILABLE_VERSIONS = 'availableVersions';
    public const string KEY_DOCUMENTATION_URL = 'documentationURL';

    private APIMethodInterface $method;

    public function __construct(APIMethodInterface $method)
    {
        $this->method = $method;
    }

    public function toArray() : array
    {
        return array(
            self::KEY_METHOD_NAME => $this->method->getMethodName(),
            self::KEY_SELECTED_VERSION => $this->method->getActiveVersion(),
            self::KEY_AVAILABLE_VERSIONS => $this->method->getVersions(),
            self::KEY_DESCRIPTION => $this->method->getDescription(),
            self::KEY_REQUEST_MIME => $this->method->getRequestMime(),
            self::KEY_RESPONSE_MIME => $this->method->getResponseMime(),
            self::KEY_REQUEST_TIME => $this->method->getRequestTime()?->getISODate(true),
            self::KEY_DOCUMENTATION_URL => (string)$this->method->getDocumentationURL()
        );
    }
}
