<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Parameters;

use Application\API\Parameters\APIParameterInterface;
use Application\API\Parameters\Type\StringParameter;
use AppUtils\OperationResult;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;

/**
 * Tests for {@see \Application\API\Parameters\BaseAPIParameter::validateByCallback()},
 * verifying its extra `...$args` are forwarded to the callback after the
 * standard `(value, result, param)` triplet, and that callers passing no
 * extra args see no change in behaviour.
 */
final class CallbackValidationArgsTest extends APITestCase
{
    public function test_argsAreForwardedToCallback() : void
    {
        $_REQUEST['foo'] = 'bar';

        $received = array();

        $param = new StringParameter('foo', 'Param Label');
        $param->validateByCallback(
            function (mixed $value, OperationResult $result, APIParameterInterface $param, mixed ...$extra) use (&$received) : void {
                $received = array($value, $result, $param, ...$extra);
            },
            'extra-a',
            42
        );

        $this->assertResultValidWithNoMessages($param->getValidationResults());

        $this->assertSame('bar', $received[0]);
        $this->assertInstanceOf(OperationResult::class, $received[1]);
        $this->assertSame($param, $received[2]);
        $this->assertSame('extra-a', $received[3]);
        $this->assertSame(42, $received[4]);
    }

    public function test_noArgsCallbackUnchanged() : void
    {
        $_REQUEST['foo'] = 'bar';

        $received = array();

        $param = new StringParameter('foo', 'Param Label');
        $param->validateByCallback(
            function (mixed $value, OperationResult $result, APIParameterInterface $param) use (&$received) : void {
                $received = array($value, $result, $param);
            }
        );

        $this->assertResultValidWithNoMessages($param->getValidationResults());

        $this->assertSame('bar', $received[0]);
        $this->assertInstanceOf(OperationResult::class, $received[1]);
        $this->assertSame($param, $received[2]);
        $this->assertCount(3, $received, 'No extra args must be appended when none were passed to validateByCallback().');
    }
}
