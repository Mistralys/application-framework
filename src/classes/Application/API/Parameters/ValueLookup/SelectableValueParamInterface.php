<?php

declare(strict_types=1);

namespace Application\API\Parameters\ValueLookup;

use Application\API\Parameters\APIParameterInterface;

interface SelectableValueParamInterface extends APIParameterInterface
{
    /**
     * @return SelectableParamValue[]
     */
    public function getSelectableValues() : array;

    public function getDefaultSelectableValue() : ?SelectableParamValue;

    /**
     * @return string[]
     */
    public function getSelectableValueOptions() : array;

    /**
     * Checks whether the given value exists in the selectable values.
     *
     * @param mixed $value Numeric and boolean values will be converted to string for comparison.
     * @return bool
     */
    public function selectableValueExists(mixed $value) : bool;
}
