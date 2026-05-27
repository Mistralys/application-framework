<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Parameters;

use Application\API\Parameters\Type\ClearableStringParameter;
use Application\API\Parameters\Validation\ParamValidationInterface;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;

/**
 * Tests for {@see ClearableStringParameter}.
 *
 * Covers all three resolution states (absent, empty/whitespace, value),
 * edge cases (numeric values, non-string values), hasValue() behaviour,
 * and setMaxLength() integration.
 */
final class ClearableStringParameterTest extends APITestCase
{
    // region: Resolution states

    public function test_absentKey_returnsNull() : void
    {
        // Key is not in $_REQUEST at all
        $param = new ClearableStringParameter('foo', 'Param Label');

        $this->assertNull($param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_emptyStringPresent_returnsEmptyString() : void
    {
        $_REQUEST['foo'] = '';

        $param = new ClearableStringParameter('foo', 'Param Label');

        $this->assertSame('', $param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_whitespaceOnlyPresent_returnsEmptyString() : void
    {
        $_REQUEST['foo'] = '   ';

        $param = new ClearableStringParameter('foo', 'Param Label');

        $this->assertSame('', $param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_validString_returnsTrimmedString() : void
    {
        $_REQUEST['foo'] = 'hello';

        $param = new ClearableStringParameter('foo', 'Param Label');

        $this->assertSame('hello', $param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_validStringWithLeadingTrailingWhitespace_returnsTrimmedString() : void
    {
        $_REQUEST['foo'] = '  hello world  ';

        $param = new ClearableStringParameter('foo', 'Param Label');

        $this->assertSame('hello world', $param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    // endregion

    // region: Numeric values

    public function test_integerValue_returnsStringRepresentation() : void
    {
        $_REQUEST['foo'] = 42;

        $param = new ClearableStringParameter('foo', 'Param Label');

        $this->assertSame('42', $param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_floatValue_returnsStringRepresentation() : void
    {
        $_REQUEST['foo'] = 3.14;

        $param = new ClearableStringParameter('foo', 'Param Label');

        $this->assertSame('3.14', $param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_numericStringValue_returnsString() : void
    {
        $_REQUEST['foo'] = '123';

        $param = new ClearableStringParameter('foo', 'Param Label');

        $this->assertSame('123', $param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    // endregion

    // region: Non-string values

    public function test_arrayValue_returnsNullWithWarning() : void
    {
        $_REQUEST['foo'] = array('not', 'a', 'string');

        $param = new ClearableStringParameter('foo', 'Param Label');

        $this->assertNull($param->getValue());
        // Validation is still valid (warning, not error), but has the invalid-value-type message
        $this->assertResultHasInvalidValueType($param->getValidationResults());
    }

    public function test_nullValue_distinguishedFromAbsent_returnsNull() : void
    {
        // Key is present in $_REQUEST but value is null.
        // array_key_exists returns true for null values, so this is treated
        // as "present with non-string value" → null with warning.
        $_REQUEST['foo'] = null;

        $param = new ClearableStringParameter('foo', 'Param Label');

        // null is not numeric and not a string, so it produces a warning.
        // The result should still be marked with invalid value type.
        $this->assertNull($param->getValue());
        $this->assertResultHasInvalidValueType($param->getValidationResults());
    }

    // endregion

    // region: hasValue() behaviour

    public function test_hasValue_absentKey_returnsFalse() : void
    {
        $param = new ClearableStringParameter('foo', 'Param Label');

        $this->assertFalse($param->hasValue());
    }

    public function test_hasValue_emptyStringPresent_returnsTrue() : void
    {
        $_REQUEST['foo'] = '';

        $param = new ClearableStringParameter('foo', 'Param Label');

        $this->assertTrue($param->hasValue());
    }

    public function test_hasValue_whitespaceOnlyPresent_returnsTrue() : void
    {
        $_REQUEST['foo'] = '   ';

        $param = new ClearableStringParameter('foo', 'Param Label');

        $this->assertTrue($param->hasValue());
    }

    public function test_hasValue_valuePresent_returnsTrue() : void
    {
        $_REQUEST['foo'] = 'some value';

        $param = new ClearableStringParameter('foo', 'Param Label');

        $this->assertTrue($param->hasValue());
    }

    // endregion

    // region: setMaxLength() integration

    public function test_setMaxLength_valueBelowLimit_passes() : void
    {
        $_REQUEST['foo'] = 'hi';

        $param = new ClearableStringParameter('foo', 'Param Label');
        $param->setMaxLength(5);

        $this->assertSame('hi', $param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_setMaxLength_valueExceedsLimit_fails() : void
    {
        $_REQUEST['foo'] = 'toolong';

        $param = new ClearableStringParameter('foo', 'Param Label');
        $param->setMaxLength(5);

        $this->assertNull($param->getValue());
        $this->assertResultInvalid($param->getValidationResults());
        $this->assertResultHasCode($param->getValidationResults(), ParamValidationInterface::VALIDATION_MAX_LENGTH_EXCEEDED);
    }

    public function test_setMaxLength_emptyStringPresent_notCapped() : void
    {
        // An empty string (clearable value) should not be rejected by max-length
        // validation — MaxLengthValidation skips empty strings.
        $_REQUEST['foo'] = '';

        $param = new ClearableStringParameter('foo', 'Param Label');
        $param->setMaxLength(3);

        $this->assertSame('', $param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_setMaxLength_absentKey_notCapped() : void
    {
        // An absent key resolves to null; max-length validation skips null.
        $param = new ClearableStringParameter('foo', 'Param Label');
        $param->setMaxLength(3);

        $this->assertNull($param->getValue());
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }

    public function test_setMaxLength_returnsThisForFluentChaining() : void
    {
        $param = new ClearableStringParameter('foo', 'Param Label');
        $result = $param->setMaxLength(10);

        $this->assertSame($param, $result);
    }

    // endregion

    // region: Type label

    public function test_getTypeLabel_returnsClearableString() : void
    {
        $param = new ClearableStringParameter('foo', 'Param Label');

        // 'learable' is a translatable substring of 'Clearable string'. Asserting a
        // partial substring (rather than the full label) keeps this test resilient to
        // capitalisation or phrasing changes across locales, while still verifying the
        // distinctive portion of the label that confirms the correct type is returned.
        $this->assertStringContainsString('learable', $param->getTypeLabel());
    }

    // endregion
}
