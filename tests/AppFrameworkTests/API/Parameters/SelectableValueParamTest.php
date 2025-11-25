<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Parameters;

use AppFrameworkTestClasses\API\SelectableValueParamStub;
use Application\API\Parameters\APIParameterException;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;

final class SelectableValueParamTest extends APITestCase
{
    public function test_selectValidValue() : void
    {
        $param = new SelectableValueParamStub();

        $param->selectValue(SelectableValueParamStub::VALUE_2);

        $this->assertSame(
            SelectableValueParamStub::VALUE_2,
            $param->getValue()
        );
    }

    public function test_selectUnknownValue() : void
    {
        $this->expectException(APIParameterException::class);
        $this->expectExceptionCode(APIParameterException::ERROR_INVALID_PARAM_VALUE);

        $param = new SelectableValueParamStub();

        $param->selectValue('unknown-value');
    }
}
