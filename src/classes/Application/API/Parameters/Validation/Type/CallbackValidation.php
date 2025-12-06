<?php

declare(strict_types=1);

namespace Application\API\Parameters\Validation\Type;

use Application\API\Parameters\APIParameterInterface;
use Application\API\Parameters\Validation\BaseParamValidation;
use AppUtils\OperationResult;

class CallbackValidation extends BaseParamValidation
{
    /**
     * @var (callable(int|float|bool|string|array, OperationResult, APIParameterInterface, mixed...) : void)
     */
    private $callback;

    /**
     * @var array<int,mixed>
     */
    private array $args;

    /**
     * @param (callable(int|float|bool|string|array, OperationResult, APIParameterInterface, mixed...) : void) $callback
     * @param mixed ...$args
     */
    public function __construct(callable $callback, ...$args)
    {
        $this->callback = $callback;
        $this->args = $args;
    }

    public function validate(float|int|bool|array|string|null $value, OperationResult $result, APIParameterInterface $param): void
    {
        if($value === null) {
            // Nothing to validate
            return;
        }

        $callback = $this->callback;
        $args = array_merge(array($value, $result, $param), $this->args);
        $callback(...$args);
    }
}
