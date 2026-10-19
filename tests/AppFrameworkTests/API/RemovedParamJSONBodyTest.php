<?php

declare(strict_types=1);

namespace AppFrameworkTests\API;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\ErrorResponsePayload;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;
use TestDriver\API\TestJSONBodyParamsMethod;
use TestDriver\API\TestRemovedParamMethod;

/**
 * Covers AC-19: {@see \Application\API\BaseMethods\BaseAPIMethod::rejectRemovedParams()}
 * now sources its key list from {@see \Application\API\Parameters\APIParamManager::getValueSource()},
 * so a declared removed name sent inside a JSON body is detected the same
 * way as one sent in the query string — for both a JSON method
 * ({@see TestJSONBodyParamsMethod}, which declares {@see TestJSONBodyParamsMethod::PARAM_LEGACY_LABEL}
 * removed) and a non-JSON method ({@see TestRemovedParamMethod}).
 */
final class RemovedParamJSONBodyTest extends APITestCase
{
    private function createMethod(string $body='{}') : TestJSONBodyParamsMethod
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestJSONBodyParamsMethod::METHOD_NAME;

        $method = new TestJSONBodyParamsMethod(APIManager::getInstance());
        $method->setRequestBody($body);

        return $method;
    }

    public function test_removedNameInsideJSONBodyIsRejected() : void
    {
        $method = $this->createMethod(json_encode(array(
            TestJSONBodyParamsMethod::PARAM_LEGACY_LABEL => 'legacy-value-in-body',
        ), JSON_THROW_ON_ERROR));

        $data = $method->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $data);
        $this->assertSame(APIMethodInterface::ERROR_REMOVED_PARAMETER_USED, $data->getErrorCode());
    }

    public function test_removedNameInQueryStringOfJSONMethodIsStillRejected() : void
    {
        $_REQUEST[TestJSONBodyParamsMethod::PARAM_LEGACY_LABEL] = 'legacy-value-in-query';

        $method = $this->createMethod('{}');

        $data = $method->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $data);
        $this->assertSame(APIMethodInterface::ERROR_REMOVED_PARAMETER_USED, $data->getErrorCode());
    }

    /**
     * Regression guard for non-JSON methods: {@see TestRemovedParamMethod}
     * (not a {@see \Application\API\Traits\JSONRequestInterface} method)
     * must keep detecting a removed name sent in the query string, exactly
     * as before this WP's change to the key source.
     */
    public function test_removedNameInQueryStringOfNonJSONMethodIsStillRejected() : void
    {
        $_REQUEST[TestRemovedParamMethod::PARAM_NEW_NAME] = 'value';
        $_REQUEST[TestRemovedParamMethod::PARAM_OLD_NAME] = 'legacy-value';
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestRemovedParamMethod::METHOD_NAME;

        $method = new TestRemovedParamMethod(APIManager::getInstance());
        $data = $method->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $data);
        $this->assertSame(APIMethodInterface::ERROR_REMOVED_PARAMETER_USED, $data->getErrorCode());
    }
}
