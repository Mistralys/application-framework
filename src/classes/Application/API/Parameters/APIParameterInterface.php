<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters;

use Application\API\APIMethodInterface;
use Application\API\Parameters\Validation\ParamValidationInterface;
use Application\Validation\ValidationLoggableInterface;
use Application\Validation\ValidationResults;
use AppUtils\Interfaces\StringableInterface;
use AppUtils\OperationResult;

/**
 * Interface for API method parameters.
 *
 * A base implementation is provided by {@see BaseAPIParameter}.
 *
 * @package API
 * @subpackage Parameters
 */
interface APIParameterInterface extends ValidationLoggableInterface
{
    public const array RESERVED_PARAM_NAMES = array(
        APIMethodInterface::REQUEST_PARAM_METHOD,
        APIMethodInterface::REQUEST_PARAM_API_VERSION
    );

    public function getName(): string;
    public function getTypeLabel() : string;

    /**
     * @param bool $required
     * @return $this
     */
    public function makeRequired(bool $required=true) : self;

    public function isRequired(): bool;
    public function getLabel(): string;

    /**
     * @return int|string|float|bool|array<int|string,mixed>|null
     */
    public function getDefaultValue() : int|string|float|bool|array|null;

    /**
     * @return int|float|bool|string|array<int|string,mixed>|null
     */
    public function getValue() : int|float|bool|string|array|null;
    public function hasValue() : bool;

    /**
     * Manually selects the value to use for the parameter.
     * The value will be used instead of any value provided
     * in the request.
     *
     * If the parameter implements {@see SelectableValueParamInterface},
     * this will also check that the value exists in the selectable values.
     *
     * @param int|float|bool|string|array<int|string,mixed>|null $value Note: Set to `NULL` to clear any selected value.
     * @return $this
     */
    public function selectValue(int|float|bool|string|array|null $value) : self;

    /**
     * @param int|float|bool|string|array<int|string,mixed>|null $default
     * @return $this
     */
    public function setDefaultValue(int|float|bool|string|array|null $default) : self;

    public function getDescription(): string;
    public function hasDescription(): bool;
    public function setDescription(string|StringableInterface $description): self;
    public function getValidationResults(): ValidationResults;
    public function isValid() : bool;

    /**
     * @param ParamValidationInterface $validation
     * @return $this
     */
    public function validateBy(ParamValidationInterface $validation) : self;

    /**
     * Call a custom callback to validate the parameter value.
     *
     * If the value is invalid, the callback must call {@see OperationResult::makeError()}
     * on the provided result object.
     *
     * @param (callable(int|float|bool|string|array<int|string,mixed>, OperationResult, mixed...) : void) $callback
     * @param mixed ...$args Additional arguments to pass to the callback after the value and OperationResult.
     * @return self
     */
    public function validateByCallback(callable $callback, ...$args) : self;

    /**
     * @param array<int,int|float|string|bool|array<int|string,mixed>> $values
     * @return $this
     */
    public function validateByEnum(array $values) : self;

    /**
     * Invalidate this parameter. This causes it to be excluded from the final output
     * when validating the required parameters for a request using validation rules.
     *
     * @return $this
     */
    public function invalidate() : self;

    /**
     * Whether this parameter is enabled. Disabled parameters are ignored for value
     * resolution (they are disabled automatically when they have been invalidated,
     * for example).
     *
     * @return bool
     */
    public function isInvalidated() : bool;
}
