<?php

declare(strict_types=1);

namespace Application\API\Parameters\Validation\Type;

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

    public function validate(int|float|bool|string|array|null $value, OperationResult $result) : void
    {
        if(!is_string($value)) {
            $result->makeError(
                sprintf('Invalid value type (using strict typing). Expected a string, %1$s given.', gettype($value)),
                ParamValidationInterface::VALIDATION_INVALID_VALUE_TYPE
            );
        }

        if(!preg_match($this->regex, $value)) {
            $result->makeError(
                'The value does not match the required regex format.',
                ParamValidationInterface::VALIDATION_INVALID_FORMAT_BY_REGEX
            );
        }
    }
}
