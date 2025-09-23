<?php

declare(strict_types=1);

namespace Application\API\Parameters\Validation\Type;

use Application\API\Parameters\Validation\BaseParamValidation;
use AppUtils\OperationResult;

class CallbackValidation extends BaseParamValidation
{
    /**
     * @var callable(int|float|bool|string|array $value, OperationResult $result, ...$args) : void
     */
    private $callback;

    /**
     * @var array<int,mixed>
     */
    private array $args;

    /**
     * @param callable(int|float|bool|string|array $value, OperationResult $result, ...$args) : void $callback
     * @param mixed ...$args
     */
    public function __construct(callable $callback, ...$args)
    {
        $this->callback = $callback;
        $this->args = $args;
    }

    public function validate(float|int|bool|array|string $value, OperationResult $result): void
    {
        $callback = $this->callback;
        $args = array_merge(array($value, $result), $this->args);
        $callback(...$args);
    }
}
