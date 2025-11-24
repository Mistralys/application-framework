<?php
/**
 * @package Connectors
 * @subpackage Headers
 */

declare(strict_types=1);

namespace Connectors\Headers;

use AppUtils\Baskets\GenericStringPrimaryBasket;

/**
 * Utility class used to store HTTPHeader objects for use
 * in connector requests.
 *
 * @package Connectors
 * @subpackage Headers
 *
 * @method HTTPHeader[] getAll()
 * @method HTTPHeader getByID(string $id)
 */
class HTTPHeadersBasket extends GenericStringPrimaryBasket
{
    public function addHeader(string $name, string $value): void
    {
        $this->addItem(new HTTPHeader($name, $value));
    }
}
