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


/**
 * JSON API Parameter: Accepts a JSON string and converts it to an array.
 *
 * > NOTE: If the value is already an array, it will be used as-is.
 *
 * @package API
 * @subpackage Parameters
 *
 * @method array<int|string,mixed>|null getValue()
 */
class JSONParameter extends BaseAPIParameter
{

    private ?array $defaultValue = null;

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
     * @throws APIParameterException {@see APIParameterException::ERROR_INVALID_DEFAULT_VALUE}
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
                APIParameterException::ERROR_INVALID_DEFAULT_VALUE,
                $e
            );
        }
    }

    public function setDefaultValue(mixed $default) : self
    {
        $default = $this->requireValidType($default);

        if(is_string($default)) {
            $default = $this->convertJSON($default);
        }

        $this->defaultValue = $default;

        return $this;
    }

    /**
     * @param mixed $value
     * @return string|array<int|string,mixed>|null
     * @throws APIParameterException {@see APIParameterException::ERROR_INVALID_DEFAULT_VALUE}
     */
    private function requireValidType(mixed $value) : string|array|null
    {
        if($value === null || is_string($value) || is_array($value)) {
            return $value;
        }

        throw new APIParameterException(
            'Invalid default value.',
            sprintf(
                'Expected an array, given: [%s].',
                gettype($value)
            ),
            APIParameterException::ERROR_INVALID_DEFAULT_VALUE
        );
    }
}
