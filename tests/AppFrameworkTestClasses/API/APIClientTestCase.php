<?php

declare(strict_types=1);

namespace Mistralys\AppFrameworkTests\TestClasses;

use AppFrameworkTestClasses\API\APIClientTestInterface;
use AppFrameworkTestClasses\API\APIClientTestTrait;
use AppFrameworkTestClasses\ApplicationTestCase;
use AppFrameworkTestClasses\Traits\OperationResultTestTrait;

abstract class APIClientTestCase extends ApplicationTestCase implements APIClientTestInterface
{
    use OperationResultTestTrait;
    use APIClientTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->startTransaction();
    }
}
