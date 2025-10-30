<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\Type;

use Application\API\Parameters\APIParameterException;
use Application\API\Parameters\BaseAPIParameter;
use Application\API\Parameters\Validation\ParamValidationInterface;

/**
 * Integer API Parameter.
 *
 * > NOTE: Will convert float values to integers, with a warning.
 *
 * @package API
 * @subpackage Parameters
 *
 * @method int|null getValue()
 */
class IntegerParameter extends BaseAPIParameter
{
    public function getTypeLabel(): string
    {
        return t('Integer');
    }

    private int $defaultValue = 0;

    public function getDefaultValue(): int
    {
        return $this->defaultValue;
    }

    /**
     * @param int|float|string|mixed $default The default value. Must be numeric, all other types are rejected.
     * @return $this
     */
    public function setDefaultValue(mixed $default) : self
    {
        $this->requireValidType($default);

        $this->defaultValue = (int)$default;

        return $this;
    }

    private function requireValidType(mixed $value) : void
    {
        if(is_numeric($value)) {
            return;
        }

        throw new APIParameterException(
            'Invalid default value.',
            sprintf(
                'Expected a numeric value, given: [%s].',
                gettype($value)
            ),
            APIParameterException::ERROR_INVALID_DEFAULT_VALUE
        );
    }

    protected function resolveValue(): ?int
    {
        $value = $this->getRequestParam()->get();

        if($value === null || $value === '') {
            return null;
        }

        if(is_numeric($value))
        {
            $int = (int)$value;

            if((string)$int !== (string)$value) {
                $this->result->makeWarning(
                    sprintf(
                        'Float value [%s] has been automatically converted to integer [%s].',
                        $value,
                        $int
                    ),
                    ParamValidationInterface::VALIDATION_WARNING_FLOAT_TO_INT
                );
            }

            return $int;
        }

        $this->result->makeWarning(
            sprintf(
                'Expected an integer value, given: [%s].',
                gettype($value)
            ),
            ParamValidationInterface::VALIDATION_INVALID_VALUE_TYPE
        );

        return null;
    }
}
