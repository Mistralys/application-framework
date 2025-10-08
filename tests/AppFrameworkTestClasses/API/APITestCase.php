<?php

declare(strict_types=1);

namespace Mistralys\AppFrameworkTests\TestClasses;

use AppFrameworkTestClasses\ApplicationTestCase;
use AppFrameworkTestClasses\Traits\OperationResultTestInterface;
use AppFrameworkTestClasses\Traits\OperationResultTestTrait;
use Application\API\Parameters\Validation\ParamValidationInterface;
use AppUtils\OperationResult;

abstract class APITestCase extends ApplicationTestCase implements OperationResultTestInterface
{
    use OperationResultTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $_REQUEST = array();
        $_POST = array();
        $_GET = array();
    }

    public function assertResultHasInvalidValueType(OperationResult $result) : void
    {
        $this->assertResultHasCode($result, ParamValidationInterface::VALIDATION_INVALID_VALUE_TYPE);
    }
}
