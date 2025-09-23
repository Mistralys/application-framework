<?php

declare(strict_types=1);

namespace Application\API\Parameters\Type;

use Application\API\Parameters\APIParameterException;
use Application\API\Parameters\BaseAPIParameter;
use Application\API\Parameters\Validation\Type\RegexValidation;
use AppUtils\RegexHelper;

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
    public function setDefaultValue(mixed $default) : self
    {
        $this->defaultValue = $this->requireValidType($default);

        return $this;
    }

    /**
     * @param mixed $value
     * @return string
     * @throws APIParameterException
     */
    private function requireValidType(mixed $value) : string
    {
        if(is_string($value)) {
            return $value;
        }

        throw new APIParameterException(
            'Invalid default value.',
            sprintf(
                'Expected a string, given: [%s].',
                gettype($value)
            ),
            APIParameterException::ERROR_INVALID_DEFAULT_VALUE
        );
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

    protected function resolveValue(): ?string
    {
        $value = $this
            ->getRequestParam()
            ->getString();

        if($value !== '') {
            return $value;
        }

        return null;
    }
}
