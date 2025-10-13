<?php

declare(strict_types=1);

namespace Application\API\Parameters\Validation\Type;

use Application\API\Parameters\APIParameterInterface;
use Application\API\Parameters\Validation\BaseParamValidation;
use Application\API\Parameters\Validation\ParamValidationInterface;
use AppUtils\OperationResult;

class RegexValidation extends BaseParamValidation
{
    private string $regex;

    public function __construct(string $regex)
    {
        $this->regex = $regex;
    }

    public function validate(int|float|bool|string|array|null $value, OperationResult $result, APIParameterInterface $param) : void
    {
        if($value === null) {
            // Nothing to validate
            return;
        }

        if(!is_string($value)) {
            $result->makeError(
                sprintf(
                    'Invalid value type for API parameter `%1$s` (using strict typing). '.PHP_EOL.
                    'Expected a string, %2$s given.',
                    $param->getName(),
                    gettype($value)
                ),
                ParamValidationInterface::VALIDATION_INVALID_VALUE_TYPE
            );
        }

        if(!preg_match($this->regex, $value)) {
            $result->makeError(
                sprintf(
                    'The value for API parameter `%s` does not match the required format. '.PHP_EOL.
                    'The regex to match is: '.PHP_EOL.
                    '%s',
                    $param->getName(),
                    $this->regex
                ),
                ParamValidationInterface::VALIDATION_INVALID_FORMAT_BY_REGEX
            );
        }
    }
}
