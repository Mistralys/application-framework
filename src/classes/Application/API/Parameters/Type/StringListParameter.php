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
 * API Parameter: List of strings as an array.
 *
 * Accepts a comma-separated string or an array of strings. Each item is
 * whitespace-trimmed, and empty strings (including items that become empty
 * after trimming) are filtered out.
 *
 * **Null and empty resolution:**
 * - A `null` request value resolves to `null` (parameter absent).
 * - An empty string `""` resolves to `null`.
 * - An array or comma-separated string where all items are empty after trimming
 *   resolves to `null`.
 *
 * **Usage example:**
 * ```php
 * // Register the parameter on an API method
 * $param = $this->manageParams()
 *     ->addParam('tags', t('Tags'))
 *     ->stringList();
 *
 * // The request value "foo, bar, baz" resolves to ['foo', 'bar', 'baz']
 * // The request value "  , , " resolves to null (all-empty after trim)
 * // The request value null resolves to null (parameter absent)
 *
 * // Set a default value
 * $param->setDefaultValue('foo, bar');        // ['foo', 'bar']
 * $param->setDefaultValue(['foo', 'bar']);     // ['foo', 'bar']
 * $param->setDefaultValue(null);              // [] (empty array, no default)
 *
 * // Force a specific value regardless of request or default
 * $param->selectValue('foo, bar');            // ['foo', 'bar']
 * $param->selectValue(null);                  // [] (empty array)
 * ```
 *
 * **`@property` note:** The `@property string[] $defaultValue` annotation
 * overrides the parent's `mixed`-typed `$defaultValue` property for IDE
 * type-narrowing purposes. It is not a promoted property.
 *
 * @package API
 * @subpackage Parameters
 *
 * @property string[] $defaultValue
 */
class StringListParameter extends BaseAPIParameter
{
    use ListParameterTrait;
    public function getTypeLabel(): string
    {
        return t('String List');
    }

    /**
     * @return string[]
     */
    public function getDefaultValue(): array
    {
        return $this->defaultValue ?? array();
    }

    /**
     * @param array<int|string,mixed>|string|null $default A comma-separated string or an array of strings. Set to `NULL` to reset to an empty array. Other value types are rejected.
     * @return $this
     * @throws APIParameterException {@see APIParameterException::ERROR_INVALID_PARAM_VALUE}
     */
    public function setDefaultValue(int|float|bool|string|array|null $default) : self
    {
        return parent::setDefaultValue($this->filterValues($this->requireValidType($default)));
    }

    /**
     * @param array<int|string,mixed>|string|null $value A comma-separated string or an array of strings. Set to `NULL` to reset to an empty array. Other value types are rejected.
     * @return $this
     * @throws APIParameterException {@see APIParameterException::ERROR_INVALID_PARAM_VALUE}
     */
    public function selectValue(float|int|bool|array|string|null $value): self
    {
        return parent::selectValue($this->filterValues($this->requireValidType($value)));
    }

    /**
     * @param array<int|string,mixed> $values
     * @return string[]
     */
    private function filterValues(array $values) : array
    {
        $result = array();

        foreach($values as $item)
        {
            $item = trim((string)$item);

            if($item === '') {
                continue;
            }

            $result[] = $item;
        }

        return $result;
    }



    /**
     * @return string[]|null
     */
    protected function resolveValue(): array|null
    {
        $value = $this->getRequestParam()->get();

        if($value === null || $value === '') {
            return null;
        }

        if(!is_array($value) && !is_string($value)) {
            $this->result->makeWarning(
                'Ignoring non-array, non-string string list value.',
                ParamValidationInterface::VALIDATION_INVALID_VALUE_TYPE
            );
            return null;
        }

        if(is_string($value)) {
            $value = ConvertHelper::explodeTrim(',', $value);
        }

        $result = $this->filterValues($value);

        if(empty($result)) {
            return null;
        }

        return $result;
    }

    /**
     * @return string[]|null
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
