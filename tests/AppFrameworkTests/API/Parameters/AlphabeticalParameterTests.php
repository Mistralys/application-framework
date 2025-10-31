<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Parameters;

use Application\API\Parameters\CommonTypes\AlphabeticalParameter;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;

final class AlphabeticalParameterTests extends APITestCase
{
    public function test_validValueInRequest() : void
    {
        $value = 'Alphabet';

        $param = new AlphabeticalParameter('foo', 'Param Label');

        $this->assertParamValidWithValue($param, $value, $value);
        $this->assertSame($value, $param->getAlphabetical());
    }

    public function test_invalidValueInRequest() : void
    {
        $this->assertParamInvalidWithValue(
            new AlphabeticalParameter('foo', 'Param Label'),
            'abc123'
        );
    }
}

