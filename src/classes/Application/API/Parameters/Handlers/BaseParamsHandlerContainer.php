<?php

declare(strict_types=1);

namespace Application\API\Parameters\Handlers;

use Application\API\APIMethodInterface;
use Application\API\Parameters\APIParamManager;

abstract class BaseParamsHandlerContainer implements ParamsHandlerContainerInterface
{
    /**
     * @var APIHandlerInterface[]
     */
    private array $handlers = array();
    private APIMethodInterface $method;

    public function __construct(APIMethodInterface $method)
    {
        $this->method = $method;
    }

    public function getMethod(): APIMethodInterface
    {
        return $this->method;
    }

    public function getManager() : APIParamManager
    {
        return $this->method->manageParams();
    }

    protected function registerHandler(APIHandlerInterface $handler) : void
    {
        $this->handlers[] = $handler;
    }

    public function resolveValue() : mixed
    {
        foreach($this->handlers as $handler) {
            $value = $handler->resolveValue();
            if($value !== null) {
                return $value;
            }
        }

        return null;
    }

    public function getAll() : array
    {
        return $this->handlers;
    }

    /**
     * @return class-string<APIHandlerInterface>[]
     */
    public function getIDs() : array
    {
        $results = array();

        foreach($this->handlers as $handler) {
            $results[] = get_class($handler);
        }

        return $results;
    }

    /**
     * @return string[]
     */
    public function getParamNames() : array
    {
        $results = array();
        foreach($this->getAll() as $handler) {
            foreach($handler->getParams() as $param) {
                $results[] = $param->getName();
            }
        }

        $results = array_unique($results);

        sort($results);

        return $results;
    }

    abstract protected function isValidValueType(string|int|float|bool|array|object $value) : bool;

    public function requireValue() : string|int|float|bool|array|object
    {
        $value = $this->resolveValue();

        if($value !== null && $this->isValidValueType($value)) {
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
     * Selects the given value in all handlers that support value selection.
     *
     * > NOTE: This should be the final value type returned by the parameter or rule.
     *  > For example: If the parameter is an integer ID, this should select
     *  > the record object.
     *
     * @param string|int|float|bool|array<int|string,mixed>|object $value
     * @return $this
     */
    public function selectValue(string|int|float|bool|array|object $value): self
    {
        foreach($this->handlers as $handler) {
            $handler->selectValue($value);
        }

        return $this;
    }
}
