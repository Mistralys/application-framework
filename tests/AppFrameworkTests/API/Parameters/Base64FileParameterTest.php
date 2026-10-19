<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Parameters;

use Application\API\Parameters\Type\Base64FileParameter;
use Application\API\Parameters\Validation\ParamValidationInterface;
use Application\ConfigSettings\AppConfig;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;

final class Base64FileParameterTest extends APITestCase
{
    // region: Basic decoding

    public function test_plainBase64Value() : void
    {
        $content = 'Hello World';
        $_REQUEST['foo'] = base64_encode($content);

        $param = new Base64FileParameter('foo', 'Param Label');

        $this->assertSame($content, $param->getValue());
        $this->assertSame($content, $param->getDecodedContent());
        $this->assertSame(strlen($content), $param->getDecodedSize());
        $this->assertResultValid($param->getValidationResults());
    }

    public function test_dataURIValue() : void
    {
        $content = 'Hello World';
        $_REQUEST['foo'] = 'data:text/plain;base64,'.base64_encode($content);

        $param = new Base64FileParameter('foo', 'Param Label');

        $this->assertSame($content, $param->getValue());
        $this->assertResultValid($param->getValidationResults());
    }

    public function test_dataURIWithAdditionalParametersValue() : void
    {
        $content = 'Hello World';
        $_REQUEST['foo'] = 'data:text/plain;charset=utf-8;base64,'.base64_encode($content);

        $param = new Base64FileParameter('foo', 'Param Label');

        $this->assertSame($content, $param->getValue());
        $this->assertResultValid($param->getValidationResults());
    }

    public function test_whitespaceIsStripped() : void
    {
        $content = 'Hello World, this is a longer string to produce multiple base64 chunks.';
        $encoded = base64_encode($content);
        $wrapped = chunk_split($encoded, 8, "\n");

        $_REQUEST['foo'] = $wrapped;

        $param = new Base64FileParameter('foo', 'Param Label');

        $this->assertSame($content, $param->getValue());
        $this->assertResultValid($param->getValidationResults());
    }

    // endregion

    // region: Absent / empty / type

    public function test_absentValueIsNull() : void
    {
        unset($_REQUEST['foo']);

        $param = new Base64FileParameter('foo', 'Param Label');

        $this->assertNull($param->getValue());
        $this->assertResultValid($param->getValidationResults());
    }

    public function test_emptyStringValueIsNull() : void
    {
        $_REQUEST['foo'] = '';

        $param = new Base64FileParameter('foo', 'Param Label');

        $this->assertNull($param->getValue());
        $this->assertResultValid($param->getValidationResults());
    }

    public function test_nonStringValueIsWarning() : void
    {
        $_REQUEST['foo'] = array('not', 'a', 'string');

        $param = new Base64FileParameter('foo', 'Param Label');

        $this->assertNull($param->getValue());
        $this->assertResultHasCode($param->getValidationResults(), ParamValidationInterface::VALIDATION_INVALID_VALUE_TYPE);
    }

    // endregion

    // region: Invalid base64

    public function test_invalidBase64IsRejected() : void
    {
        // Characters outside the base64 alphabet, strict decoding must fail.
        $_REQUEST['foo'] = 'not_valid_base64!!!';

        $param = new Base64FileParameter('foo', 'Param Label');

        $this->assertNull($param->getValue());
        $this->assertResultInvalid($param->getValidationResults());
        $this->assertResultHasCode($param->getValidationResults(), ParamValidationInterface::VALIDATION_INVALID_BASE64);
    }

    // endregion

    // region: Size limit

    public function test_defaultLimitComesFromConfig() : void
    {
        $param = new Base64FileParameter('foo', 'Param Label');

        $this->assertSame(AppConfig::getAPIMaxFileContentSize(), $param->getMaxSize());
    }

    public function test_setMaxSizeOverridesDefault() : void
    {
        $param = new Base64FileParameter('foo', 'Param Label');
        $param->setMaxSize(1024);

        $this->assertSame(1024, $param->getMaxSize());
    }

    public function test_valueExactlyAtLimitIsAccepted_padded() : void
    {
        // 3 bytes of content encodes to 4 base64 characters with no padding;
        // 2 bytes of content encodes to 4 base64 characters with "==" padding.
        $content = 'ab'; // 2 bytes
        $encoded = base64_encode($content); // "YWI=" -> padded
        $this->assertStringEndsWith('=', $encoded);

        $_REQUEST['foo'] = $encoded;

        $param = new Base64FileParameter('foo', 'Param Label');
        $param->setMaxSize(strlen($content));

        $this->assertSame($content, $param->getValue());
        $this->assertResultValid($param->getValidationResults());
    }

    public function test_valueExactlyAtLimitIsAccepted_unpadded() : void
    {
        $content = 'abc'; // 3 bytes
        $encoded = base64_encode($content); // "YWJj" -> no padding
        $this->assertStringEndsNotWith('=', $encoded);

        $_REQUEST['foo'] = $encoded;

        $param = new Base64FileParameter('foo', 'Param Label');
        $param->setMaxSize(strlen($content));

        $this->assertSame($content, $param->getValue());
        $this->assertResultValid($param->getValidationResults());
    }

    public function test_oneByteOverLimitIsRejected_padded() : void
    {
        $content = 'ab'; // 2 bytes
        $encoded = base64_encode($content);

        $_REQUEST['foo'] = $encoded;

        $param = new Base64FileParameter('foo', 'Param Label');
        $param->setMaxSize(strlen($content) - 1);

        $this->assertNull($param->getValue());
        $this->assertResultHasCode($param->getValidationResults(), ParamValidationInterface::VALIDATION_FILE_SIZE_EXCEEDED);
    }

    public function test_oneByteOverLimitIsRejected_unpadded() : void
    {
        $content = 'abc'; // 3 bytes
        $encoded = base64_encode($content);

        $_REQUEST['foo'] = $encoded;

        $param = new Base64FileParameter('foo', 'Param Label');
        $param->setMaxSize(strlen($content) - 1);

        $this->assertNull($param->getValue());
        $this->assertResultHasCode($param->getValidationResults(), ParamValidationInterface::VALIDATION_FILE_SIZE_EXCEEDED);
    }

    /**
     * The size check must run on the encoded length before decoding, so an
     * oversized value that also contains invalid base64 characters is
     * rejected with the size code, not the invalid-base64 code.
     */
    public function test_oversizedAndInvalidBase64YieldsSizeExceeded() : void
    {
        $_REQUEST['foo'] = str_repeat('!', 100);

        $param = new Base64FileParameter('foo', 'Param Label');
        $param->setMaxSize(1);

        $this->assertNull($param->getValue());
        $this->assertResultHasCode($param->getValidationResults(), ParamValidationInterface::VALIDATION_FILE_SIZE_EXCEEDED);
        $this->assertFalse($param->getValidationResults()->containsCode(ParamValidationInterface::VALIDATION_INVALID_BASE64));
    }

    // endregion

    // region: selectValue()

    public function test_selectValueOverridesRequest() : void
    {
        $content = 'From Request';
        $_REQUEST['foo'] = base64_encode($content);

        $param = new Base64FileParameter('foo', 'Param Label');
        $param->selectValue('Selected Content');

        $this->assertSame('Selected Content', $param->getValue());
    }

    // endregion
}
