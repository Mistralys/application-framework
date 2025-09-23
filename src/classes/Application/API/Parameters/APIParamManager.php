<?php

declare(strict_types=1);

namespace Application\API\Parameters;

use Application\API\APIMethodInterface;
use Application\API\Parameters\Validation\ParamValidationResults;

class APIParamManager
{
    private APIMethodInterface $method;

    /**
     * @var array<string,APIParameterInterface>
     */
    private array $params = array();

    public function __construct(APIMethodInterface $method)
    {
        $this->method = $method;
    }

    public function add(string $name, string $label) : ParamTypeSelector
    {
        return new ParamTypeSelector($this, $name, $label);
    }

    public function registerParam(APIParameterInterface $param) : self
    {
        $name = $param->getName();

        if (in_array($name, APIParameterInterface::RESERVED_PARAM_NAMES, true)) {
            throw new APIParameterException(
                'Tried registering a reserved parameter',
                sprintf(
                    'The parameter [%1$s] is a reserved parameter, the API method [%2$s] may not register it for itself.',
                    $name,
                    $this->method->getMethodName()
                ),
                APIParameterException::ERROR_RESERVED_PARAM_NAME
            );
        }

        if(!isset($this->params[$name])) {
            $this->params[$name] = $param;
            return $this;
        }

        throw new APIParameterException(
            'Parameter has already been registered.',
            sprintf(
                'A parameter with the name [%s] has already been registered in the API method [%s].',
                $name,
                $this->method->getMethodName()
            ),
            APIParameterException::ERROR_PARAM_ALREADY_REGISTERED
        );
    }

    public function getParams() : array
    {
        ksort($this->params);

        return array_values($this->params);
    }

    public function validateAll() : ParamValidationResults
    {
        $results = new ParamValidationResults();

        foreach($this->params as $param) {
            $results->addResult($param->getValidationResult());
        }

        return $results;
    }
}

