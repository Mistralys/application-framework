<?php
/**
 * @package API
 * @subpackage Traits
 */

declare(strict_types=1);

namespace Application\API\Traits;

use Application\API\APIMethodInterface;

/**
 * @package API
 * @subpackage Traits
 * @see JSONResponseTrait
 */
interface JSONResponseInterface extends APIMethodInterface
{
    public const string RESPONSE_KEY_API = 'api';
    public const string RESPONSE_KEY_STATE = 'state';
    public const string RESPONSE_KEY_CODE = 'code';
    public const string RESPONSE_KEY_DATA = 'data';
    public const string RESPONSE_KEY_MESSAGE = 'message';
    public const string RESPONSE_STATE_ERROR = 'error';
    public const string RESPONSE_STATE_SUCCESS = 'success';

    public function getExampleJSONResponse() : array;

    /**
     * @return array<string,string> Key-value pairs of response keys and their descriptions.
     */
    public function getReponseKeyDescriptions() : array;
}
