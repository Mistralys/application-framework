<?php

declare(strict_types=1);

namespace AppFrameworkTests\API;

use Application\API\Parameters\APIParameterException;
use Application\API\Parameters\Type\IntegerParameter;
use Application\API\Parameters\Validation\ParamValidationInterface;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;

final class IntegerParameterTests extends APITestCase
{
    public function test_validValueInRequest() : void
    {
        $_REQUEST['foo'] = '42';

        $param = new IntegerParameter('foo', 'Foo Label');

        $this->assertSame(42, $param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResult());
    }

    public function test_convertNumericValueInRequest() : void
    {
        $_REQUEST['foo'] = 42;

        $param = new IntegerParameter('foo', 'Foo Label');

        $this->assertSame(42, $param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResult());
    }

    public function test_invalidValueInRequest() : void
    {
        $_REQUEST['foo'] = 'invalid';

        $param = new IntegerParameter('foo', 'Foo Label');

        $this->assertNull($param->getValue());
        $this->assertResultValid($param->getValidationResult());
        $this->assertResultHasInvalidValueType($param->getValidationResult());
    }

    public function test_emptyStringInRequest() : void
    {
        $_REQUEST['foo'] = '';

        $param = new IntegerParameter('foo', 'Foo Label');

        $this->assertNull($param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResult());
    }

    public function test_nullValueInRequest() : void
    {
        $_REQUEST['foo'] = null;

        $param = new IntegerParameter('foo', 'Foo Label');

        $this->assertNull($param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResult());
    }

    public function test_floatValueInRequest() : void
    {
        $_REQUEST['foo'] = 4.2;

        $param = new IntegerParameter('foo', 'Foo Label');

        $this->assertSame(4, $param->getValue());
        $this->assertResultValid($param->getValidationResult());
        $this->assertResultHasCode($param->getValidationResult(), ParamValidationInterface::VALIDATION_WARNING_FLOAT_TO_INT);
    }

    public function test_setDefaultValueWithValidInteger() : void
    {
        $param = new IntegerParameter('foo', 'Foo Label');
        $param->setDefaultValue(42);

        $this->assertSame(42, $param->getDefaultValue());
        $this->assertResultValidWithNoMessages($param->getValidationResult());
    }

    public function test_setDefaultValueWithInvalidString() : void
    {
        $this->expectException(APIParameterException::class);
        $this->expectExceptionCode(APIParameterException::ERROR_INVALID_DEFAULT_VALUE);

        $param = new IntegerParameter('foo', 'Foo Label');
        $param->setDefaultValue('invalid string');
    }
}
