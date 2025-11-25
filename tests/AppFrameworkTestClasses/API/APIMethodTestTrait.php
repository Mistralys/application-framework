<?php
/**
 * @package Test Classes
 * @subpackage API
 */

declare(strict_types=1);

namespace AppFrameworkTestClasses\API;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\Clients\API\APIKeyMethodInterface;
use Application\API\ErrorResponsePayload;
use Application\API\Parameters\APIParameterInterface;
use Application\API\Parameters\Validation\ParamValidationInterface;
use Application\API\ResponsePayload;
use AppUtils\ArrayDataCollection;
use AppUtils\ClassHelper;
use AppUtils\OperationResult;
use Maileditor\API\GetBusinessAreasAPI;

/**
 * @package Test Classes
 * @subpackage API
 *
 * @see APIMethodTestInterface
 */
trait APIMethodTestTrait
{
    /**
     * Asserts that the given operation result contains the code for an invalid value type,
     * {@see ParamValidationInterface::VALIDATION_INVALID_VALUE_TYPE}.
     */
    public function assertResultHasInvalidValueType(OperationResult $result) : void
    {
        $this->assertResultHasCode($result, ParamValidationInterface::VALIDATION_INVALID_VALUE_TYPE);
    }

    public function assertSuccessfulResponse(ResponsePayload|ErrorResponsePayload|APIMethodInterface $response, string $message='') : ResponsePayload
    {
        if($response instanceof APIMethodInterface) {
            $response = $response->processReturn();
        }

        if($response instanceof ErrorResponsePayload) {
            $this->fail(
                $message.PHP_EOL.
                'Expected a successful response, but it\'s an error response: '.PHP_EOL.
                $response->getAsString()
            );
        }

        return $response;
    }

    public function assertErrorResponse(ResponsePayload|ErrorResponsePayload|APIMethodInterface $response) : ErrorResponsePayload
    {
        if($response instanceof APIMethodInterface) {
            $response = $response->processReturn();
        }

        if(!$response instanceof ErrorResponsePayload) {
            $this->fail('The response is expected to be an error response, given: '.PHP_EOL.get_class($response));
        }

        $this->addToAssertionCount(1);

        return $response;
    }

    public function assertErrorResponseCode(ResponsePayload|ErrorResponsePayload|APIMethodInterface $response, int $code) : ErrorResponsePayload
    {
        $response = $this->assertErrorResponse($response);

        $this->assertSame($code, $response->getErrorCode(), 'The response code is not as expected.');

        return $response;
    }

    /**
     * @param class-string<APIMethodInterface> $methodClass
     * @param array<string,string|int|bool|float|array|NULL>|ArrayDataCollection|NULL $requestParams Any request parameters that the method may require
     * @return void
     */
    public function assertMethodCallIsSuccessful(string $methodClass, array|ArrayDataCollection|NULL $requestParams=null) : void
    {
        if($requestParams === null) {
            $requestParams = array();
        }

        if($requestParams instanceof ArrayDataCollection) {
            $requestParams = $requestParams->getData();
        }

        $method = new $methodClass(APIManager::getInstance());

        $this->assertInstanceOf(APIMethodInterface::class, $method, 'Must be an API method class name');

        if($method instanceof APIKeyMethodInterface) {
            $method->manageParamAPIKey()->selectKey($this->createTestAPIKey());
        }

        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = $method->getMethodName();

        foreach($requestParams as $key => $value) {
            $_REQUEST[$key] = $value;
        }

        $this->assertSuccessfulResponse(new $methodClass(APIManager::getInstance())->processReturn());
    }

    public function assertParamInvalidWithValue(APIParameterInterface $param, mixed $value) : void
    {
        $_REQUEST[$param->getName()] = $value;

        $this->assertNull($param->getValue());
        $this->assertResultInvalid($param->getValidationResults());
    }

    public function assertParamValueIsSame(APIParameterInterface $param, mixed $value, int|float|string|array|bool|NULL $expected) : void
    {
        $_REQUEST[$param->getName()] = $value;

        $this->assertSame($expected, $param->getValue());
    }

    public function assertParamValidWithValue(APIParameterInterface $param, mixed $value, int|float|string|array|bool|NULL $expected) : void
    {
        $this->assertParamValueIsSame($param, $value, $expected);
        $this->assertResultValidWithNoMessages($param->getValidationResults());
    }
}
