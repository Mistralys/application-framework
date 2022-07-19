<?php

declare(strict_types=1);

use Mistralys\AppFrameworkTests\TestClasses\ApplicationTestCase;

final class Global_EnumsTest extends ApplicationTestCase
{
    public function test_getValues() : void
    {
        // The values are always sorted by key name
        $expected = array(
            'INT_VALUE' => 5,
            'BOOL_VALUE' => true,
            'FLOAT_VALUE' => 4.78,
            'STRING_VALUE' => 'string'
        );

        $this->assertEquals($expected, TestEnum::getValues());
    }

    public function test_getNames() : void
    {
        $expected = array(
            'BOOL_VALUE',
            'FLOAT_VALUE',
            'INT_VALUE',
            'STRING_VALUE'
        );

        $this->assertEquals($expected, TestEnum::getNames());
    }

    public function test_isValidValue() : void
    {
        $this->assertTrue(TestEnum::isValidValue(4.78));
        $this->assertFalse(TestEnum::isValidValue('unknown value'));
    }

    public function test_isValidName() : void
    {
        $this->assertTrue(TestEnum::isValidName('BOOL_VALUE'));
        $this->assertFalse(TestEnum::isValidName('bool_value'));
    }

    public function test_getNameByValue() : void
    {
        $this->assertEquals('FLOAT_VALUE', TestEnum::getNameByValue(4.78));
    }

    public function test_getNameByValue_notExists() : void
    {
        try
        {
            TestEnum::getNameByValue('unknown value');
        }
        catch (Application_Exception $e)
        {
            $this->assertSame(ENUM_ERROR_NAME_DOES_NOT_EXIST, $e->getCode());
            return;
        }

        $this->fail('No exception thrown');
    }
}
