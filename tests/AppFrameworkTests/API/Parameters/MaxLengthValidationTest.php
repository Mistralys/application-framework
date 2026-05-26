<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Parameters;

use Application\API\Parameters\Type\StringParameter;
use Application\API\Parameters\Validation\ParamValidationInterface;
use Application\API\Parameters\Validation\Type\MaxLengthValidation;
use AppUtils\OperationResult;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;

final class MaxLengthValidationTest extends APITestCase
{
    // region: Standalone MaxLengthValidation tests

    private function createResult() : OperationResult
    {
        return new OperationResult($this);
    }

    private function createParam(string $name='test') : StringParameter
    {
        return new StringParameter($name, 'Test Parameter');
    }

    public function test_exactBoundaryPasses() : void
    {
        $validation = new MaxLengthValidation(5);
        $result = $this->createResult();

        $validation->validate('hello', $result, $this->createParam());

        $this->assertResultValidWithNoMessages($result);
    }

    public function test_overBoundaryFails() : void
    {
        $validation = new MaxLengthValidation(5);
        $result = $this->createResult();

        $validation->validate('toolong', $result, $this->createParam());

        $this->assertResultInvalid($result);
        $this->assertResultHasCode($result, ParamValidationInterface::VALIDATION_MAX_LENGTH_EXCEEDED);
    }

    public function test_underBoundaryPasses() : void
    {
        $validation = new MaxLengthValidation(10);
        $result = $this->createResult();

        $validation->validate('short', $result, $this->createParam());

        $this->assertResultValidWithNoMessages($result);
    }

    public function test_nullValueIsSkipped() : void
    {
        $validation = new MaxLengthValidation(5);
        $result = $this->createResult();

        $validation->validate(null, $result, $this->createParam());

        $this->assertResultValidWithNoMessages($result);
    }

    public function test_emptyStringIsSkipped() : void
    {
        $validation = new MaxLengthValidation(5);
        $result = $this->createResult();

        $validation->validate('', $result, $this->createParam());

        $this->assertResultValidWithNoMessages($result);
    }

    public function test_nonStringValueIsSkipped() : void
    {
        $validation = new MaxLengthValidation(2);
        $result = $this->createResult();

        // Integer value — not a string, should be skipped
        $validation->validate(42, $result, $this->createParam());

        $this->assertResultValidWithNoMessages($result);
    }

    public function test_multibyteSafeLength() : void
    {
        // Each character is one codepoint (3 bytes in UTF-8)
        $threeChars = 'äöü'; // 3 multibyte chars
        $validation = new MaxLengthValidation(3);
        $result = $this->createResult();

        $validation->validate($threeChars, $result, $this->createParam());

        $this->assertResultValidWithNoMessages($result);
    }

    public function test_multibyteSafeLengthExceeded() : void
    {
        $fourChars = 'äöüé'; // 4 multibyte chars
        $validation = new MaxLengthValidation(3);
        $result = $this->createResult();

        $validation->validate($fourChars, $result, $this->createParam());

        $this->assertResultInvalid($result);
        $this->assertResultHasCode($result, ParamValidationInterface::VALIDATION_MAX_LENGTH_EXCEEDED);
    }

    // endregion

    // region: StringParameter::setMaxLength() integration tests

    public function test_setMaxLength_valueBelowLimit() : void
    {
        $_REQUEST['foo'] = 'hi';

        $param = new StringParameter('foo', 'Param Label');
        $param->setMaxLength(5);

        $this->assertSame('hi', $param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_setMaxLength_valueAtExactLimit() : void
    {
        $_REQUEST['foo'] = 'hello';

        $param = new StringParameter('foo', 'Param Label');
        $param->setMaxLength(5);

        $this->assertSame('hello', $param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_setMaxLength_valueExceedsLimit() : void
    {
        $_REQUEST['foo'] = 'toolong';

        $param = new StringParameter('foo', 'Param Label');
        $param->setMaxLength(5);

        $this->assertNull($param->getValue());
        $this->assertResultInvalid($param->getValidationResults());
        $this->assertResultHasCode($param->getValidationResults(), ParamValidationInterface::VALIDATION_MAX_LENGTH_EXCEEDED);
    }

    public function test_setMaxLength_returnsThisForFluentChaining() : void
    {
        $param = new StringParameter('foo', 'Param Label');
        $result = $param->setMaxLength(10);

        $this->assertSame($param, $result);
    }

    public function test_setMaxLength_nullValueInRequest() : void
    {
        $_REQUEST['foo'] = null;

        $param = new StringParameter('foo', 'Param Label');
        $param->setMaxLength(5);

        $this->assertNull($param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_setMaxLength_emptyStringInRequest() : void
    {
        $_REQUEST['foo'] = '';

        $param = new StringParameter('foo', 'Param Label');
        $param->setMaxLength(5);

        $this->assertNull($param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    // endregion
}
