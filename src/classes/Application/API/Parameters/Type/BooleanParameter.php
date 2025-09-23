<?php

declare(strict_types=1);

namespace Application\API\Parameters\Type;

use Application\API\Parameters\BaseAPIParameter;
use AppUtils\ConvertHelper;

/**
 * @method bool getValue()
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
     * @param bool $default
     * @return $this
     */
    public function setDefaultValue(bool $default) : self
    {
        $this->defaultValue = $default;
        return $this;
    }
}
