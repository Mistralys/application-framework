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
use AppUtils\ConvertHelper;

/**
 * API Parameter: List of integer IDs as an array.
 *
 * @package API
 * @subpackage Parameters
 *
 * @property int[] $defaultValue
 */
class IDListParameter extends BaseAPIParameter
{
    public function getTypeLabel(): string
    {
        return t('ID List');
    }

    /**
     * @return int[]
     */
    public function getDefaultValue(): array
    {
        return $this->defaultValue;
    }

    /**
     * @param array<int|string,int|float|string>|string|NULL $default An array of IDs or a comma-separated string of IDs. Set to `NULL` to reset to an empty array. Other value types are ignored.
     * @return $this
     */
    public function setDefaultValue(int|float|bool|string|array|null $default) : self
    {
        return parent::setDefaultValue($this->filterValues($this->requireValidType($default)));
    }

    /**
     * @param array<int|string,int|float|string>|string|null $value
     * @return BaseAPIParameter
     * @throws APIParameterException
     */
    public function selectValue(float|int|bool|array|string|null $value): BaseAPIParameter
    {
        return parent::selectValue($this->filterValues($this->requireValidType($value)));
    }

    /**
     * @param array<int|string,mixed> $values
     * @return int[]
     */
    private function filterValues(array $values) : array
    {
        $result = array();

        foreach($values as $id)
        {
            if(!is_numeric($id)) {
                continue;
            }

            $result[] = (int)$id;
        }

        return $result;
    }

    /**
     * @param mixed $value
     * @return array<int|string,mixed>
     * @throws APIParameterException {@see APIParameterException::ERROR_INVALID_PARAM_VALUE}
     */
    private function requireValidType(mixed $value) : array
    {
        if($value === null) {
            return array();
        }

        if(is_array($value)) {
            return $value;
        }

        if(is_string($value)) {
            return ConvertHelper::explodeTrim(',', $value);
        }

        throw new APIParameterException(
            'Invalid parameter value.',
            sprintf(
                'Expected an array or comma-separated string, given: [%s].',
                gettype($value)
            ),
            APIParameterException::ERROR_INVALID_PARAM_VALUE
        );
    }

    /**
     * @return int[]|null
     */
    protected function resolveValue(): array|null
    {
        $value = $this->getRequestParam()->get();

        if($value === null) {
            return null;
        }

        if(is_numeric($value)) {
            $value = (string)$value;
        }

        if(!is_array($value) && !is_string($value)) {
            $this->result->makeWarning(
                'Ignoring non-array, non-string ID list value.',
                ParamValidationInterface::VALIDATION_INVALID_VALUE_TYPE
            );
            return null;
        }

        if(is_string($value)) {
            $value = ConvertHelper::explodeTrim(',', $value);
        }

        $result = array();
        foreach($value as $id)
        {
            if(!is_numeric($id)) {
                $this->result->makeWarning(
                    sprintf('Ignoring non-numeric ID value: [%s]', toString($id)),
                    ParamValidationInterface::VALIDATION_NON_NUMERIC_ID
                );
                continue;
            }

            $result[] = (int)$id;
        }

        return $result;
    }

    /**
     * @return int[]|null
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
