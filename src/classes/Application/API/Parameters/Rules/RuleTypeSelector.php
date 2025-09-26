<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\Rules;

use Application\API\Parameters\APIParameterInterface;
use Application\API\Parameters\APIParamManager;
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

    public function or() : OrRule
    {
        $rule = new OrRule();

        $this->manager->registerRule($rule);

        return $rule;
    }

    public function requiredIfOtherIsSet(APIParameterInterface $target, APIParameterInterface $other) : RequiredIfOtherIsSetRule
    {
        $rule = new RequiredIfOtherIsSetRule($target, $other);

        $this->manager->registerRule($rule);

        return $rule;
    }

    public function requiredIfOtherValueEquals(APIParameterInterface $target, APIParameterInterface $other, mixed $expectedValue) : RequiredIfOtherValueEquals
    {
        $rule = new RequiredIfOtherValueEquals($target, $other, $expectedValue);

        $this->manager->registerRule($rule);

        return $rule;
    }
}
