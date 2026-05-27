<?php

declare(strict_types=1);

namespace Application\API\Parameters\Validation;

/**
 * Base class for all parameter validators.
 *
 * This class serves as a concrete base type for all parameter validators
 * in the `Validation/Type/` namespace. No shared logic is provided here
 * intentionally — the class exists purely as a type anchor so that validators
 * can be type-checked against `BaseParamValidation` rather than the broader
 * `ParamValidationInterface`.
 *
 * If future validators need common utilities (e.g. an `isValidatable()` guard
 * that centralises the null / empty-string / non-string skip logic), they
 * should be added here rather than duplicated across individual Type/ validators.
 *
 * @package API
 * @subpackage Parameters
 */
abstract class BaseParamValidation implements ParamValidationInterface
{
}
