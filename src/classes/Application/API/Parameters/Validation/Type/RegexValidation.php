<?php

declare(strict_types=1);

namespace Application\API\Parameters\Validation\Type;

use Application\API\Parameters\Validation\BaseParamValidation;
use AppUtils\OperationResult;

class RegexValidation extends BaseParamValidation
{
    public const int VALIDATION_INVALID_TYPE = 183301;
    public const int VALIDATION_INVALID_FORMAT = 183302;

    private string $regex;

    public function __construct(string $regex)
    {
        $this->regex = $regex;
    }

    public function validate(int|float|bool|string|array $value, OperationResult $result) : void
    {
        if(!is_string($value)) {
            $result->makeError(
                sprintf('Invalid value type (using strict typing). Expected a string, %1$s given.', gettype($value)),
                self::VALIDATION_INVALID_TYPE
            );
        }

        if(!preg_match($this->regex, $value)) {
            $result->makeError(
                'The value does not match the required format.',
                self::VALIDATION_INVALID_FORMAT
            );
        }
    }
}
