<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Parameters;

use Application\API\Parameters\APIParameterException;
use Application\API\Parameters\Type\IDListParameter;
use Application\API\Parameters\Validation\ParamValidationInterface;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;
use stdClass;

final class IDListParameterTest extends APITestCase
{
    public function test_validStringValueInRequest(): void
    {
        $_REQUEST['foo'] = '42,55,14789';

        $param = new IDListParameter('foo', 'Param Label');

        $this->assertSame(array(42, 55, 14789), $param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_stringValueIsWhitespaceAgnostic(): void
    {
        $_REQUEST['foo'] = '  42 ,  55,   14789   ';

        $param = new IDListParameter('foo', 'Param Label');

        $this->assertSame(array(42, 55, 14789), $param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_validArrayValueInRequest() : void
    {
        $_REQUEST['foo'] = array(42, 55, 14789);

        $param = new IDListParameter('foo', 'Param Label');

        $this->assertSame(array(42, 55, 14789), $param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_invalidIDValueInRequest() : void
    {
        $_REQUEST['foo'] = '42,invalid,14789';

        $param = new IDListParameter('foo', 'Param Label');

        $this->assertSame(array(42, 14789), $param->getValue());
        $this->assertResultValid($param->getValidationResults());
        $this->assertResultHasCode($param->getValidationResults(), ParamValidationInterface::VALIDATION_NON_NUMERIC_ID);
    }

    public function test_numericValueIsConvertedToString() : void
    {
        $_REQUEST['foo'] = 42;

        $param = new IDListParameter('foo', 'Param Label');

        $this->assertSame(array(42), $param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_emptyStringInRequest() : void
    {
        $_REQUEST['foo'] = '';

        $param = new IDListParameter('foo', 'Param Label');

        $this->assertNull($param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_nullValueInRequest() : void
    {
        $_REQUEST['foo'] = null;

        $param = new IDListParameter('foo', 'Param Label');

        $this->assertNull($param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_invalidValueTypeInRequest() : void
    {
        $_REQUEST['foo'] = new stdClass();

        $param = new IDListParameter('foo', 'Param Label');

        $this->assertNull($param->getValue());
        $this->assertTrue($param->getValidationResults()->isValid());
        $this->assertTrue($param->getValidationResults()->containsCode(ParamValidationInterface::VALIDATION_INVALID_VALUE_TYPE));
    }

    // region: Default values

    public function test_setDefaultWithArrayValue() : void
    {
        $param = new IDListParameter('foo', 'Param Label');
        $param->setDefaultValue(array(42, 55, 14789));

        $this->assertSame(array(42, 55, 14789), $param->getDefaultValue());
    }

    public function test_setDefaultWithStringValue() : void
    {
        $param = new IDListParameter('foo', 'Param Label');
        $param->setDefaultValue('42,55,14789');

        $this->assertSame(array(42, 55, 14789), $param->getDefaultValue());
    }

    public function test_setDefaultWithInvalidValue() : void
    {
        $this->expectException(APIParameterException::class);
        $this->expectExceptionCode(APIParameterException::ERROR_INVALID_PARAM_VALUE);

        $param = new IDListParameter('foo', 'Param Label');
        $param->setDefaultValue(false);
    }

    // endregion

    // region: Selecting values

    public function test_selectWithArrayValue() : void
    {
        $param = new IDListParameter('foo', 'Param Label');
        $param->selectValue(array(42, 55, 14789));

        $this->assertSame(array(42, 55, 14789), $param->getValue());
    }

    public function test_selectWithStringValue() : void
    {
        $param = new IDListParameter('foo', 'Param Label');
        $param->selectValue('42,55,14789');

        $this->assertSame(array(42, 55, 14789), $param->getValue());
    }

    public function test_selectWithInvalidValue() : void
    {
        $this->expectException(APIParameterException::class);
        $this->expectExceptionCode(APIParameterException::ERROR_INVALID_PARAM_VALUE);

        $param = new IDListParameter('foo', 'Param Label');
        $param->selectValue(true);
    }

    public function test_selectOverridesRequestAndDefaultValue() : void
    {
        $_REQUEST['foo'] = '20,30';

        $param = new IDListParameter('foo', 'Param Label');
        $param->setDefaultValue('10,15');
        $param->selectValue('42,55,14789');

        $this->assertSame(array(42, 55, 14789), $param->getValue());
    }

    // endregion
}
