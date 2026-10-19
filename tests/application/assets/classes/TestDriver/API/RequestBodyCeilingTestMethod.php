<?php
/**
 * @package TestDriver
 * @subpackage API
 */

declare(strict_types=1);

namespace TestDriver\API;

use Application\API\Request\RequestBodyReader;

/**
 * Test stub for {@see \AppFrameworkTests\API\RequestBodyCeilingTest}: a
 * {@see TestJSONBodyParamsMethod} subclass that lets a test inject the
 * exact {@see RequestBodyReader} instance {@see \Application\API\BaseMethods\BaseAPIMethod::getRequestBody()}
 * reads through, following the direct-instantiation pattern of
 * {@see \AppFrameworkTests\API\Parameters\JSONBodyParamResolutionTest}.
 *
 * `METHOD_NAME` is overridden so the test's `$_REQUEST[method]` dispatch
 * resolves to this subclass rather than the parent fixture.
 *
 * @package TestDriver
 * @subpackage API
 *
 * @see \AppFrameworkTests\API\RequestBodyCeilingTest
 */
class RequestBodyCeilingTestMethod extends TestJSONBodyParamsMethod
{
    public const string METHOD_NAME = 'RequestBodyCeilingTest';

    private ?RequestBodyReader $testReader = null;

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    /**
     * Injects the reader {@see self::createRequestBodyReader()} returns for
     * the remainder of the test, in place of the real, booted-ceiling
     * default.
     *
     * @param RequestBodyReader $reader
     * @return $this
     */
    public function setTestReader(RequestBodyReader $reader) : self
    {
        $this->testReader = $reader;
        return $this;
    }

    protected function createRequestBodyReader() : RequestBodyReader
    {
        if($this->testReader !== null) {
            return $this->testReader;
        }

        return parent::createRequestBodyReader();
    }

    /**
     * Test-only accessor exposing the otherwise-protected
     * {@see self::createRequestBodyReader()}, so a test can inspect the
     * default reader's {@see RequestBodyReader::getMaxBytes()} without an
     * injected override.
     *
     * @return RequestBodyReader
     */
    public function exposeCreateRequestBodyReader() : RequestBodyReader
    {
        return $this->createRequestBodyReader();
    }
}
