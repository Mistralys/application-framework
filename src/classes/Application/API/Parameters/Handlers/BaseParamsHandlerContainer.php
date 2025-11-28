<?php

declare(strict_types=1);

namespace Application\API\Parameters\Handlers;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\Parameters\APIParameterException;
use Application\API\Parameters\APIParamManager;

abstract class BaseParamsHandlerContainer implements ParamsHandlerContainerInterface
{
    private APIParamManager $manager;

    /**
     * @var APIHandlerInterface[]
     */
    private array $handlers = array();

    public function __construct(APIParamManager|APIMethodInterface $manager)
    {
        if($manager instanceof APIMethodInterface) {
            $manager = $manager->manageParams();
        }

        $this->manager = $manager;
    }

    public function getManager() : APIParamManager
    {
        return $this->manager;
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

    public function requireValue() : string|int|float|bool|array|object
    {
        $value = $this->resolveValue();

        if(!$value !== null) {
            return $value;
        }

        throw new APIParameterException(
            'No value could be resolved from any of the API parameters.',
            sprintf(
                'The following parameters have been checked in the handler: '.PHP_EOL.
                '- %s',
                implode(PHP_EOL.'- ', $this->getIDs())
            ),
            APIParameterException::ERROR_NO_VALUE_RESOLVABLE
        );
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
