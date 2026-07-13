<?php

declare(strict_types=1);

namespace Application\API\Parameters\Validation\Type;

use Application\API\Parameters\APIParameterInterface;
use Application\API\Parameters\Validation\BaseParamValidation;
use Application\API\Parameters\Validation\ParamValidationInterface;
use AppUtils\OperationResult;

class RequiredValidation extends BaseParamValidation
{
    public function validate(float|int|bool|array|string|null $value, OperationResult $result, APIParameterInterface $param): void
    {
        // An array value — even an empty one — is a provided value.
        // Only null means the parameter was not sent.
        if(is_array($value)) {
            return;
        }

        // Exempt falsy-but-valid scalar values: 0 (integer zero), '0' (string zero),
        // and false (boolean false) must not be treated as missing.
        if(empty($value) && $value !== 0 && $value !== '0' && $value !== false)
        {
            $result->makeError(
                sprintf('The API parameter `%s` is required.', $param->getName()),
                ParamValidationInterface::VALIDATION_EMPTY_REQUIRED_PARAM
            );
        }
    }
}
