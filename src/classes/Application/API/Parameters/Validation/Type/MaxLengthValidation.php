<?php

declare(strict_types=1);

namespace Application\API\Parameters\Validation\Type;

use Application\API\Parameters\APIParameterInterface;
use Application\API\Parameters\Validation\BaseParamValidation;
use Application\API\Parameters\Validation\ParamValidationInterface;
use AppUtils\OperationResult;

/**
 * Validates that a string parameter value does not exceed a maximum character
 * length, measured with `mb_strlen()` for multibyte safety.
 *
 * **Skip conditions** — the following values are passed through without error:
 * - `null`
 * - empty string (`''`)
 * - any non-string value (type enforcement is handled by other validators)
 *
 * When the value exceeds the configured limit, a
 * {@see ParamValidationInterface::VALIDATION_MAX_LENGTH_EXCEEDED} error is
 * recorded on the result.
 *
 * @package API
 * @subpackage Parameters
 * @see StringParameter::setMaxLength()
 */
class MaxLengthValidation extends BaseParamValidation
{
    private int $maxLength;

    public function __construct(int $maxLength)
    {
        $this->maxLength = $maxLength;
    }

    public function validate(int|float|bool|string|array|null $value, OperationResult $result, APIParameterInterface $param) : void
    {
        if($value === null || $value === '') {
            // Nothing to validate
            return;
        }

        if(!is_string($value)) {
            // Non-string values are skipped; type enforcement is handled by other validators
            return;
        }

        $length = mb_strlen($value);

        if($length > $this->maxLength) {
            $result->makeError(
                sprintf(
                    'The value for API parameter `%s` exceeds the maximum allowed length of %d character(s). '.PHP_EOL.
                    'Actual length: %d character(s).',
                    $param->getName(),
                    $this->maxLength,
                    $length
                ),
                ParamValidationInterface::VALIDATION_MAX_LENGTH_EXCEEDED
            );
        }
    }
}
