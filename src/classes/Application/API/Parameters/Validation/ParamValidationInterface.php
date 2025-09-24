<?php

declare(strict_types=1);

namespace Application\API\Parameters\Validation;

use AppUtils\OperationResult;

interface ParamValidationInterface
{
    public const int VALIDATION_NON_NUMERIC_ID = 183501;
    public const int VALIDATION_INVALID_VALUE_TYPE = 183502;
    public const int VALIDATION_INVALID_JSON_DATA = 183503;
    public const int VALIDATION_INVALID_FORMAT_BY_REGEX = 183504;
    public const int VALIDATION_WARNING_FLOAT_TO_INT = 183505;

    public function validate(int|float|bool|string|array $value, OperationResult $result) : void;
}
