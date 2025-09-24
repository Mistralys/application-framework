<?php

declare(strict_types=1);

namespace AppFrameworkTests\Application;

use Application\API\Parameters\APIParameterException;
use Application\API\Parameters\Type\BooleanParameter;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;

final class BooleanParameterTests extends APITestCase
{
    public function test_validStringTrueValueInRequest() : void
    {
        $_REQUEST['foo'] = 'true';

        $param = new BooleanParameter('foo', 'Foo Label');

        $this->assertTrue($param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResult());
    }

    public function test_validStringFalseValueInRequest() : void
    {
        $_REQUEST['foo'] = 'false';

        $param = new BooleanParameter('foo', 'Foo Label');

        $this->assertFalse($param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResult());
    }

    public function test_validStringYesValueInRequest() : void
    {
        $_REQUEST['foo'] = 'yes';

        $param = new BooleanParameter('foo', 'Foo Label');

        $this->assertTrue($param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResult());
    }

    public function test_validStringNoValueInRequest() : void
    {
        $_REQUEST['foo'] = 'no';

        $param = new BooleanParameter('foo', 'Foo Label');

        $this->assertFalse($param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResult());
    }

    public function test_validIntegerTrueValueInRequest() : void
    {
        $_REQUEST['foo'] = 1;

        $param = new BooleanParameter('foo', 'Foo Label');

        $this->assertTrue($param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResult());
    }

    public function test_validIntegerFalseValueInRequest() : void
    {
        $_REQUEST['foo'] = 0;

        $param = new BooleanParameter('foo', 'Foo Label');

        $this->assertFalse($param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResult());
    }

    public function test_invalidValueInRequest() : void
    {
        $_REQUEST['foo'] = 'invalid';

        $param = new BooleanParameter('foo', 'Foo Label');

        $this->assertNull($param->getValue());
        $this->assertResultValid($param->getValidationResult());
        $this->assertResultHasInvalidValueType($param->getValidationResult());
    }

    public function test_setDefaultValidValue() : void
    {
        $param = new BooleanParameter('foo', 'Foo Label');
        $param->setDefaultValue(true);

        $this->assertTrue($param->getDefaultValue());
        $this->assertResultValidWithNoMessages($param->getValidationResult());
    }

    public function test_setDefaultInvalidValue() : void
    {
        $this->expectException(APIParameterException::class);
        $this->expectExceptionCode(APIParameterException::ERROR_INVALID_DEFAULT_VALUE);

        $param = new BooleanParameter('foo', 'Foo Label');
        $param->setDefaultValue('invalid');
    }
}
