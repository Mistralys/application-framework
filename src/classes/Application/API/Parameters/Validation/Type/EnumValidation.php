<?php

declare(strict_types=1);

namespace Application\API\Parameters\Validation\Type;

use Application\API\Parameters\APIParameterException;
use Application\API\Parameters\Validation\BaseParamValidation;
use AppUtils\OperationResult;

class EnumValidation extends BaseParamValidation
{
    public const int VALIDATION_INVALID_VALUE = 183201;

    /**
     * @var array<int,int|float|string|bool>
     */
    private array $allowedValues = array();

    /**
     * @param array<int,int|float|string|bool|mixed> $allowedValues
     */
    public function __construct(array $allowedValues)
    {
        foreach($allowedValues as $value)
        {
            if (is_bool($value) || is_int($value) || is_float($value) || is_string($value)) {
                $this->allowedValues[] = $value;
                continue;
            }

            throw new APIParameterException(
                'Invalid value in allowed list.',
                sprintf(
                    'Allowed values must be of type int, float, string, or bool. %s given.',
                    gettype($value)
                ),
                APIParameterException::ERROR_INVALID_PARAM_CONFIG
            );
        }
    }

    public function validate(float|int|bool|array|string|null $value, OperationResult $result): void
    {
        if (in_array($value, $this->allowedValues, true)) {
            return;
        }

        $result->makeError(
            sprintf(
                'Invalid value (using strict typing). '.PHP_EOL.
                'Allowed values are: '.PHP_EOL.
                '- %s',
                implode(
                    PHP_EOL.'- ',
                    array_map(
                        static fn($v) => var_export($v, true),
                        $this->allowedValues
                    )
                )
            ),
            self::VALIDATION_INVALID_VALUE
        );
    }
}
