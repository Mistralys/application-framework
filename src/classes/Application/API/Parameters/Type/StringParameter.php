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
 */
class StringParameter extends BaseAPIParameter
{
    public function getTypeLabel(): string
    {
        return t('String');
    }

    private ?string $defaultValue = null;

    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }

    /**
     * @param string|null|mixed $default The default value. Must be a string or null, all other types are rejected.
     * @return $this
     */
    public function setDefaultValue(mixed $default) : self
    {
        $this->defaultValue = $this->requireValidType($default);

        return $this;
    }

    /**
     * @param mixed $value
     * @return string|NULL
     * @throws APIParameterException
     */
    private function requireValidType(mixed $value) : ?string
    {
        if(is_string($value) || $value === null) {
            return $value;
        }

        throw new APIParameterException(
            'Invalid default value.',
            sprintf(
                'Expected a string, given: [%s].',
                gettype($value)
            ),
            APIParameterException::ERROR_INVALID_DEFAULT_VALUE
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
