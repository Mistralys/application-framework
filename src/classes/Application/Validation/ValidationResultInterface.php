<?php
/**
 * @package Application
 * @subpackage Validation
 */

declare(strict_types=1);

namespace Application\Validation;

/**
 * Interface for classes that perform validation and can return
 * a set of validation results.
 *
 * @package Application
 * @subpackage Validation
 */
interface ValidationResultInterface
{
    /**
     * Gets all validation results. This will trigger the validation
     * if it has not been done yet.
     *
     * @return ValidationResults
     */
    public function getValidationResults() : ValidationResults;

    /**
     * Label of the validating system to be used in logs and other messages.
     * Should be in invariant language, not translated.
     *
     * @return string
     */
    public function getValidatorLabel() : string;
}
