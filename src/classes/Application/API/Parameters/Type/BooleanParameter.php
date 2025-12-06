<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\Type;

use Application\API\Parameters\APIParameterException;
use Application\API\Parameters\APIParameterInterface;
use Application\API\Parameters\BaseAPIParameter;
use Application\API\Parameters\Validation\ParamValidationInterface;
use Application\API\Parameters\ValueLookup\SelectableParamValue;
use Application\API\Parameters\ValueLookup\SelectableValueParamInterface;
use Application\API\Parameters\ValueLookup\SelectableValueParamTrait;
use AppUtils\ConvertHelper;
use function AppUtils\parseVariable;

/**
 * Boolean parameter type. Also accepts string values that can be converted to boolean,
 * as supported by {@see ConvertHelper::string2bool()}.
 *
 * @package API
 * @subpackage Parameters
 *
 * @property bool|NULL $defaultValue
 */
class BooleanParameter extends BaseAPIParameter implements SelectableValueParamInterface
{
    use SelectableValueParamTrait;

    public function getTypeLabel(): string
    {
        return t('Boolean');
    }

    protected function resolveValue(): ?bool
    {
        $value = $this->getRequestParam()->get();

        if($value === null || $value === '') {
            return null;
        }

        if(ConvertHelper::isBoolean($value)) {
            return ConvertHelper::string2bool($value);
        }

        $this->result->makeWarning(
            sprintf(
                'Expected a boolean value or compatible string, given: [%s].',
                gettype($value)
            ),
            ParamValidationInterface::VALIDATION_INVALID_VALUE_TYPE
        );

        return null;
    }

    public function getDefaultValue(): ?bool
    {
        return $this->defaultValue;
    }

    /**
     * @param bool|string|int|null $default A boolean value, or a string that can be converted to boolean by {@see ConvertHelper::string2bool()}. Other value types are rejected.
     * @return $this
     * @throws APIParameterException {@see APIParameterException::ERROR_INVALID_PARAM_VALUE}
     */
    public function setDefaultValue(int|float|bool|string|array|null $default) : self
    {
        return parent::setDefaultValue($this->requireValidType($default));
    }

    /**
     * @param bool|string|int|null $value A boolean value, or a string that can be converted to boolean by {@see ConvertHelper::string2bool()}. Other value types are rejected.
     * @return $this
     * @throws APIParameterException {@see APIParameterException::ERROR_INVALID_PARAM_VALUE}
     */
    public function selectValue(float|int|bool|array|string|null $value): self
    {
        return parent::selectValue($this->requireValidType($value));
    }

    private function requireValidType(mixed $value) : ?bool
    {
        if(empty($value)) {
            return null;
        }

        if(ConvertHelper::isBoolean($value)) {
            return ConvertHelper::string2bool($value);
        }

        throw new APIParameterException(
            'Invalid default value.',
            sprintf(
                'Expected a boolean value, given: [%s].',
                parseVariable($value)->enableType()->toString()
            ),
            APIParameterException::ERROR_INVALID_PARAM_VALUE
        );
    }

    public function getDefaultSelectableValue(): ?SelectableParamValue
    {
        return null;
    }

    protected function _getValues(): array
    {
        return array(
            new SelectableParamValue('true', 'True'),
            new SelectableParamValue('false', 'False'),
            new SelectableParamValue('yes', 'Yes'),
            new SelectableParamValue('no', 'No')
        );
    }

    public function getValue(): ?bool
    {
        $value = parent::getValue();

        if(is_bool($value)) {
            return $value;
        }

        return null;
    }
}
