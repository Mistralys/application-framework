<?php

declare(strict_types=1);

namespace AppFrameworkTests\API;

use Application\API\APIManager;
use Application\API\Clients\API\APIKeyMethodInterface;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;
use TestDriver\API\TestAPIKeyMethod;
use TestDriver\API\TestGetCountryBySetAPI;

final class ErrorResponseTest extends APITestCase
{
    public function test_errorDataIncludesValidationErrors() : void
    {
        $method = new TestAPIKeyMethod(APIManager::getInstance());

        $response = $this->assertErrorResponse($method);

        $data = $response->getErrorData();

        $this->assertArrayHasKey('validationErrors', $data);
        $this->assertIsArray($data['validationErrors']);
        $this->assertNotEmpty($data['validationErrors']);

        foreach($data['validationErrors'] as $error) {
            $this->assertArrayHasKey('param', $error);
            $this->assertArrayHasKey('code', $error);
            $this->assertArrayHasKey('message', $error);
            $this->assertIsInt($error['code']);
            $this->assertIsString($error['message']);
            $this->assertStringNotContainsString('ERROR #', $error['message']);
        }
    }

    public function test_errorDataStillIncludesValidationMessages() : void
    {
        $method = new TestAPIKeyMethod(APIManager::getInstance());

        $response = $this->assertErrorResponse($method);

        $data = $response->getErrorData();

        $this->assertArrayHasKey('validationMessages', $data);
        $this->assertIsArray($data['validationMessages']);
        $this->assertNotEmpty($data['validationMessages']);

        foreach($data['validationMessages'] as $message) {
            $this->assertIsString($message);
        }
    }

    public function test_validationErrorParamMatchesParameterName() : void
    {
        $method = new TestAPIKeyMethod(APIManager::getInstance());

        $response = $this->assertErrorResponse($method);

        $data = $response->getErrorData();

        $paramNames = array_column($data['validationErrors'], 'param');

        $this->assertContains(APIKeyMethodInterface::API_KEY_PARAM_NAME, $paramNames);
    }

    public function test_ruleLevelValidationErrorHasNullParam() : void
    {
        $method = new TestGetCountryBySetAPI(APIManager::getInstance());

        $response = $this->assertErrorResponse($method);

        $data = $response->getErrorData();

        $this->assertArrayHasKey('validationErrors', $data);
        $this->assertNotEmpty($data['validationErrors']);
        $this->assertNull($data['validationErrors'][0]['param']);
    }
}
