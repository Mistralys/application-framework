<?php

declare(strict_types=1);

namespace Application\API\Parameters\Handlers;

interface APIHandlerInterface
{
    /**
     * Selects a value directly for this parameter or rule, bypassing normal resolution.
     *
     * > NOTE: This should be the final value type returned by the parameter or rule.
     * > For example: If the parameter is an integer ID, this should select
     * > the record object.
     *
     * @param mixed $value
     * @return $this
     */
    public function selectValue(mixed $value) : self;

    /**
     * Resolves and returns the final, resolved value for this parameter or rule.
     *
     * > NOTE: This will return the final value type expected from this parameter or rule.
     * > For example: If the parameter is an integer ID, this should return
     * > the record object.
     *
     * @return mixed|NULL The resolved value, or NULL if not set/available.
     */
    public function resolveValue() : mixed;
}
