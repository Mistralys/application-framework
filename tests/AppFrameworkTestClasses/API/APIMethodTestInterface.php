<?php
/**
 * @package Test Classes
 * @subpackage API
 */

declare(strict_types=1);

namespace AppFrameworkTestClasses\API;

use AppFrameworkTestClasses\ApplicationTestCaseInterface;
use AppFrameworkTestClasses\Traits\OperationResultTestInterface;
use Application\API\APIMethodInterface;
use Application\API\ErrorResponsePayload;
use Application\API\ResponsePayload;
use AppUtils\OperationResult;

/**
 * @package Test Classes
 * @subpackage API
 *
 * @see APIMethodTestTrait
 */
interface APIMethodTestInterface extends ApplicationTestCaseInterface, OperationResultTestInterface
{
    /**
     * Asserts that the given operation result contains the code for an invalid value type,
     * {@see ParamValidationInterface::VALIDATION_INVALID_VALUE_TYPE}.
     */
    public function assertResultHasInvalidValueType(OperationResult $result) : void;

    public function assertSuccessfulResponse(ResponsePayload|ErrorResponsePayload|APIMethodInterface $response, string $message='') : ResponsePayload;
    public function assertErrorResponse(ResponsePayload|ErrorResponsePayload|APIMethodInterface $response) : ErrorResponsePayload;
    public function assertErrorResponseCode(ResponsePayload|ErrorResponsePayload|APIMethodInterface $response, int $code) : ErrorResponsePayload;
}
