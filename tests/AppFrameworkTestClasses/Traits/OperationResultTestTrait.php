<?php
/**
 * @package Application Tests
 * @subpackage Operation Results
 */

declare(strict_types=1);

namespace AppFrameworkTestClasses\Traits;

use Application\Validation\ValidationResultInterface;
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
    public function assertResultValid(OperationResult|ValidationResultInterface $result) : void
    {
        $this->assertTrue($this->resolveResult($result)->isValid());
    }

    protected function resolveResult(OperationResult|ValidationResultInterface $result) : OperationResult
    {
        if($result instanceof ValidationResultInterface) {
            return $result->getValidationResults();
        }

        return $result;
    }

    public function assertResultValidWithNoMessages(OperationResult|ValidationResultInterface $result) : void
    {
        $result = $this->resolveResult($result);

        $this->assertResultValid($result);
        $this->assertResultHasNoMessages($result);
    }

    public function assertResultHasNoMessages(OperationResult|ValidationResultInterface $result) : void
    {
        $result = $this->resolveResult($result);

        if($result instanceof OperationResult_Collection) {
            $this->assertSame(0, $result->countResults());
        } else {
            $this->assertSame('', $result->getMessage());
            $this->assertSame(0, $result->getCode());
        }
    }

    public function assertResultHasCode(OperationResult|ValidationResultInterface $result, int $code) : void
    {
        $result = $this->resolveResult($result);

        if($result instanceof OperationResult_Collection) {
            $this->assertTrue($result->containsCode($code));
        } else {
            $this->assertSame($code, $result->getCode());
        }
    }

    public function assertResultInvalid(OperationResult|ValidationResultInterface $result) : void
    {
        $this->assertFalse($this->resolveResult($result)->isValid());
    }
}
