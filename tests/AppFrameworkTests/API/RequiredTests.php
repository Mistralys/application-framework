<?php

declare(strict_types=1);

namespace AppFrameworkTests\API;

use Application\API\Parameters\Type\StringParameter;
use Application\API\Parameters\Validation\ParamValidationInterface;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;

final class RequiredTests extends APITestCase
{
    public function test_paramNotRequiredByDefault() : void
    {
        $param = new StringParameter('foo', 'Foo Label');

        $this->assertFalse($param->isRequired());
    }

    public function test_setRequired() : void
    {
        $param = new StringParameter('foo', 'Foo Label');

        $param->makeRequired();

        $this->assertTrue($param->isRequired());
    }

    public function test_isInvalidIfEmptyAndRequired() : void
    {
        $param = new StringParameter('foo', 'Foo Label');
        $param->makeRequired();

        $this->assertNull($param->getValue());
        $this->assertResultInvalid($param->getValidationResult());
        $this->assertResultHasCode($param->getValidationResult(), ParamValidationInterface::VALIDATION_EMPTY_REQUIRED_PARAM);
    }
}
