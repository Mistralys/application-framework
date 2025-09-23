<?php

declare(strict_types=1);

namespace Application\API\Parameters;

use Application\API\APIMethodInterface;
use Application\API\Parameters\Validation\ParamValidationInterface;
use AppUtils\Interfaces\StringableInterface;
use AppUtils\OperationResult;

interface APIParameterInterface
{
    public const array RESERVED_PARAM_NAMES = array(
        APIMethodInterface::REQUEST_PARAM_METHOD,
        APIMethodInterface::REQUEST_PARAM_API_VERSION
    );

    public function getName(): string;

    /**
     * @param bool $required
     * @return $this
     */
    public function makeRequired(bool $required=true) : self;

    public function isRequired(): bool;
    public function getLabel(): string;

    public function getDefaultValue() : int|string|float|bool|array;
    public function getValue() : int|float|bool|string|array;
    public function getDescription(): string;
    public function hasDescription(): bool;
    public function setDescription(string|StringableInterface $description): self;
    public function getValidationResult(): OperationResult;
    public function isValid() : bool;

    /**
     * @param ParamValidationInterface $validation
     * @return $this
     */
    public function addValidation(ParamValidationInterface $validation) : self;

    /**
     * Call a custom callback to validate the parameter value.
     *
     * If the value is invalid, the callback must call {@see OperationResult::makeError()}
     * on the provided result object.
     *
     * @param callable(int|float|bool|string|array $value, OperationResult $result, ...$args) : void $callback
     * @param mixed ...$args Additional arguments to pass to the callback after the value and OperationResult.
     * @return self
     */
    public function addValidationCallback(callable $callback, ...$args) : self;

    /**
     * @param array<int,int|float|string|bool|array> $values
     * @return $this
     */
    public function addValidationEnum(array $values) : self;
}
