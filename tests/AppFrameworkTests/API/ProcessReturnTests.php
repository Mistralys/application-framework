<?php

declare(strict_types=1);

namespace AppFrameworkTests\API;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\ErrorResponsePayload;
use Application\API\ResponsePayload;
use AppUtils\ConvertHelper\JSONConverter;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;
use TestDriver\API\TestErrorResponseMethod;
use TestDriver\API\TestJSON2JSONMethod;

final class ProcessReturnTests extends APITestCase
{
    public function test_processReturnGetData() : void
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestJSON2JSONMethod::METHOD_NAME;

        $JSONData = array('test' => 'foo');

        $method = new TestJSON2JSONMethod(APIManager::getInstance());

        // Simulate the request body, because `php://input` streams are not writable.
        $method->setRequestBody(JSONConverter::var2json($JSONData));

        $data = $method->processReturn();

        $this->assertInstanceOf(ResponsePayload::class, $data);

        $this->assertResultValidWithNoMessages($method->getValidationResults());

        $this->assertSame($JSONData, $data->getData());
    }

    public function test_processReturnErrorResponse() : void
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestErrorResponseMethod::METHOD_NAME;

        $method = new TestErrorResponseMethod(APIManager::getInstance());

        $data = $method->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $data);

        $this->assertSame(TestErrorResponseMethod::ERROR_CODE_ERROR_RESPONSE, $data->getErrorCode());
        $this->assertSame(TestErrorResponseMethod::ERROR_MESSAGE, $data->getErrorMessage());
    }
}
