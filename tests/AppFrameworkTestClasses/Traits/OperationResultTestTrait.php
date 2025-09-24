<?php
/**
 * @package Application Tests
 * @subpackage Operation Results
 */

declare(strict_types=1);

namespace AppFrameworkTestClasses\Traits;

use AppUtils\OperationResult;
use AppUtils\OperationResult_Collection;

/**
 * Trait used to add common assertions for {@see OperationResult} objects.
 *
 * ## Usage
 *
 * 1. Include the trait in your test class.
 * 2. Implement the matching interface {@see OperationResultTestInterface}.
 *
 * @package Application Tests
 * @subpackage Operation Results
 *
 * @see OperationResultTestInterface
 */
trait OperationResultTestTrait
{
    public function assertResultValid(OperationResult $result) : void
    {
        $this->assertTrue($result->isValid());
    }

    public function assertResultValidWithNoMessages(OperationResult $result) : void
    {
        $this->assertResultValid($result);
        $this->assertResultHasNoMessages($result);
    }

    public function assertResultHasNoMessages(OperationResult $result) : void
    {
        if($result instanceof OperationResult_Collection) {
            $this->assertSame(0, $result->countResults());
        } else {
            $this->assertSame('', $result->getMessage());
            $this->assertSame(0, $result->getCode());
        }
    }

    public function assertResultHasCode(OperationResult $result, int $code) : void
    {
        if($result instanceof OperationResult_Collection) {
            $this->assertTrue($result->containsCode($code));
        } else {
            $this->assertSame($code, $result->getCode());
        }
    }

    public function assertResultInvalid(OperationResult $result) : void
    {
        $this->assertFalse($result->isValid());
    }
}
