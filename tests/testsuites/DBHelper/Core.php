<?php

declare(strict_types=1);

use AppUtils\Microtime;

final class DBHelper_Core_TestCase extends ApplicationTestCase
{
    public function test_filterValue() : void
    {
        $tests = array(
            array(
                'label' => 'Microtime',
                'value'=> new Microtime('2021-06-30 14:45:45.555000'),
                'expected' => '2021-06-30 14:45:45.555000'
            ),
            array(
                'label' => 'DateTime',
                'value'=> new DateTime('2021-06-30 14:45:45'),
                'expected' => '2021-06-30 14:45:45'
            ),
            array(
                'label' => 'Boolean',
                'value'=> true,
                'expected' => 'true'
            ),
            array(
                'label' => 'Integer',
                'value'=> 45,
                'expected' => '45'
            ),
            array(
                'label' => 'Float',
                'value'=> 4.478,
                'expected' => '4.478'
            ),
            array(
                'label' => 'Stringable object',
                'value' => sb()->add('text'),
                'expected' => 'text'
            )
        );

        foreach ($tests as $test)
        {
            $result = DBHelper::filterValueForDB($test['value']);

            $this->assertSame($test['expected'], $result, $test['label']);
        }
    }

    public function test_filterObjectException() : void
    {
        try
        {
            DBHelper::filterValueForDB(new stdClass());
        }
        catch (DBHelper_Exception $e)
        {
            $this->assertSame(DBHelper::ERROR_CANNOT_CONVERT_OBJECT, $e->getCode());
            return;
        }

        $this->fail('No exception triggered.');
    }

    public function test_filterArrayException() : void
    {
        try
        {
            DBHelper::filterValueForDB(array());
        }
        catch (DBHelper_Exception $e)
        {
            $this->assertSame(DBHelper::ERROR_CANNOT_CONVERT_ARRAY, $e->getCode());
            return;
        }

        $this->fail('No exception triggered.');
    }

    public function test_filterResourceException() : void
    {
        $res = fopen(__FILE__, 'r');

        try
        {
            DBHelper::filterValueForDB($res);
        }
        catch (DBHelper_Exception $e)
        {
            $this->assertSame(DBHelper::ERROR_CANNOT_CONVERT_RESOURCE, $e->getCode());
            return;
        }
        finally
        {
            fclose($res);
        }

        $this->fail('No exception triggered.');
    }
}
