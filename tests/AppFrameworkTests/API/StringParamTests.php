<?php

declare(strict_types=1);

namespace AppFrameworkTests\Application;

use Application\API\Parameters\Type\StringParameter;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;

final class StringParamTests extends APITestCase
{
    public function test_paramValidation() : void
    {
        $_REQUEST['foo'] = 'bar';

        $param = new StringParameter('foo', 'Foo Label');

        $this->assertSame('bar', $param->getValue());
    }

    public function test_invalidValueInRequest() : void
    {
        $_REQUEST['foo'] = 42;

        $param = new StringParameter('foo', 'Foo Label');

        $this->assertNull($param->getValue());
    }
}
