<?php
/**
 * @package Application Tests
 * @subpackage Operation Results
 */

declare(strict_types=1);

namespace AppFrameworkTestClasses\Traits;

use AppFrameworkTestClasses\ApplicationTestCaseInterface;
use Application\Validation\ValidationResultInterface;
use AppUtils\OperationResult;

/**
 * @package Application Tests
 * @subpackage Operation Results
 */
interface OperationResultTestInterface extends ApplicationTestCaseInterface
{
    public function assertResultValidWithNoMessages(OperationResult|ValidationResultInterface $result) : void;
    public function assertResultValid(OperationResult|ValidationResultInterface $result) : void;
    public function assertResultInvalid(OperationResult|ValidationResultInterface $result) : void;
    public function assertResultHasNoMessages(OperationResult|ValidationResultInterface $result) : void;
    public function assertResultHasCode(OperationResult|ValidationResultInterface $result, int $code) : void;
}
