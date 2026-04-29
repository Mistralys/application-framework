<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Parameters;

use Application\API\Parameters\APIParameterException;
use Application\API\Parameters\Type\StringListParameter;
use Application\API\Parameters\Validation\ParamValidationInterface;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;
use stdClass;

final class StringListParameterTest extends APITestCase
{
    // region: Request values

    public function test_validCommaSeparatedStringInRequest(): void
    {
        $_REQUEST['foo'] = 'alpha,beta,gamma';

        $param = new StringListParameter('foo', 'Param Label');

        $this->assertSame(array('alpha', 'beta', 'gamma'), $param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_stringValueIsWhitespaceAgnostic(): void
    {
        $_REQUEST['foo'] = '  alpha ,  beta,   gamma   ';

        $param = new StringListParameter('foo', 'Param Label');

        $this->assertSame(array('alpha', 'beta', 'gamma'), $param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_validArrayValueInRequest(): void
    {
        $_REQUEST['foo'] = array('alpha', 'beta', 'gamma');

        $param = new StringListParameter('foo', 'Param Label');

        $this->assertSame(array('alpha', 'beta', 'gamma'), $param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_emptyStringsAreFilteredFromArray(): void
    {
        $_REQUEST['foo'] = array('alpha', '', 'gamma', '   ');

        $param = new StringListParameter('foo', 'Param Label');

        $this->assertSame(array('alpha', 'gamma'), $param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_nullValueInRequest(): void
    {
        $_REQUEST['foo'] = null;

        $param = new StringListParameter('foo', 'Param Label');

        $this->assertNull($param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_emptyStringInRequest(): void
    {
        $_REQUEST['foo'] = '';

        $param = new StringListParameter('foo', 'Param Label');

        $this->assertNull($param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_allEmptyItemsAfterFilteringResolvesToNull(): void
    {
        $_REQUEST['foo'] = ',,,';

        $param = new StringListParameter('foo', 'Param Label');

        $this->assertNull($param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_invalidValueTypeInRequest(): void
    {
        $_REQUEST['foo'] = new stdClass();

        $param = new StringListParameter('foo', 'Param Label');

        $this->assertNull($param->getValue());
        $this->assertTrue($param->getValidationResults()->isValid());
        $this->assertTrue($param->getValidationResults()->containsCode(ParamValidationInterface::VALIDATION_INVALID_VALUE_TYPE));
    }

    // endregion

    // region: Default values

    public function test_setDefaultWithArrayValue(): void
    {
        $param = new StringListParameter('foo', 'Param Label');
        $param->setDefaultValue(array('alpha', 'beta', 'gamma'));

        $this->assertSame(array('alpha', 'beta', 'gamma'), $param->getDefaultValue());
    }

    public function test_setDefaultWithStringValue(): void
    {
        $param = new StringListParameter('foo', 'Param Label');
        $param->setDefaultValue('alpha,beta,gamma');

        $this->assertSame(array('alpha', 'beta', 'gamma'), $param->getDefaultValue());
    }

    public function test_setDefaultWithNullResetsToEmptyArray(): void
    {
        $param = new StringListParameter('foo', 'Param Label');
        $param->setDefaultValue('alpha,beta');
        $param->setDefaultValue(null);

        $this->assertSame(array(), $param->getDefaultValue());
    }

    public function test_setDefaultFiltersWhitespace(): void
    {
        $param = new StringListParameter('foo', 'Param Label');
        $param->setDefaultValue('  alpha ,  beta  ');

        $this->assertSame(array('alpha', 'beta'), $param->getDefaultValue());
    }

    public function test_setDefaultFiltersEmptyStrings(): void
    {
        $param = new StringListParameter('foo', 'Param Label');
        $param->setDefaultValue(array('alpha', '', '  ', 'gamma'));

        $this->assertSame(array('alpha', 'gamma'), $param->getDefaultValue());
    }

    public function test_setDefaultWithInvalidValueThrows(): void
    {
        $this->expectException(APIParameterException::class);
        $this->expectExceptionCode(APIParameterException::ERROR_INVALID_PARAM_VALUE);

        $param = new StringListParameter('foo', 'Param Label');
        $param->setDefaultValue(false);
    }

    // endregion

    // region: Selecting values

    public function test_selectWithArrayValue(): void
    {
        $param = new StringListParameter('foo', 'Param Label');
        $param->selectValue(array('alpha', 'beta', 'gamma'));

        $this->assertSame(array('alpha', 'beta', 'gamma'), $param->getValue());
    }

    public function test_selectWithStringValue(): void
    {
        $param = new StringListParameter('foo', 'Param Label');
        $param->selectValue('alpha,beta,gamma');

        $this->assertSame(array('alpha', 'beta', 'gamma'), $param->getValue());
    }

    public function test_selectWithInvalidValueThrows(): void
    {
        $this->expectException(APIParameterException::class);
        $this->expectExceptionCode(APIParameterException::ERROR_INVALID_PARAM_VALUE);

        $param = new StringListParameter('foo', 'Param Label');
        $param->selectValue(true);
    }

    public function test_selectOverridesRequestAndDefaultValue(): void
    {
        $_REQUEST['foo'] = 'one,two';

        $param = new StringListParameter('foo', 'Param Label');
        $param->setDefaultValue('x,y');
        $param->selectValue('alpha,beta,gamma');

        $this->assertSame(array('alpha', 'beta', 'gamma'), $param->getValue());
    }

    // endregion
}
