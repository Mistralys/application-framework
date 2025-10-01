<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters;

use AppUtils\Interfaces\StringableInterface;

/**
 * Interface for sets of parameters, used in rules.
 * The implementation is provided by {@see ParamSet}.
 *
 * @package API
 * @subpackage Parameters
 */
interface ParamSetInterface extends StringableInterface
{
    public function getLabel() : string;
    public function setLabel(?string $label) : self;
    public function getID(): string;
    /**
     * @return APIParameterInterface[]
     */
    public function getParams(): array;
    public function isValid() : bool;
    public function apply() : self;
    public function invalidate() : self;
}
