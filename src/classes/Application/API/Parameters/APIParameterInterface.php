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

    public function getDefaultValue() : int|string|float|bool|array|null;
    public function getValue() : int|float|bool|string|array|null;
    public function hasValue() : bool;

    /**
     * @param mixed $default
     * @return $this
     */
    public function setDefaultValue(mixed $default) : self;

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
     * @param (callable(int|float|bool|string|array, OperationResult, mixed...) : void) $callback
     * @param mixed ...$args Additional arguments to pass to the callback after the value and OperationResult.
     * @return self
     */
    public function validateByCallback(callable $callback, ...$args) : self;

    /**
     * @param array<int,int|float|string|bool|array> $values
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
