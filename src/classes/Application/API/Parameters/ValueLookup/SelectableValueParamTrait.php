<?php

declare(strict_types=1);

namespace Application\API\Parameters\ValueLookup;

trait SelectableValueParamTrait
{
    public function getSelectableValues() : array
    {
        $values = $this->_getValues();

        usort($values, static function(SelectableParamValue $a, SelectableParamValue $b) : int {
            return strnatcasecmp($a->getLabel(), $b->getLabel());
        });

        return $values;
    }

    /**
     * @return SelectableParamValue[]
     */
    abstract protected function _getValues() : array;
}
