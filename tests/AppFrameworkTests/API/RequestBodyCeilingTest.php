<?php

declare(strict_types=1);

namespace AppFrameworkTests\API;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\ErrorResponsePayload;
use Application\API\Request\RequestBodyReader;
use Application\API\ResponsePayload;
use Application\API\Traits\JSONRequestInterface;
use Application\ConfigSettings\AppConfig;
use Connectors_ResponseCode;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;
use TestDriver\API\RequestBodyCeilingTestMethod;

/**
 * Covers AC-04, AC-05 and AC-06: {@see \Application\API\BaseMethods\BaseAPIMethod::createRequestBodyReader()}
 * and the {@see \Application\API\BaseMethods\BaseAPIMethod::getRequestBody()} conversion of a
 * {@see \Application\API\Request\RequestBodyException} into the matching client-facing error
 * response — the too-large/discarded branches (182802/413) and the unchanged read-failure
 * branch (182801) — plus the `setRequestBody()` preset bypass and the default reader's ceiling.
 */
final class RequestBodyCeilingTest extends APITestCase
{
    // region: Helpers

    private string $tempFile = '';

    protected function setUp() : void
    {
        parent::setUp();

        $this->tempFile = sys_get_temp_dir().'/request-body-ceiling-test-'.uniqid().'.tmp';
    }

    protected function tearDown() : void
    {
        parent::tearDown();

        if($this->tempFile !== '' && file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    private function createMethod() : RequestBodyCeilingTestMethod
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = RequestBodyCeilingTestMethod::METHOD_NAME;

        return new RequestBodyCeilingTestMethod(APIManager::getInstance());
    }

    // endregion

    // region: AC-05 — too-large / discarded conversion

    public function test_oversizedBodyYields182802And413() : void
    {
        file_put_contents($this->tempFile, str_repeat('a', 20));

        $method = $this->createMethod()
            ->setTestReader(new RequestBodyReader(10, null, $this->tempFile));

        $data = $method->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $data);
        $this->assertSame(JSONRequestInterface::ERROR_REQUEST_BODY_TOO_LARGE, $data->getErrorCode());
        $this->assertSame(Connectors_ResponseCode::HTTP_REQUEST_ENTITY_TOO_LARGE, $data->getHttpStatusCode());
        $this->assertSame(10, $data->getErrorData()[JSONRequestInterface::RESPONSE_KEY_ERROR_MAX_REQUEST_BODY_SIZE] ?? null);
        $this->assertStringContainsString('10', $data->getErrorMessage());
    }

    public function test_discardedBodyYields182802And413() : void
    {
        file_put_contents($this->tempFile, '');

        $method = $this->createMethod()
            ->setTestReader(new RequestBodyReader(1024, 100, $this->tempFile));

        $data = $method->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $data);
        $this->assertSame(JSONRequestInterface::ERROR_REQUEST_BODY_TOO_LARGE, $data->getErrorCode());
        $this->assertSame(Connectors_ResponseCode::HTTP_REQUEST_ENTITY_TOO_LARGE, $data->getHttpStatusCode());
        $this->assertSame(1024, $data->getErrorData()[JSONRequestInterface::RESPONSE_KEY_ERROR_MAX_REQUEST_BODY_SIZE] ?? null);
    }

    // endregion

    // region: AC-06 — read failure unchanged, within-ceiling unaffected

    public function test_readFailureYields182801() : void
    {
        $method = $this->createMethod()
            ->setTestReader(new RequestBodyReader(10, null, '/nonexistent/path/does-not-exist'));

        $data = $method->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $data);
        $this->assertSame(JSONRequestInterface::ERROR_FAILED_TO_READ_INPUT, $data->getErrorCode());
    }

    public function test_bodyWithinCeilingResolvesNormally() : void
    {
        $body = '{}';
        file_put_contents($this->tempFile, $body);

        $method = $this->createMethod()
            ->setTestReader(new RequestBodyReader(1000, strlen($body), $this->tempFile));

        $data = $method->processReturn();

        $this->assertInstanceOf(ResponsePayload::class, $data);
    }

    // endregion

    // region: AC-04 — preset bypass, default reader ceiling

    public function test_presetBodyBypassesReader() : void
    {
        $method = $this->createMethod()
            // Points at a nonexistent stream under a zero ceiling: if the
            // reader were ever consulted, read() would throw. A preset must
            // bypass it entirely.
            ->setTestReader(new RequestBodyReader(0, null, '/nonexistent/path/does-not-exist'))
            ->setRequestBody('{}');

        $data = $method->processReturn();

        $this->assertInstanceOf(ResponsePayload::class, $data, 'A setRequestBody() preset must bypass the reader entirely.');
    }

    public function test_defaultReaderUsesBootedCeiling() : void
    {
        $method = $this->createMethod();

        $reader = $method->exposeCreateRequestBodyReader();

        $this->assertSame(AppConfig::getAPIMaxRequestBodySize(), $reader->getMaxBytes());
    }

    // endregion
}
