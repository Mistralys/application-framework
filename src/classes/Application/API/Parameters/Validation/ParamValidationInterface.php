<?php

declare(strict_types=1);

namespace Application\API\Parameters\Validation;

use AppUtils\OperationResult;

interface ParamValidationInterface
{
    public function validate(int|float|bool|string|array $value, OperationResult $result) : void;
}
