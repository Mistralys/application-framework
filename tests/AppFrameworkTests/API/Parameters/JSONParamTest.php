<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Parameters;

use Application\API\Parameters\APIParameterException;
use Application\API\Parameters\Type\JSONParameter;
use Application\API\Parameters\Validation\ParamValidationInterface;
use AppUtils\ConvertHelper\JSONConverter;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;
use stdClass;

final class JSONParamTest extends APITestCase
{
    public function test_validValueInRequest() : void
    {
        $_REQUEST['foo'] = JSONConverter::var2json(array('foo' => 'bar'));

        $param = new JSONParameter('foo', 'Param Label');

        $this->assertSame(array('foo' => 'bar'), $param->getValue());
        $this->assertTrue($param->getValidationResults()->isValid());
    }

    public function test_invalidValueInRequest() : void
    {
        $_REQUEST['foo'] = 42;

        $param = new JSONParameter('foo', 'Param Label');

        $this->assertNull($param->getValue());
        $this->assertTrue($param->getValidationResults()->isValid());
        $this->assertTrue($param->getValidationResults()->containsCode(ParamValidationInterface::VALIDATION_INVALID_VALUE_TYPE));
    }

    public function test_emptyStringValueInRequest() : void
    {
        $_REQUEST['foo'] = '';

        $param = new JSONParameter('foo', 'Param Label');

        $this->assertNull($param->getValue());
        $this->assertTrue($param->getValidationResults()->isValid());

    }

    public function test_nullValueInRequest() : void
    {
        $_REQUEST['foo'] = null;

        $param = new JSONParameter('foo', 'Param Label');

        $this->assertNull($param->getValue());
        $this->assertTrue($param->getValidationResults()->isValid());
    }

    public function test_setDefaultValueWithValidJSON() : void
    {
        $param = new JSONParameter('foo', 'Param Label');
        $param->setDefaultValue('{"foo":"bar"}');

        $this->assertSame(array('foo' => 'bar'), $param->getDefaultValue());
    }

    public function test_setDefaultValueWithInvalidJSON() : void
    {
        $this->expectException(APIParameterException::class);
        $this->expectExceptionCode(APIParameterException::ERROR_INVALID_PARAM_VALUE);

        $param = new JSONParameter('foo', 'Param Label');
        $param->setDefaultValue('invalid json');
    }

    public function test_setDefaultWithInvalidType() : void
    {
        $this->expectException(APIParameterException::class);
        $this->expectExceptionCode(APIParameterException::ERROR_INVALID_PARAM_VALUE);

        $param = new JSONParameter('foo', 'Param Label');
        $param->setDefaultValue(false);
    }
}
