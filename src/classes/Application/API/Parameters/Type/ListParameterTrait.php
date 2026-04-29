<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\Type;

use Application\API\Parameters\APIParameterException;
use AppUtils\ConvertHelper;

/**
 * Shared input normalisation logic for list-type API parameters.
 *
 * Used by {@see IDListParameter} and {@see StringListParameter} to
 * convert raw API input values into arrays.
 *
 * @package API
 * @subpackage Parameters
 */
trait ListParameterTrait
{
    /**
     * Normalises a raw API input value into an array.
     *
     * - `null` → empty array
     * - `array` → passthrough
     * - `string` → comma-separated explode with trim
     * - anything else → throws
     *
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
}
