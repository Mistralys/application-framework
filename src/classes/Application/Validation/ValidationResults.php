<?php
/**
 * @package Application
 * @subpackage Validation
 */

declare(strict_types=1);

namespace Application\Validation;

use AppFrameworkTestClasses\Traits\OperationResultTestTrait;
use AppUtils\OperationResult_Collection;

/**
 * Collection of validation results, based on the {@see OperationResult_Collection} class,
 * and tied to the interface {@see ValidationResultInterface} to automate recognition of
 * classes that can provide validation results and better integration with validation systems.
 *
 * Also see the test helper trait {@see OperationResultTestTrait} that works with this class.
 *
 * @package Application
 * @subpackage Validation
 */
class ValidationResults extends OperationResult_Collection
{
    public function __construct(ValidationResultInterface $subject)
    {
        parent::__construct($subject, $subject->getLogIdentifier());
    }
}
