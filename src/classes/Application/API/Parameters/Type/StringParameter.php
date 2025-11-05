<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\Type;

use Application\API\Parameters\APIParameterException;
use Application\API\Parameters\BaseAPIParameter;
use Application\API\Parameters\Type\StringParam\StringValidations;
use Application\API\Parameters\Validation\ParamValidationInterface;
use Application\API\Parameters\Validation\Type\RegexValidation;
use AppUtils\RegexHelper;

/**
 * String API Parameter.
 *
 * - Accepts any string and numeric values.
 * - Numeric values will be converted to strings.
 * - Empty strings will be treated as null values.
 * - Null values will be treated as null values.
 * - Other value types will be ignored, and a warning will be issued.
 *
 * @package API
 * @subpackage Parameters
 *
 * @property string|null $defaultValue
 */
class StringParameter extends BaseAPIParameter
{
    public function getTypeLabel(): string
    {
        return t('String');
    }

    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }

    /**
     * @param string|int|float|null $default Numeric values will be converted to strings. All other types are rejected.
     * @return $this
     * @throws APIParameterException {@see APIParameterException::ERROR_INVALID_PARAM_VALUE}
     */
    public function setDefaultValue(int|float|bool|string|array|null $default) : self
    {
        return parent::setDefaultValue($this->requireValidType($default));
    }

    /**
     * @param string|int|float|null $value Numeric values will be converted to strings. All other types are rejected.
     * @return $this
     * @throws APIParameterException {@see APIParameterException::ERROR_INVALID_PARAM_VALUE}
     */
    public function selectValue(float|int|bool|array|string|null $value): self
    {
        return parent::selectValue($this->requireValidType($value));
    }


    /**
     * @param mixed $value
     * @return string|NULL
     * @throws APIParameterException {@see APIParameterException::ERROR_INVALID_PARAM_VALUE}
     */
    private function requireValidType(mixed $value) : ?string
    {
        if(is_numeric($value)) {
            return (string)$value;
        }

        if(is_string($value) || $value === null) {
            return $value;
        }

        throw new APIParameterException(
            'Invalid parameter value.',
            sprintf(
                'Expected a string, given: [%s].',
                gettype($value)
            ),
            APIParameterException::ERROR_INVALID_PARAM_VALUE
        );
    }

    /**
     * Returns a helper to choose among predefined string validations.
     * @return StringValidations
     */
    public function validateAs() : StringValidations
    {
        return new StringValidations($this);
    }

    public function validateByRegex(string $regex) : self
    {
        return $this->validateBy(new RegexValidation($regex));
    }

    protected function resolveValue(): ?string
    {
        $value = $this
            ->getRequestParam()
            ->get();

        if(is_numeric($value)) {
            $value = (string)$value;
        }

        if(is_string($value) && $value !== '') {
            return $value;
        }

        if($value === null || $value === '') {
            return $this->defaultValue;
        }

        $this->result->makeWarning(
            sprintf('The value must be a string, [%s] given.', gettype($value)),
            ParamValidationInterface::VALIDATION_INVALID_VALUE_TYPE
        );

        return null;
    }

    public function getValue() : ?string
    {
        $value = parent::getValue();
        if(is_string($value)) {
            return parent::getValue();
        }

        return null;
    }
}
