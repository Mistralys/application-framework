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
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;
use function AppUtils\parseVariable;


/**
 * JSON API Parameter: Accepts a JSON string and converts it to an array.
 *
 * > NOTE: If the value is already an array, it will be used as-is.
 *
 * @package API
 * @subpackage Parameters
 *
 * @property array<int|string,mixed>|null $defaultValue
 */
class JSONParameter extends BaseAPIParameter
{

    public function getTypeLabel(): string
    {
        return t('JSON');
    }

    public function getDefaultValue(): ?array
    {
        return $this->defaultValue;
    }

    protected function resolveValue(): array|null
    {
        $value = $this->getRequestParam()->get();

        if(empty($value)) {
            return null;
        }

        if(!is_string($value))
        {
            $this->result->makeWarning(
                'The JSON value must be a string.',
                ParamValidationInterface::VALIDATION_INVALID_VALUE_TYPE
            );

            return null;
        }

        try { return JSONConverter::json2array($value); }
        catch (JSONConverterException) {
            $this->result->makeError(
                'The given value is not valid JSON.',
                ParamValidationInterface::VALIDATION_INVALID_JSON_DATA
            );
        }

        return null;
    }

    /**
     * @param string $json
     * @return array<int|string,mixed>
     * @throws APIParameterException {@see APIParameterException::ERROR_INVALID_PARAM_VALUE}
     */
    private function convertJSON(string $json) : array
    {
        try
        {
            return JSONConverter::json2array($json);
        }
        catch(JSONConverterException $e)
        {
            throw new APIParameterException(
                'Invalid default value.',
                'Given string could not be parsed as JSON: ' . $e->getMessage(),
                APIParameterException::ERROR_INVALID_PARAM_VALUE,
                $e
            );
        }
    }

    /**
     * @param array<int|string,mixed>|string|null $value String values will be parsed as JSON.
     * @return $this
     * @throws APIParameterException {@see APIParameterException::ERROR_INVALID_PARAM_VALUE}
     */
    public function selectValue(float|int|bool|array|string|null $value): self
    {
        $value = $this->requireValidType($value);

        if(is_string($value)) {
            $value = $this->convertJSON($value);
        }

        return parent::selectValue($value);
    }

    /**
     * @param string|array<int|string,mixed>|null $default String values will be parsed as JSON.
     * @return $this
     * @throws APIParameterException
     */
    public function setDefaultValue(int|float|bool|string|array|null $default) : self
    {
        $default = $this->requireValidType($default);

        if(is_string($default)) {
            $default = $this->convertJSON($default);
        }

        return parent::setDefaultValue($default);
    }

    /**
     * @param mixed $value
     * @return string|array<int|string,mixed>|null
     * @throws APIParameterException {@see APIParameterException::ERROR_INVALID_PARAM_VALUE}
     */
    private function requireValidType(mixed $value) : string|array|null
    {
        if($value === null || is_string($value) || is_array($value)) {
            return $value;
        }

        throw new APIParameterException(
            'Invalid parameter value.',
            sprintf(
                'Expected an array or JSON string, given: [%s].',
                parseVariable($value)->enableType()->toString()
            ),
            APIParameterException::ERROR_INVALID_PARAM_VALUE
        );
    }

    /**
     * @return array<int|string,mixed>|null
     */
    public function getValue(): ?array
    {
        $value = parent::getValue();

        if(is_array($value)) {
            return $value;
        }

        return null;
    }
}
