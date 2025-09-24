<?php

declare(strict_types=1);

namespace Application\API\Parameters\Type;

use Application\API\Parameters\APIParameterException;
use Application\API\Parameters\BaseAPIParameter;

/**
 * @method int|null getValue()
 */
class IntegerParameter extends BaseAPIParameter
{
    private int $defaultValue = 0;

    public function getDefaultValue(): int
    {
        return $this->defaultValue;
    }

    /**
     * @param int|float|string $default
     * @return $this
     */
    public function setDefaultValue(mixed $default) : self
    {
        $this->requireValidType($default);

        $this->defaultValue = (int)$default;

        return $this;
    }

    private function requireValidType(mixed $value) : void
    {
        if(is_numeric($value)) {
            return;
        }

        throw new APIParameterException(
            'Invalid default value.',
            sprintf(
                'Expected a numeric value, given: [%s].',
                gettype($value)
            ),
            APIParameterException::ERROR_INVALID_DEFAULT_VALUE
        );
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
