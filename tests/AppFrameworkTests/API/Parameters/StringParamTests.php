<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Parameters;

use Application\API\Parameters\APIParameterException;
use Application\API\Parameters\Type\StringParameter;
use Application\API\Parameters\Validation\ParamValidationInterface;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;

final class StringParamTests extends APITestCase
{
    public function test_validValueInRequest() : void
    {
        $_REQUEST['foo'] = 'bar';

        $param = new StringParameter('foo', 'Foo Label');

        $this->assertSame('bar', $param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_convertNumericValueInRequest() : void
    {
        $_REQUEST['foo'] = 42;

        $param = new StringParameter('foo', 'Foo Label');

        $this->assertSame('42', $param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_invalidValueInRequest() : void
    {
        $_REQUEST['foo'] = array();

        $param = new StringParameter('foo', 'Foo Label');

        $this->assertNull($param->getValue());
        $this->assertResultValid($param->getValidationResults());
        $this->assertResultHasInvalidValueType($param->getValidationResults());
    }

    public function test_emptyStringInRequest() : void
    {
        $_REQUEST['foo'] = '';

        $param = new StringParameter('foo', 'Foo Label');

        $this->assertNull($param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_nullValueInRequest() : void
    {
        $_REQUEST['foo'] = null;

        $param = new StringParameter('foo', 'Foo Label');

        $this->assertNull($param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_setDefaultValueWithValidString() : void
    {
        $param = new StringParameter('foo', 'Foo Label');
        $param->setDefaultValue('default string');

        $this->assertSame('default string', $param->getDefaultValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_setDefaultValueWithInvalidType() : void
    {
        $this->expectException(APIParameterException::class);
        $this->expectExceptionCode(APIParameterException::ERROR_INVALID_DEFAULT_VALUE);

        $param = new StringParameter('foo', 'Foo Label');
        $param->setDefaultValue(123);
    }

    public function test_regexValidationValid() : void
    {
        $_REQUEST['foo'] = 'bar123';

        $param = new StringParameter('foo', 'Foo Label');
        $param->validateByRegex('/^bar[0-9]+$/');

        $this->assertSame('bar123', $param->getValue());
    }

    public function test_regexValidationInvalid() : void
    {
        $_REQUEST['foo'] = 'baz123';
        $param = new StringParameter('foo', 'Foo Label');
        $param->validateByRegex('/^bar[0-9]+$/');

        $this->assertNull($param->getValue());
        $this->assertResultInvalid($param->getValidationResults());
        $this->assertResultHasCode($param->getValidationResults(), ParamValidationInterface::VALIDATION_INVALID_FORMAT_BY_REGEX);
    }

    public function test_defaultWithValidString() : void
    {
        $param = new StringParameter('foo', 'Foo Label');
        $param->setDefaultValue('default string');

        $this->assertSame('default string', $param->getValue());
    }

    public function test_defaultWithInvalidType() : void
    {
        $this->expectException(APIParameterException::class);
        $this->expectExceptionCode(APIParameterException::ERROR_INVALID_DEFAULT_VALUE);

        $param = new StringParameter('foo', 'Foo Label');
        $param->setDefaultValue(array());
    }
}
