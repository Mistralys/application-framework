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
use Application\API\Parameters\Validation\Type\MaxLengthValidation;
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
 * ## Validator helper naming conventions
 *
 * This class exposes two styles of validator-registering helpers that coexist
 * for historical reasons:
 *
 * - **`validateBy*` prefix** — procedural style; e.g. `validateByRegex()`.
 *   Used for validators that apply a validation rule without altering a named
 *   property of the parameter.
 * - **`set*` prefix** — property-setter style; e.g. `setMaxLength()`.
 *   Used for validators that correspond to a named, configurable attribute of
 *   the parameter (the max length is a property of the string, not just a
 *   validation rule).
 *
 * Both styles call `validateBy()` internally and return `$this` for fluent
 * chaining. New helpers should follow the `set*` convention when they model a
 * named parameter attribute, and `validateBy*` when they apply a standalone
 * rule with no corresponding attribute.
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

    /**
     * Registers a regex validation that requires the string value to match
     * the given PCRE pattern.
     *
     * @param string $regex A valid PCRE regex pattern (e.g. `/^[a-z]+$/i`).
     * @return $this
     */
    public function validateByRegex(string $regex) : self
    {
        return $this->validateBy(new RegexValidation($regex));
    }

    /**
     * Registers a max length validation that ensures the string value
     * does not exceed the specified number of characters (multibyte-safe).
     *
     * @param int $maxLength Maximum allowed character count.
     * @return $this
     */
    public function setMaxLength(int $maxLength) : self
    {
        return $this->validateBy(new MaxLengthValidation($maxLength));
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
            return $value;
        }

        return null;
    }
}
