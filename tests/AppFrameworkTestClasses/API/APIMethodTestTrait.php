<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\API;

use Application\API\APIMethodInterface;
use Application\API\ErrorResponsePayload;
use Application\API\Parameters\Validation\ParamValidationInterface;
use Application\API\ResponsePayload;
use AppUtils\OperationResult;

/**
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

    public function assertSuccessfulResponse(ResponsePayload|ErrorResponsePayload|APIMethodInterface $response) : ResponsePayload
    {
        if($response instanceof APIMethodInterface) {
            $response = $response->processReturn();
        }

        if($response instanceof ErrorResponsePayload) {
            $this->fail('The response is an error response: '.PHP_EOL.$response->getAsString());
        }

        return $response;
    }
}