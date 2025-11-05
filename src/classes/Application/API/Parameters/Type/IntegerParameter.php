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
 * @property int|NULL $defaultValue
 */
class IntegerParameter extends BaseAPIParameter
{
    public function getTypeLabel(): string
    {
        return t('Integer');
    }

    public function getDefaultValue(): ?int
    {
        return $this->defaultValue;
    }

    /**
     * @param int|float|string|null $default The default value. Must be numeric or `NULL`, all other types are rejected.
     * @return $this
     */
    public function setDefaultValue(int|float|bool|string|array|null $default) : self
    {
        return parent::setDefaultValue($this->requireValidType($default));
    }

    /**
     * @param int|float|string|null $value String and float values will be converted to integer.
     * @return $this
     * @throws APIParameterException
     */
    public function selectValue(float|int|bool|array|string|null $value): self
    {
        return parent::selectValue($this->requireValidType($value));
    }

    private function requireValidType(mixed $value) : ?int
    {
        if($value !== 0 && empty($value)) {
            return null;
        }

        if(is_numeric($value)) {
            return (int)$value;
        }

        throw new APIParameterException(
            'Invalid default value.',
            sprintf(
                'Expected a numeric value, given: [%s].',
                gettype($value)
            ),
            APIParameterException::ERROR_INVALID_PARAM_VALUE
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

    public function getValue(): ?int
    {
        $value = parent::getValue();

        if(is_int($value)) {
            return $value;
        }

        return null;
    }
}
