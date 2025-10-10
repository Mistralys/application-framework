<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\API;

use AppFrameworkTestClasses\ApplicationTestCaseInterface;
use AppFrameworkTestClasses\Traits\OperationResultTestInterface;
use Application\API\APIMethodInterface;
use Application\API\ErrorResponsePayload;
use Application\API\ResponsePayload;
use AppUtils\OperationResult;

/**
 * @see APIMethodTestTrait
 */
interface APIMethodTestInterface extends ApplicationTestCaseInterface, OperationResultTestInterface
{
    /**
     * Asserts that the given operation result contains the code for an invalid value type,
     * {@see ParamValidationInterface::VALIDATION_INVALID_VALUE_TYPE}.
     */
    public function assertResultHasInvalidValueType(OperationResult $result) : void;

    public function assertSuccessfulResponse(ResponsePayload|ErrorResponsePayload|APIMethodInterface $response) : ResponsePayload;
}
