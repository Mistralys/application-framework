<?php
/**
 * @package Test Classes
 * @subpackage API
 */

declare(strict_types=1);

namespace AppFrameworkTestClasses\API;

use Application\API\APIMethodInterface;
use Application\API\ErrorResponsePayload;
use Application\API\Parameters\Validation\ParamValidationInterface;
use Application\API\ResponsePayload;
use AppUtils\OperationResult;

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
}