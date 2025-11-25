<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Parameters;

use Application\API\Parameters\CommonTypes\AlphanumericParameter;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;

final class AlphanumericParameterTest extends APITestCase
{
    public function test_validValueInRequest() : void
    {
        $value = 'abc123';

        $param = new AlphanumericParameter('foo', 'Param Label');

        $this->assertParamValidWithValue($param, $value, $value);
        $this->assertSame($value, $param->getAlphanumeric());
    }

    public function test_invalidValueInRequest() : void
    {
        $this->assertParamInvalidWithValue(
            new AlphanumericParameter('foo', 'Param Label'),
            'abc-123'
        );
    }
}

