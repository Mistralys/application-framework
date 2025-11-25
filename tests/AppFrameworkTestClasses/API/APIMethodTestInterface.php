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
use Application\API\Parameters\APIParameterInterface;
use Application\API\ResponsePayload;
use AppUtils\OperationResult;

/**
 * @package Test Classes
 * @subpackage API
 *
 * @see APIMethodTestTrait
 */
interface APIMethodTestInterface extends ApplicationTestCaseInterface, OperationResultTestInterface, APIClientTestInterface
{
    /**
     * Asserts that the given operation result contains the code for an invalid value type,
     * {@see ParamValidationInterface::VALIDATION_INVALID_VALUE_TYPE}.
     */
    public function assertResultHasInvalidValueType(OperationResult $result) : void;

    public function assertSuccessfulResponse(ResponsePayload|ErrorResponsePayload|APIMethodInterface $response, string $message='') : ResponsePayload;
    public function assertErrorResponse(ResponsePayload|ErrorResponsePayload|APIMethodInterface $response) : ErrorResponsePayload;
    public function assertErrorResponseCode(ResponsePayload|ErrorResponsePayload|APIMethodInterface $response, int $code) : ErrorResponsePayload;

    /**
     * Asserts that the given parameter is invalid when given the specified value.
     */
    public function assertParamInvalidWithValue(APIParameterInterface $param, mixed $value) : void;

    /**
     * Asserts that the given parameter's value matches the expected value,
     * independently of its validity.
     */
    public function assertParamValueIsSame(APIParameterInterface $param, mixed $value, int|float|string|array|bool|NULL $expected) : void;

    /**
     * Asserts that the given parameter is valid when given the specified value,
     * and that its value matches the expected value.
     */
    public function assertParamValidWithValue(APIParameterInterface $param, mixed $value, int|float|string|array|bool|NULL $expected) : void;
}
