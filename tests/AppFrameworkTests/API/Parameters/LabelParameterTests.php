<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Parameters;

use Application\API\Parameters\CommonTypes\EmailParameter;
use Application\API\Parameters\CommonTypes\LabelParameter;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;

final class LabelParameterTests extends APITestCase
{
    public function test_validValueInRequest() : void
    {
        $value = 'This is a label';

        $param = new LabelParameter('foo', 'Param Label');

        $this->assertParamValidWithValue($param, $value, $value);
        $this->assertSame($value, $param->getLabelValue());
    }

    public function test_invalidValueInRequest() : void
    {
        $this->assertParamInvalidWithValue(
            new LabelParameter('foo', 'Param Label'),
            ''
        );
    }
}
