<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Parameters;

use Application\API\Parameters\CommonTypes\NameOrTitleParameter;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;

final class NameOrTitleParameterTests extends APITestCase
{
    public function test_validValueInRequest() : void
    {
        $value = 'John Doe';

        $param = new NameOrTitleParameter('foo', 'Param Label');

        $this->assertParamValidWithValue($param, $value, $value);
        $this->assertSame($value, $param->getNameOrTitle());
    }

    public function test_invalidValueInRequest() : void
    {
        $this->assertParamInvalidWithValue(
            new NameOrTitleParameter('foo', 'Param Label'),
            '<bad>'
        );
    }
}

