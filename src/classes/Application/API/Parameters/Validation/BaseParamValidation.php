<?php

declare(strict_types=1);

namespace Application\API\Parameters\Validation;

use AppUtils\OperationResult;

abstract class BaseParamValidation implements ParamValidationInterface
{
    abstract public function validate(int|float|bool|string|array|null $value, OperationResult $result) : void;
}
