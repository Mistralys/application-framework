<?php

declare(strict_types=1);

namespace AppFrameworkTests\Application;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\API\Parameters\Type\StringParameter;

final class APITests extends ApplicationTestCase
{
    public function test_paramValidation() : void
    {
        $_REQUEST['foo'] = 'bar';

        $param = new StringParameter('foo', 'Foo Label');

        $this->assertSame('bar', $param->getValue());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $_REQUEST = array();
        $_POST = array();
        $_GET = array();
    }
}
