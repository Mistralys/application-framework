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
}
