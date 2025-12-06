<?php
/**
 * @package API
 * @subpackage Response
 */

declare(strict_types=1);

namespace Application\API\Response;

use Application\API\APIMethodInterface;

/**
 * Base interface for all API responses.
 *
 * @package API
 * @subpackage Response
 */
interface ResponseInterface
{
    public function getMethod() : APIMethodInterface;
}
