<?php

declare(strict_types=1);

namespace Application\API\Parameters\Handlers;

use Application\API\Parameters\APIParamManager;

interface ParamsHandlerContainerInterface
{
    public function getManager() : APIParamManager;

    /**
     * Resolves the value by checking each registered handler in order.
     * The first non-null value found is returned.
     *
     * @return mixed
     */
    public function resolveValue() : mixed;

    /**
     * Like {@see self::resolveValue()}, but guarantees a non-null return value.
     * If no value can be resolved, an exception is thrown.
     *
     * @return string|int|float|bool|array<int|string,mixed>|object
     */
    public function requireValue() : string|int|float|bool|array|object;

    /**
     * Selects the given value in all handlers that support value selection.
     * @param string|int|float|bool|array|object $value
     * @return $this
     */
    public function selectValue(string|int|float|bool|array|object $value): self;
}
