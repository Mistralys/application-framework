<?php

declare(strict_types=1);

namespace Mistralys\AppFrameworkTests\TestClasses;

use AppFrameworkTestClasses\API\APIClientTestTrait;
use AppFrameworkTestClasses\API\APIMethodTestInterface;
use AppFrameworkTestClasses\API\APIMethodTestTrait;
use AppFrameworkTestClasses\ApplicationTestCase;
use AppFrameworkTestClasses\Traits\OperationResultTestTrait;

abstract class APITestCase extends ApplicationTestCase implements APIMethodTestInterface
{
    use OperationResultTestTrait;
    use APIMethodTestTrait;
    use APIClientTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $_REQUEST = array();
        $_POST = array();
        $_GET = array();
    }
}
