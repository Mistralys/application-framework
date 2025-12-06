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
    public const string RESPONSE_KEY_ERROR_JSON_REQUEST_DATA = 'JSONRequest';

    public function getRequestData() : ArrayDataCollection;
}