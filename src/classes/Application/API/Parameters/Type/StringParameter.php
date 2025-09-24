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
 * @method string|null getValue()
 */
class StringParameter extends BaseAPIParameter
{
    private ?string $defaultValue = null;

    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }

    /**
     * @param string|null $default
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

    public function validateAsAlphanumeric() : self
    {
        return $this->validateByRegex('/^[a-zA-Z0-9]+$/');
    }

    public function validateAsAlphabetical() : self
    {
        return $this->validateByRegex('/^[a-zA-Z]+$/');
    }

    public function validateAsAlias(bool $allowCapitalLetters=false) : self
    {
        $regex = RegexHelper::REGEX_ALIAS;
        if($allowCapitalLetters) {
            $regex = RegexHelper::REGEX_ALIAS_CAPITALS;
        }

        return $this->validateBy(new RegexValidation($regex));
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
}
