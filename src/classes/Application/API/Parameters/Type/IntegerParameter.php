<?php

declare(strict_types=1);

namespace Application\API\Parameters\Type;

use Application\API\Parameters\BaseAPIParameter;

/**
 * @method int getValue()
 */
class IntegerParameter extends BaseAPIParameter
{
    private int $defaultValue = 0;

    public function getDefaultValue(): int
    {
        return $this->defaultValue;
    }

    /**
     * @param int $default
     * @return $this
     */
    public function setDefaultValue(int $default) : self
    {
        $this->defaultValue = $default;
        return $this;
    }

    protected function resolveValue(): ?int
    {
        $value = $this->getRequestParam()->get();

        if(is_numeric($value)) {
            return (int)$value;
        }

        return null;
    }
}
