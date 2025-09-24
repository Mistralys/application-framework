<?php
/**
 * @package Application Tests
 * @subpackage Operation Results
 */

declare(strict_types=1);

namespace AppFrameworkTestClasses\Traits;

use AppFrameworkTestClasses\ApplicationTestCaseInterface;
use AppUtils\OperationResult;

/**
 * @package Application Tests
 * @subpackage Operation Results
 */
interface OperationResultTestInterface extends ApplicationTestCaseInterface
{
    public function assertResultValidWithNoMessages(OperationResult $result) : void;
    public function assertResultValid(OperationResult $result) : void;
    public function assertResultInvalid(OperationResult $result) : void;
    public function assertResultHasNoMessages(OperationResult $result) : void;
    public function assertResultHasCode(OperationResult $result, int $code) : void;
}
