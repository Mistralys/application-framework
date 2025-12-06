<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters;

/**
 * Interface for reserved API parameters.
 *
 * @package API
 * @subpackage Parameters
 */
interface ReservedParamInterface extends APIParameterInterface
{
    public function isEditable() : bool;
}
