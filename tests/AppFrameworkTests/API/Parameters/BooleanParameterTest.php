<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Parameters;

use Application\API\Parameters\APIParameterException;
use Application\API\Parameters\Type\BooleanParameter;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;

final class BooleanParameterTest extends APITestCase
{
    public function test_defaultIsNull() : void
    {
        $param = new BooleanParameter('foo', 'Param Label');

        $this->assertNull($param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_validStringTrueValueInRequest() : void
    {
        $this->assertParamValueIsSame(
            new BooleanParameter('foo', 'Param Label'),
            'true',
            true
        );
    }

    public function test_validStringFalseValueInRequest() : void
    {
        $this->assertParamValueIsSame(
            new BooleanParameter('foo', 'Param Label'),
            'false',
            false
        );
    }

    public function test_validStringYesValueInRequest() : void
    {
        $this->assertParamValueIsSame(
            new BooleanParameter('foo', 'Param Label'),
            'yes',
            true
        );
    }

    public function test_validStringNoValueInRequest() : void
    {
        $this->assertParamValueIsSame(
            new BooleanParameter('foo', 'Param Label'),
            'no',
            false
        );
    }

    public function test_validIntegerTrueValueInRequest() : void
    {
        $this->assertParamValueIsSame(
            new BooleanParameter('foo', 'Param Label'),
            1,
            true
        );
    }

    public function test_validIntegerFalseValueInRequest() : void
    {
        $this->assertParamValueIsSame(
            new BooleanParameter('foo', 'Param Label'),
            0,
            false
        );
    }

    public function test_invalidValueInRequest() : void
    {
        $param = new BooleanParameter('foo', 'Param Label');

        $this->assertParamValueIsSame($param, 'invalid', null);
        $this->assertResultHasInvalidValueType($param->getValidationResults());
    }

    // region: Default values

    public function test_setDefaultValidValue() : void
    {
        $param = new BooleanParameter('foo', 'Param Label');
        $param->setDefaultValue(true);

        $this->assertTrue($param->getDefaultValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_setDefaultInvalidValue() : void
    {
        $this->expectException(APIParameterException::class);
        $this->expectExceptionCode(APIParameterException::ERROR_INVALID_PARAM_VALUE);

        $param = new BooleanParameter('foo', 'Param Label');
        $param->setDefaultValue('invalid');
    }

    public function test_requestValueOverridesDefaultValue() : void
    {
        $_REQUEST['foo'] = 'false';

        $param = new BooleanParameter('foo', 'Param Label');
        $param->setDefaultValue(true);

        $this->assertFalse($param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    // endregion

    // region: Selecting values

    public function test_selectValidValue() : void
    {
        $param = new BooleanParameter('foo', 'Param Label');
        $param->selectValue(true);

        $this->assertTrue($param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_selectInvalidValue() : void
    {
        $this->expectException(APIParameterException::class);
        $this->expectExceptionCode(APIParameterException::ERROR_INVALID_PARAM_VALUE);

        $param = new BooleanParameter('foo', 'Param Label');
        $param->selectValue('invalid');
    }

    public function test_selectValueOverridesRequestAndDefaultValues() : void
    {
        $_REQUEST['foo'] = 'false';

        $param = new BooleanParameter('foo', 'Param Label');
        $param->setDefaultValue(false);
        $param->selectValue(true);

        $this->assertTrue($param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    // endregion
}
