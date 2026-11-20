<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\Rules;

use Application\API\Parameters\APIParameterInterface;
use Application\API\Parameters\APIParamManager;
use Application\API\Parameters\Rules\Type\OnlyIfOtherValueInRule;
use Application\API\Parameters\Rules\Type\OrRule;
use Application\API\Parameters\Rules\Type\RequiredIfOtherIsSetRule;
use Application\API\Parameters\Rules\Type\RequiredIfOtherValueEquals;

/**
 * Utility selector class for different types of validation rules
 * to add to an API method's parameters.
 *
 * @package API
 * @subpackage Parameters
 */
class RuleTypeSelector
{
    private APIParamManager $manager;

    public function __construct(APIParamManager $manager)
    {
        $this->manager = $manager;
    }

    public function or(string $label) : OrRule
    {
        $rule = new OrRule($label);

        $this->manager->registerRule($rule);

        return $rule;
    }

    public function requiredIfOtherIsSet(string $label, APIParameterInterface $target, APIParameterInterface $other) : RequiredIfOtherIsSetRule
    {
        $rule = new RequiredIfOtherIsSetRule($label, $target, $other);

        $this->manager->registerRule($rule);

        return $rule;
    }

    public function requiredIfOtherValueEquals(string $label, APIParameterInterface $target, APIParameterInterface $other, mixed $expectedValue) : RequiredIfOtherValueEquals
    {
        $rule = new RequiredIfOtherValueEquals($label, $target, $other, $expectedValue);

        $this->manager->registerRule($rule);

        return $rule;
    }

    /**
     * A target parameter is only accepted when another parameter's value
     * is one of a given set of allowed values; otherwise it is rejected
     * with {@see RuleInterface::VALIDATION_PARAM_NOT_APPLICABLE}.
     *
     * @param string $label
     * @param APIParameterInterface $target The parameter that is only accepted conditionally.
     * @param APIParameterInterface $other The parameter whose value decides whether the target is accepted.
     * @param array<int,int|float|string|bool> $allowedValues The values of `$other` for which `$target` is accepted.
     * @return OnlyIfOtherValueInRule
     */
    public function onlyIfOtherValueIn(string $label, APIParameterInterface $target, APIParameterInterface $other, array $allowedValues) : OnlyIfOtherValueInRule
    {
        $rule = new OnlyIfOtherValueInRule($label, $target, $other, $allowedValues);

        $this->manager->registerRule($rule);

        return $rule;
    }
}
