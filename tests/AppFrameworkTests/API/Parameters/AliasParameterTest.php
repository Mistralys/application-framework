<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Parameters;

use Application\API\Parameters\CommonTypes\AliasParameter;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;

final class AliasParameterTest extends APITestCase
{
    public function test_validValueInRequest() : void
    {
        $value = 'alias_123-foo';

        $param = new AliasParameter(false, 'foo', 'Param Label');

        $this->assertParamValidWithValue($param, $value, $value);
        $this->assertSame($value, $param->getAlias());
    }

    public function test_validValueWithCapitalLetters() : void
    {
        $value = 'ALIAS_123-foo';

        $param = new AliasParameter(true, 'foo', 'Param Label');

        $this->assertParamValidWithValue($param, $value, $value);
        $this->assertSame($value, $param->getAlias());
    }

    public function test_invalidWithCapitalLettersValue() : void
    {
        $this->assertParamInvalidWithValue(
            new AliasParameter(false, 'foo', 'Param Label'),
            'ALIAS'
        );
    }

    public function test_invalidWithInvalidCharactersValue() : void
    {
        $this->assertParamInvalidWithValue(
            new AliasParameter(false, 'foo', 'Param Label'),
            'alias$foo'
        );
    }
}

