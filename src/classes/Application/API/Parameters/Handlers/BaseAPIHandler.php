<?php

declare(strict_types=1);

namespace Application\API\Parameters\Handlers;

use Application\API\APIMethodInterface;
use Application\API\Parameters\APIParameterException;
use Application\API\Parameters\APIParamManager;

abstract class BaseAPIHandler implements APIHandlerInterface
{
    protected mixed $selectedValue = null;
    protected APIMethodInterface $method;
    protected APIParamManager $manager;

    public function __construct(APIMethodInterface $method)
    {
        $this->method = $method;
        $this->manager = $this->method->manageParams();
    }

    public function getMethod(): APIMethodInterface
    {
        return $this->method;
    }

    public function selectValue(mixed $value) : self
    {
        $this->selectedValue = $value;
        return $this;
    }

    public function resolveValue() : mixed
    {
        return $this->selectedValue ?? $this->resolveValueFromSubject();
    }

    /**
     * Requires that the handler resolves a value.
     *
     * Returns the resolved value when one is available. When no value is
     * available, `->send()` is called on the error response, which
     * **terminates PHP request execution** — no code after `requireValue()`
     * runs in that case.
     *
     * The return type is declared as `string|int|float|bool|array|object`
     * rather than `never` because PHP does not permit `never` on a method that
     * subclasses may override with a non-`never` return type. The
     * `@phpstan-return never` annotation makes the termination contract
     * explicit for static analysis tooling.
     *
     * @return string|int|float|bool|array|object
     * @phpstan-return never
     */
    public function requireValue(): string|int|float|bool|array|object
    {
        $value = $this->resolveValue();

        if($value !== null) {
            return $value;
        }

        $this->method->errorResponse(APIMethodInterface::ERROR_NO_VALUE_AVAILABLE)
            ->setErrorMessage(
                'Value not specified, parameters could not be resolved to a known record. '.PHP_EOL.
                'The method has used the following parameters to resolve a value: '.PHP_EOL.
                '- %s',
                implode(PHP_EOL.'- ', $this->getParamNames())
            )
            ->send();
    }

    /**
     * @return string[]
     */
    public function getParamNames() : array
    {
        $result = array();

        foreach($this->getParams() as $param) {
            $result[] = $param->getName();
        }

        return $result;
    }

    /**
     * This is called when no value has been selected directly.
     * The value must be resolved from the parameter itself.
     *
     * **Null-return contract:** Implementations MUST return `null`
     * when the handler has no value to contribute (parameter absent,
     * value empty, or rule not registered). {@see BaseParamsHandlerContainer::resolveValue()}
     * iterates all registered handlers and uses "first non-null wins"
     * semantics — returning a non-null value (including an empty array)
     * will be treated as a successful resolution and prevent subsequent
     * handlers from being consulted.
     *
     * @return mixed The resolved value, or `null` if this handler has no value.
     */
    abstract protected function resolveValueFromSubject() : mixed;
}
