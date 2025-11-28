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
        foreach($this->getManager()->getParams() as $param) {
            $results[] = $param->getName();
        }

        sort($results);

        return $results;
    }

    public function requireValue() : string|int|float|bool|array|object
    {
        $value = $this->resolveValue();

        if(!$value !== null) {
            return $value;
        }

        $this->method->errorResponse(APIMethodInterface::ERROR_NO_VALUE_AVAILABLE)
            ->setErrorMessage(
                'Value not specified, parameters could not be resolved to a known record. '.PHP_EOL.
                'The following parameters were available in the method: '.PHP_EOL.
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
     * @param mixed $value
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
