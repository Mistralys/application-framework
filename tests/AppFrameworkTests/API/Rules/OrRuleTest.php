<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Rules;

use Application\API\Parameters\ParamSet;
use Application\API\Parameters\Rules\RuleInterface;
use Application\API\Parameters\Rules\Type\OrRule;
use Application\API\Parameters\Type\StringParameter;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;

final class OrRuleTest extends APITestCase
{
    public function test_validSetup() : void
    {
        $_REQUEST['paramA'] = 'valueA';
        $_REQUEST['paramB'] = 'valueB';

        $paramA = new StringParameter('paramA', 'Param A');
        $paramB = new StringParameter('paramB', 'Param B');

        $rule = new OrRule('Rule label')
            ->addSet(new ParamSet('a', $paramA))
            ->addSet(new ParamSet('b', $paramB));

        $rule->preValidate();
        $rule->apply();

        $this->assertResultValidWithNoMessages($rule);

        $this->assertTrue($paramA->isRequired());
        $this->assertFalse($paramA->isInvalidated());
        $this->assertSame('valueA', $paramA->getValue());

        $this->assertTrue($paramB->isInvalidated());
        $this->assertFalse($paramB->isRequired());
        $this->assertNull($paramB->getValue(), 'Param B should not have a value, since it was invalidated.');

        $set = $rule->getValidSet();
        $this->assertNotNull($set);
        $this->assertSame('a', $set->getID());
    }

    public function test_validSetupWithMultiParamSets() : void
    {
        $_REQUEST['paramA1'] = 'valueA1';
        $_REQUEST['paramA2'] = '';
        $_REQUEST['paramB'] = 'valueB';

        $paramA1 = new StringParameter('paramA1', 'Param A1');
        $paramA2 = new StringParameter('paramA2', 'Param A2');
        $paramB = new StringParameter('paramB', 'Param B');

        $rule = new OrRule('Rule label')
            ->addSet(new ParamSet('a', $paramA1, $paramA2))
            ->addSet(new ParamSet('b', $paramB));

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

        $rule = new OrRule('Rule label')
            ->addSet(new ParamSet('a', $paramA))
            ->addSet(new ParamSet('b', $paramB));

        $rule->preValidate();

        $this->assertFalse($paramA->isRequired());
        $this->assertFalse($paramB->isRequired());
    }

    /**
     * When several parameter sets contain the same parameter,
     * and one of those sets is invalid, the parameter should
     * not be invalidated if it is part of another valid set.
     */
    public function test_duplicateParamsDoNotGetInvalidated() : void
    {
        // We only want B to be relevant
        $_REQUEST['paramB'] = 'valueB';

        $paramA = new StringParameter('paramA', 'Param A');
        $paramB = new StringParameter('paramB', 'Param B');

        $rule = new OrRule('Rule label')
            ->addSet(new ParamSet('a', $paramA, $paramB))
            ->addSet(new ParamSet('b', $paramB));

        $rule->preValidate();

        $this->assertResultValidWithNoMessages($rule);

        $this->assertTrue($paramA->isInvalidated());
        $this->assertNull($paramA->getValue());

        $this->assertFalse($paramB->isInvalidated());
        $this->assertTrue($paramB->isRequired());
        $this->assertSame('valueB', $paramB->getValue());

        $set = $rule->getValidSet();
        $this->assertNotNull($set);
        $this->assertSame('b', $set->getID());
    }

    public function test_noMatchingParamSets() : void
    {
        $paramA = new StringParameter('paramA', 'Param A');
        $paramB = new StringParameter('paramB', 'Param B');

        $rule = new OrRule('Rule label')
            ->addSet(new ParamSet('a', $paramA))
            ->addSet(new ParamSet('b', $paramB));

        $rule->preValidate();
        $rule->apply();

        $this->assertResultInvalid($rule);
        $this->assertResultHasCode($rule, RuleInterface::VALIDATION_NO_PARAM_SET_MATCHED);
    }
}
