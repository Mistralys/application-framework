<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\Validation\Type;

use Application\API\Parameters\APIParameterInterface;
use Application\API\Parameters\Validation\BaseParamValidation;
use AppUtils\OperationResult;

/**
 * Validating a parameter value by using a callback function
 * to check if the value exists.
 *
 * @package API
 * @subpackage Parameters
 */
class ValueExistsCallbackValidation extends BaseParamValidation
{
    public const int VALIDATION_VALUE_NOT_EXISTS = 183401;

    /**
     * @var (callable(float|int|bool|array|string|null) : bool)
     */
    private $callback;

    /**
     * @param (callable(float|int|bool|array|string|null) : bool) $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function validate(float|int|bool|array|string|null $value, OperationResult $result, APIParameterInterface $param): void
    {
        if(empty($value) && $value !== 0 && $value !== '0' && $value !== false) {
            // Nothing to validate
            return;
        }

        $callback = $this->callback;

        if (!$callback($value))
        {
            $result->makeError(
                sprintf(
                    'The value specified for API parameter `%s` does not exist.',
                    $param->getName()
                ),
                self::VALIDATION_VALUE_NOT_EXISTS
            );
        }
    }
}
