<?php

declare(strict_types=1);

namespace Application\API\Parameters\Type;

use Application\API\Parameters\BaseAPIParameter;
use Application\API\Parameters\Validation\Type\RegexValidation;
use AppUtils\RegexHelper;
use AppUtils\Request\RequestParam;

/**
 * @method string getValue()
 */
class StringParameter extends BaseAPIParameter
{
    private string $defaultValue = '';

    public function getDefaultValue(): string
    {
        return $this->defaultValue;
    }

    /**
     * @param string $default
     * @return $this
     */
    public function setDefaultValue(string $default) : self
    {
        $this->defaultValue = $default;
        return $this;
    }

    public function addValidationAlnum() : self
    {
        return $this->addValidationRegex('/^[a-zA-Z0-9]+$/');
    }

    public function addValidationAlpha() : self
    {
        return $this->addValidationRegex('/^[a-zA-Z]+$/');
    }

    public function addValidationAlias(bool $allowCapitalLetters=false) : self
    {
        $regex = RegexHelper::REGEX_ALIAS;
        if($allowCapitalLetters) {
            $regex = RegexHelper::REGEX_ALIAS_CAPITALS;
        }

        return $this->addValidation(new RegexValidation($regex));
    }

    public function addValidationRegex(string $regex) : self
    {
        return $this->addValidation(new RegexValidation($regex));
    }

    protected function resolveValue(): int|float|bool|string|array|null
    {
        return $this
            ->getRequestParam()
            ->getString();
    }
}
