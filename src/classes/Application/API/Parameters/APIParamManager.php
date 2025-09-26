<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters;

use Application\API\APIMethodInterface;
use Application\API\Parameters\Rules\RuleInterface;
use Application\API\Parameters\Rules\RuleTypeSelector;
use Application\API\Parameters\Validation\ParamValidationResults;
use Application\Validation\ValidationResultInterface;

/**
 * Main API parameter manager for an API method: Handles registering parameters and rules,
 * and validating them all.
 *
 * ## Using rules
 *
 * You may add as many rules as you need with {@see APIParamManager::addRule()}.
 * The rules are executed in the order they were added. They are able to modify
 * the parameters by switching their required/optional status, or invalidating them.
 *
 * @package API
 * @subpackage Parameters
 */
class APIParamManager implements ValidationResultInterface
{
    private APIMethodInterface $method;

    /**
     * @var array<string,APIParameterInterface>
     */
    private array $params = array();
    private string $validatorLabel;

    public function __construct(APIMethodInterface $method)
    {
        $this->method = $method;
        $this->validatorLabel = sprintf('API method [%s] Parameters', $this->method->getMethodName());
    }

    public function getValidatorLabel(): string
    {
        return $this->validatorLabel;
    }

    public function addParam(string $name, string $label) : ParamTypeSelector
    {
        return new ParamTypeSelector($this, $name, $label);
    }

    public function registerParam(APIParameterInterface $param) : self
    {
        $name = $param->getName();

        if (!$param instanceof ReservedParamInterface && in_array($name, APIParameterInterface::RESERVED_PARAM_NAMES, true)) {
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

    /**
     * @return APIParameterInterface[]
     */
    public function getParams() : array
    {
        ksort($this->params);

        return array_values($this->params);
    }

    /**
     * @var RuleInterface[]
     */
    private array $rules = array();
    private ?RuleTypeSelector $ruleSelector = null;

    /**
     * Returns the rule type selector to add a new validation rule.
     * @return RuleTypeSelector
     */
    public function addRule() : RuleTypeSelector
    {
        if(!isset($this->ruleSelector)) {
            $this->ruleSelector = new RuleTypeSelector($this);
        }

        return $this->ruleSelector;
    }

    public function registerRule(RuleInterface $rule) : void
    {
        $this->rules[] = $rule;
    }

    public function getValidationResults() : ParamValidationResults
    {
        // Let the rules to any pre-validation adjustments, like
        // invalidating parameters based on other parameter values.
        foreach($this->rules as $rule) {
            $rule->preValidate();
        }

        $results = new ParamValidationResults($this);

        foreach($this->rules as $rule) {
            $results->addResult($rule->getValidationResults());
        }

        foreach($this->params as $param)
        {
            // Do not collect the validation results of invalidated parameters.
            if($param->isInvalidated()) {
                continue;
            }

            $results->addResult($param->getValidationResults());
        }

        return $results;
    }

    public function getLogIdentifier(): string
    {
        return $this->validatorLabel;
    }

    /**
     * @return RuleInterface[]
     */
    public function getRules() : array
    {
        return $this->rules;
    }
}
