<?php

declare(strict_types=1);

namespace AppFrameworkTests\Application;

use Application\API\Parameters\Type\IDListParameter;
use Application\API\Parameters\Validation\ParamValidationInterface;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;

final class IDListParameterTests extends APITestCase
{
    public function test_validStringValueInRequest(): void
    {
        $_REQUEST['foo'] = '42,55,14789';

        $param = new IDListParameter('foo', 'Foo Label');

        $this->assertSame(array(42, 55, 14789), $param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResult());
    }

    public function test_stringValueIsWhitespaceAgnostic(): void
    {
        $_REQUEST['foo'] = '  42 ,  55,   14789   ';

        $param = new IDListParameter('foo', 'Foo Label');

        $this->assertSame(array(42, 55, 14789), $param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResult());
    }

    public function test_validArrayValueInRequest() : void
    {
        $_REQUEST['foo'] = array(42, 55, 14789);

        $param = new IDListParameter('foo', 'Foo Label');

        $this->assertSame(array(42, 55, 14789), $param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResult());
    }

    public function test_invalidIDValueInRequest() : void
    {
        $_REQUEST['foo'] = '42,invalid,14789';

        $param = new IDListParameter('foo', 'Foo Label');

        $this->assertSame(array(42, 14789), $param->getValue());
        $this->assertResultValid($param->getValidationResult());
        $this->assertResultHasCode($param->getValidationResult(), ParamValidationInterface::VALIDATION_NON_NUMERIC_ID);
    }

    public function test_numericValueIsConvertedToString() : void
    {
        $_REQUEST['foo'] = 42;

        $param = new IDListParameter('foo', 'Foo Label');

        $this->assertSame(array(42), $param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResult());
    }

    public function test_emptyStringInRequest() : void
    {
        $_REQUEST['foo'] = '';

        $param = new IDListParameter('foo', 'Foo Label');

        $this->assertNull($param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResult());
    }

    public function test_nullValueInRequest() : void
    {
        $_REQUEST['foo'] = null;

        $param = new IDListParameter('foo', 'Foo Label');

        $this->assertNull($param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResult());
    }

    public function test_invalidValueTypeInRequest() : void
    {
        $_REQUEST['foo'] = new \stdClass();

        $param = new IDListParameter('foo', 'Foo Label');

        $this->assertNull($param->getValue());
        $this->assertTrue($param->getValidationResult()->isValid());
        $this->assertTrue($param->getValidationResult()->containsCode(ParamValidationInterface::VALIDATION_INVALID_VALUE_TYPE));
    }
}
