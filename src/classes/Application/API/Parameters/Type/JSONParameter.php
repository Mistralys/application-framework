<?php

declare(strict_types=1);

namespace Application\API\Parameters\Type;

use Application\API\Parameters\BaseAPIParameter;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;

class JSONParameter extends BaseAPIParameter
{
    private array $defaultValue = array();

    public function getDefaultValue(): array
    {
        return $this->defaultValue;
    }

    protected function resolveValue(): array|null
    {
        $value = $this->getRequestParam()->get();

        if(!is_string($value)) {
            return null;
        }

        try { return JSONConverter::json2array($value); }
        catch (JSONConverterException) {}

        return null;
    }

    public function setDefaultValue(array $defaultValue) : self
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }
}
