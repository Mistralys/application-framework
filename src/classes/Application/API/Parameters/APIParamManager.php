<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters;

use Application\API\APIException;
use Application\API\APIMethodInterface;
use Application\API\Parameters\Flavors\APIHeaderParameterInterface;
use Application\API\Parameters\Removed\RemovedParameter;
use Application\API\Parameters\Removed\RemovedParamCollection;
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

    public function paramExists(string $name) : bool
    {
        return isset($this->params[$name]);
    }

    public function registerParam(APIParameterInterface $param) : self
    {
        $this->requireNotValidated();

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

        if(isset($this->removedParams) && $this->removedParams->has($name)) {
            throw new APIParameterException(
                'Tried registering a parameter that has been declared removed.',
                sprintf(
                    'The parameter [%1$s] has been declared removed in the API method [%2$s], and may not also be registered as an active parameter.',
                    $name,
                    $this->method->getMethodName()
                ),
                APIParameterException::ERROR_REMOVED_PARAM_IS_REGISTERED
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
     * @var RemovedParamCollection|NULL
     */
    private ?RemovedParamCollection $removedParams = null;

    public function getRemovedParams() : RemovedParamCollection
    {
        if(!isset($this->removedParams)) {
            $this->removedParams = new RemovedParamCollection();
        }

        return $this->removedParams;
    }

    /**
     * Declares a parameter name that used to be accepted by this API
     * method, but has since been removed: Requests still sending it
     * can be detected via {@see self::getRemovedParams()} and rejected
     * with a helpful error message, instead of the method having to
     * hand-write a guard for it.
     *
     * @param string $name
     * @return RemovedParameter
     * @throws APIParameterException {@see APIParameterException::ERROR_REMOVED_PARAM_RESERVED_NAME}
     *     {@see APIParameterException::ERROR_REMOVED_PARAM_IS_REGISTERED}
     *     {@see APIParameterException::ERROR_REMOVED_PARAM_ALREADY_DECLARED}
     *     {@see APIParameterException::ERROR_INVALID_REMOVED_PARAM_CONFIG}
     * @throws APIException {@see APIException::ERROR_CANNOT_MODIFY_AFTER_VALIDATION}
     */
    public function addRemovedParam(string $name) : RemovedParameter
    {
        $this->requireNotValidated();

        if(in_array($name, APIParameterInterface::RESERVED_PARAM_NAMES, true)) {
            throw new APIParameterException(
                'Tried declaring a reserved parameter as removed.',
                sprintf(
                    'The parameter [%1$s] is a reserved parameter, the API method [%2$s] may not declare it removed.',
                    $name,
                    $this->method->getMethodName()
                ),
                APIParameterException::ERROR_REMOVED_PARAM_RESERVED_NAME
            );
        }

        if(isset($this->params[$name])) {
            throw new APIParameterException(
                'Tried declaring a parameter as removed that is already registered as an active parameter.',
                sprintf(
                    'The parameter [%1$s] has already been registered as an active parameter in the API method [%2$s], and may not also be declared removed.',
                    $name,
                    $this->method->getMethodName()
                ),
                APIParameterException::ERROR_REMOVED_PARAM_IS_REGISTERED
            );
        }

        if($this->getRemovedParams()->has($name)) {
            throw new APIParameterException(
                'Parameter has already been declared removed.',
                sprintf(
                    'The parameter [%1$s] has already been declared removed in the API method [%2$s].',
                    $name,
                    $this->method->getMethodName()
                ),
                APIParameterException::ERROR_REMOVED_PARAM_ALREADY_DECLARED
            );
        }

        $param = new RemovedParameter($name);

        $this->getRemovedParams()->add($param);

        return $param;
    }

    private function requireNotValidated() : void
    {
        if(!isset($this->validationResults)) {
            return;
        }

        throw new APIException(
            'Cannot modify API parameters and rules after validation.',
            sprintf(
                'Attempted to modify the API method [%s] after validation has already been performed. '.
                'All parameters and rules must be added before they are validated.',
                $this->method->getMethodName()
            ),
            APIException::ERROR_CANNOT_MODIFY_AFTER_VALIDATION
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
     * @return APIHeaderParameterInterface[]
     */
    public function getHeaderParams() : array
    {
        $headerParams = array();

        foreach($this->params as $param) {
            if($param instanceof APIHeaderParameterInterface) {
                $headerParams[] = $param;
            }
        }

        return $headerParams;
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
        if(!isset($this->validationResults)) {
            $this->rules[] = $rule;
            return;
        }

        $this->requireNotValidated();
    }

    private ?ParamValidationResults $validationResults = null;

    public function getValidationResults() : ParamValidationResults
    {
        if(isset($this->validationResults)) {
            return $this->validationResults;
        }

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

        $this->validationResults = $results;

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
