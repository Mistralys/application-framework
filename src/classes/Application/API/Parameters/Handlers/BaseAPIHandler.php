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
     * @return mixed
     */
    abstract protected function resolveValueFromSubject() : mixed;
}
