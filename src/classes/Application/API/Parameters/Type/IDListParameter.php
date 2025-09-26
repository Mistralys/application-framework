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
 * @method int[]|null getValue()
 */
class IDListParameter extends BaseAPIParameter
{
    public function getTypeLabel(): string
    {
        return t('ID List');
    }

    /**
     * @var int[]
     */
    private array $defaultValue = array();

    /**
     * @return int[]
     */
    public function getDefaultValue(): array
    {
        return $this->defaultValue;
    }

    /**
     * @param array<int|string,int|float|string>|string $default An array of IDs or a comma-separated string of IDs.
     * @return $this
     */
    public function setDefaultValue(mixed $default) : self
    {
        $this->defaultValue = array();

        foreach($this->requireValidType($default) as $id)
        {
            if(!is_numeric($id)) {
                continue;
            }

            $this->defaultValue[] = (int)$id;
        }

        return $this;
    }

    /**
     * @param mixed $value
     * @return array<int|string,mixed>
     * @throws APIParameterException
     */
    private function requireValidType(mixed $value) : array
    {
        if(is_array($value)) {
            return $value;
        }

        if(is_string($value)) {
            return ConvertHelper::explodeTrim(',', $value);
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
}
