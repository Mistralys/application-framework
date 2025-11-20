<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Parameters;

use Application\API\Parameters\CommonTypes\DateParameter;
use AppUtils\Microtime;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;

final class DateParameterTest extends APITestCase
{
    public function test_validValueInRequest() : void
    {
        $value = '2020-10-31 12:34:56';

        $param = new DateParameter('foo', 'Param Label');

        $this->assertParamValidWithValue($param, $value, $value);
        $this->assertInstanceOf(Microtime::class, $param->getDate());
    }

    public function test_invalidValueInRequest() : void
    {
        $this->assertParamInvalidWithValue(
            new DateParameter('foo', 'Param Label'),
            'not a date'
        );
    }
}

