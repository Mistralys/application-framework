<?php

declare(strict_types=1);

namespace Application\API\Parameters\Type;

use Application\API\Parameters\APIParameterException;
use Application\API\Parameters\BaseAPIParameter;
use AppUtils\ConvertHelper;

/**
 * @method bool|null getValue()
 */
class BooleanParameter extends BaseAPIParameter
{
    private bool $defaultValue = false;

    protected function resolveValue(): bool|null
    {
        $value = $this->getRequestParam()->get();

        if(ConvertHelper::isBoolean($value)) {
            return ConvertHelper::string2bool($value);
        }

        return null;
    }

    public function getDefaultValue(): bool
    {
        return $this->defaultValue;
    }

    /**
     * @param bool|string $default A boolean value, or a string that can be converted to boolean by {@see ConvertHelper::string2bool()}.
     * @return $this
     */
    public function setDefaultValue(mixed $default) : self
    {
        $this->defaultValue = $this->requireValidType($default);

        return $this;
    }

    private function requireValidType(mixed $value) : bool
    {
        if(ConvertHelper::isBoolean($value)) {
            return ConvertHelper::string2bool($value);
        }

        throw new APIParameterException(
            'Invalid default value.',
            sprintf(
                'Expected a boolean value, given: [%s].',
                gettype($value)
            ),
            APIParameterException::ERROR_INVALID_DEFAULT_VALUE
        );
    }
}
