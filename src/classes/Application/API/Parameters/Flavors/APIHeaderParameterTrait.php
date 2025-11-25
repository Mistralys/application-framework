<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\Flavors;

/**
 * Trait used to help implementing API parameters that are
 * passed via HTTP headers.
 *
 * See the interface {@see APIHeaderParameterInterface} for more details.
 *
 * @package API
 * @subpackage Parameters
 *
 * @see APIHeaderParameterInterface
 */
trait APIHeaderParameterTrait
{
    protected function resolveValue(): ?string
    {
        return $this->getHeaderValue();
    }
}
