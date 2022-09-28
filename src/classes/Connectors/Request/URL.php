<?php
/**
 * @package Connectors
 * @supackage Request
 * @see Connectors_Request_URL
 */

declare(strict_types=1);

/**
 * Handles a URL-based API request.
 *
 * @package Connectors
 * @supackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Connectors_Request_URL extends Connectors_Request
{
    protected function _getHashData() : array
    {
        return array();
    }
}
