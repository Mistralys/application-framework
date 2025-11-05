<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Parameters;

use Application\API\Parameters\CommonTypes\EmailParameter;
use Application\API\Parameters\CommonTypes\MD5Parameter;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;

final class MD5ParameterTests extends APITestCase
{
    public function test_validValueInRequest() : void
    {
        $value = md5('foo bar');
        $param = new MD5Parameter('foo', 'Param Label');

        $this->assertParamValidWithValue($param, $value, $value);
        $this->assertSame($value, $param->getMD5());
    }

    public function test_invalidValueInRequest() : void
    {
        $this->assertParamInvalidWithValue(
            new MD5Parameter('foo', 'Param Label'),
            'not md5 value'
        );
    }
}
