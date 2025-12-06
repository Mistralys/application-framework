<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Parameters;

use Application\API\Parameters\CommonTypes\EmailParameter;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;

final class EmailParameterTest extends APITestCase
{
    public function test_validValueInRequest() : void
    {
        $value = 'valid@email.com';

        $param = new EmailParameter('foo', 'Param Label');

        $this->assertParamValidWithValue($param, $value, $value);
        $this->assertSame($value, $param->getEmail());
    }

    public function test_invalidValueInRequest() : void
    {
        $this->assertParamInvalidWithValue(
            new EmailParameter('foo', 'Param Label'),
            'invalid@/email/.com'
        );
    }
}
