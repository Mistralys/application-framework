<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Rules;

use Application\API\Parameters\Rules\Type\RequiredIfOtherIsSetRule;
use Application\API\Parameters\Type\StringParameter;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;

final class RequiredIfOtherIsSetTest extends APITestCase
{
    public function test_makeTargetNotRequired() : void
    {
        $paramA = new StringParameter('paramA', 'Param A')->makeRequired();
        $paramB = new StringParameter('paramB', 'Param B')->makeRequired();

        $rule = new RequiredIfOtherIsSetRule('Label', $paramA, $paramB);

        $rule->preValidate();

        $this->assertFalse($paramA->isRequired(), 'Param A should not be required, since it is the target parameter. It will be set as required only if Param B is set.');
        $this->assertTrue($paramB->isRequired());
    }

    public function test_makeRequiredWhenSet() : void
    {
        $_REQUEST['paramA'] = 'valueA';
        $_REQUEST['paramB'] = 'valueB';

        $paramA = new StringParameter('paramA', 'Param A');
        $paramB = new StringParameter('paramB', 'Param B');

        $rule = new RequiredIfOtherIsSetRule('Label', $paramA, $paramB);

        $rule->preValidate();
        $rule->apply();

        $this->assertTrue($paramA->isRequired(), 'Param A should be required, since Param B is set.');
        $this->assertFalse($paramB->isRequired(), 'No changes to Param B, since it is the condition parameter.');
    }

    public function test_makeNotRequiredWhenNotSet() : void
    {
        $_REQUEST['paramA'] = 'valueA';

        $paramA = new StringParameter('paramA', 'Param A')->makeRequired();
        $paramB = new StringParameter('paramB', 'Param B')->makeRequired();

        $rule = new RequiredIfOtherIsSetRule('Label', $paramA, $paramB);

        $rule->preValidate();
        $rule->apply();

        $this->assertFalse($paramA->isRequired(), 'Param A should not be be required, since Param B is not set.');
        $this->assertTrue($paramB->isRequired(), 'Param B can be set as required independently of param A.');
    }
}
