<?php

declare(strict_types=1);

namespace Application\API\Parameters\Handlers;

use Application\API\Parameters\APIParameterException;
use Application\API\Parameters\APIParameterInterface;

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

    /**
     * Like {@see self::resolveValue()} but with a guaranteed non-null return value.
     * If no value can be resolved, an exception should be thrown.
     *
     * @return string|int|float|bool|array|object
     * @throws APIParameterException
     */
    public function requireValue() : string|int|float|bool|array|object;

    /**
     * Returns the list of parameters managed by this handler.
     * @return APIParameterInterface[]
     */
    public function getParams() : array;
}
