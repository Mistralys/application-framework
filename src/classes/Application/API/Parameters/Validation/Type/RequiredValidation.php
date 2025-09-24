<?php

declare(strict_types=1);

namespace Application\API\Parameters\Validation\Type;

use Application\API\Parameters\Validation\BaseParamValidation;
use Application\API\Parameters\Validation\ParamValidationInterface;
use AppUtils\OperationResult;

class RequiredValidation extends BaseParamValidation
{
    public function validate(float|int|bool|array|string|null $value, OperationResult $result): void
    {
        if(empty($value) && $value !== 0 && $value !== '0' && $value !== false)
        {
            $result->makeError(
                'The parameter is required.',
                ParamValidationInterface::VALIDATION_EMPTY_REQUIRED_PARAM
            );
        }
    }
}
