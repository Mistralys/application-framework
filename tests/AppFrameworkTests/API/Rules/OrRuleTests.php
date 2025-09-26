<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Rules;

use Application\API\Parameters\Rules\RuleInterface;
use Application\API\Parameters\Rules\Type\OrRule;
use Application\API\Parameters\Type\StringParameter;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;

final class OrRuleTests extends APITestCase
{
    public function test_validSetup() : void
    {
        $_REQUEST['paramA'] = 'valueA';
        $_REQUEST['paramB'] = 'valueB';

        $paramA = new StringParameter('paramA', 'Param A');
        $paramB = new StringParameter('paramB', 'Param B');

        $rule = new OrRule()->orParam($paramA)->orParam($paramB);

        $rule->preValidate();
        $rule->apply();

        $this->assertResultValidWithNoMessages($rule);

        $this->assertTrue($paramA->isRequired());
        $this->assertFalse($paramA->isInvalidated());
        $this->assertSame('valueA', $paramA->getValue());

        $this->assertTrue($paramB->isInvalidated());
        $this->assertFalse($paramB->isRequired());
        $this->assertNull($paramB->getValue(), 'Param B should not have a value, since it was invalidated.');
    }

    public function test_validSetupWithMultiParamSets() : void
    {
        $_REQUEST['paramA1'] = 'valueA1';
        $_REQUEST['paramA2'] = '';
        $_REQUEST['paramB'] = 'valueB';

        $paramA1 = new StringParameter('paramA1', 'Param A1');
        $paramA2 = new StringParameter('paramA2', 'Param A2');
        $paramB = new StringParameter('paramB', 'Param B');

        $rule = new OrRule()->orParams($paramA1, $paramA2)->orParam($paramB);

        $rule->preValidate();
        $rule->apply();

        $this->assertResultValidWithNoMessages($rule);
        $this->assertTrue($paramB->isRequired());
    }

    /**
     * The OR rule makes all parameters not required, since only one of them
     * needs to be provided. That one is then automatically set as required.
     */
    public function test_makeAllParamsNotRequired() : void
    {
        $paramA = new StringParameter('paramA', 'Param A')->makeRequired();
        $paramB = new StringParameter('paramB', 'Param B')->makeRequired();

        $rule = new OrRule()->orParam($paramA)->orParam($paramB);

        $rule->preValidate();

        $this->assertFalse($paramA->isRequired());
        $this->assertFalse($paramB->isRequired());
    }

    public function test_noMatchingParamSets() : void
    {
        $paramA = new StringParameter('paramA', 'Param A');
        $paramB = new StringParameter('paramB', 'Param B');

        $rule = new OrRule()->orParam($paramA)->orParam($paramB);

        $rule->preValidate();
        $rule->apply();

        $this->assertResultInvalid($rule);
        $this->assertResultHasCode($rule, RuleInterface::VALIDATION_NO_PARAM_SET_MATCHED);
    }
}
