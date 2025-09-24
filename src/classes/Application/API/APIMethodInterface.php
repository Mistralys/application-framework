<?php
/**
 * @package API
 * @subpackage Core
 */

declare(strict_types=1);

namespace Application\API;

use Application_CORS;
use AppUtils\ArrayDataCollection;
use AppUtils\Interfaces\StringPrimaryRecordInterface;
use AppUtils\Microtime;
use UI\AdminURLs\AdminURLInterface;

/**
 * Interface for all API methods.
 *
 * @package API
 * @subpackage Core
 */
interface APIMethodInterface extends StringPrimaryRecordInterface
{
    public const int ERROR_REQUEST_DATA_EXCEPTION = 183001;
    public const int ERROR_RESPONSE_DATA_EXCEPTION = 183002;
    public const int ERROR_INVALID_REQUEST_PARAMS = 183003;

    public const string REQUEST_PARAM_API_VERSION = 'apiVersion';
    public const string REQUEST_PARAM_METHOD = 'method';

    public const string RESPONSE_KEY_ERROR_REQUEST_DATA = 'requestData';
    public const string RESPONSE_KEY_ERROR_PARAM_DETAILS = 'validationErrors';


    public function getInfo() : APIInfo;
    public function getMethodName() : string;
    public function getDescription() : string;
    public function getRequestMime() : string;
    public function getResponseMime() : string;
    public function getDocumentationURL() : AdminURLInterface;
    public function getRequestTime() : Microtime;
    /**
     * Retrieves an indexed array containing available API
     * version numbers that can be specified to work with.
     *
     * @return string[]
     */
    public function getVersions() : array;

    /**
     * Retrieves the current version of the API endpoint.
     *
     * @return string
     */
    public function getCurrentVersion() : string;

    /**
     * Gets the version that the method is currently
     * working with. If a valid version has been specified
     * in the request, that version is returned. Otherwise,
     * the current version is returned.
     *
     * @return string
     */
    public function getActiveVersion() : string;

    /**
     * Adds a domain name to the list of allowed cross-origin
     * request sources. Adding one of these enables CORS for
     * this API endpoint.
     *
     * > Note: use the wildcard <code>*</code> as domain to enable
     * > all cross-origin sources.
     *
     * @param string $domain
     * @return $this
     */
    public function allowCORSDomain(string $domain) : self;

    public function getCORS() : Application_CORS;

    /**
     * Processes the API request and sends the response.
     * @return never
     */
    public function process(): never;

    /**
     * Processes the method as usual, but instead of sending
     * the response to the client, it returns the response data
     * as an ArrayDataCollection object.
     *
     * > This is mostly used for unit testing API methods, but
     * > can potentially allow re-using API methods outside
     * > the API endpoint context.
     *
     * @return ArrayDataCollection
     * @throws APIException
     */
    public function processReturn(): ArrayDataCollection;
}
