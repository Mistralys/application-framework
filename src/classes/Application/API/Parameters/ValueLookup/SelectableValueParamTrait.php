<?php

declare(strict_types=1);

namespace Application\API\Parameters\ValueLookup;

use AppUtils\ConvertHelper;

trait SelectableValueParamTrait
{
    /**
     * @return SelectableParamValue[]
     */
    public function getSelectableValues() : array
    {
        $values = $this->_getValues();

        usort($values, static function(SelectableParamValue $a, SelectableParamValue $b) : int {
            return strnatcasecmp($a->getLabel(), $b->getLabel());
        });

        return $values;
    }

    /**
     * @return string[]
     */
    public function getSelectableValueOptions() : array
    {
        $result = array();
        foreach($this->getSelectableValues() as $value) {
            $result[] = $value->getValue();
        }

        return $result;
    }

    public function selectableValueExists(mixed $value) : bool
    {
        if(is_bool($value)) {
            $value = ConvertHelper::boolStrict2string($value);
        }

        if(is_int($value) || is_float($value)) {
            $value = (string)$value;
        }

        return array_any(
            $this->getSelectableValues(),
            static fn($selectableValue) => $selectableValue->getValue() === $value
        );

    }

    /**
     * @return SelectableParamValue[]
     */
    abstract protected function _getValues() : array;
}
