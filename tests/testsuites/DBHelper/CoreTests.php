<?php
/**
 * @package Application
 * @subpackage UnitTests
 */

declare(strict_types=1);

use AppUtils\Microtime;

/**
 * @package Application
 * @subpackage UnitTests
 */
final class DBHelper_CoreTests extends DBHelperTestCase
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
            ),
            array(
                'label' => 'NULL',
                'value' => null,
                'expected' => null
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

    public function test_isAutoIncrementColumn() : void
    {
        $this->assertFalse(DBHelper::isAutoIncrementColumn(DBHelperTestCase::TEST_RECORDS_TABLE, DBHelperTestCase::TEST_RECORDS_COL_LABEL));
        $this->assertTrue(DBHelper::isAutoIncrementColumn(DBHelperTestCase::TEST_RECORDS_TABLE, DBHelperTestCase::TEST_RECORDS_PRIMARY));
    }

    public function test_tableExists() : void
    {
        $this->assertFalse(DBHelper::tableExists('unknown_table_'.$this->getTestCounter()));
        $this->assertTrue(DBHelper::tableExists(DBHelperTestCase::TEST_RECORDS_TABLE));
        $this->assertTrue(DBHelper::tableExists(DBHelperTestCase::TEST_RECORDS_DATA_TABLE));
    }

    /**
     * Checking if a key exists must only find records with the
     * correct column value combinations.
     */
    public function test_keyExists() : void
    {
        // Create a test record
        $label = 'Test label '.$this->getTestCounter();
        $alias = 'test-alias-'.$this->getTestCounter();

        DBHelper::insertDynamic(
            DBHelperTestCase::TEST_RECORDS_TABLE,
            array(
                DBHelperTestCase::TEST_RECORDS_COL_LABEL => $label,
                DBHelperTestCase::TEST_RECORDS_COL_ALIAS => $alias
            )
        );

        // Attempt to find the record
        $this->assertTrue(
            DBHelper::keyExists(
                DBHelperTestCase::TEST_RECORDS_TABLE,
                array(
                    DBHelperTestCase::TEST_RECORDS_COL_LABEL => $label,
                    DBHelperTestCase::TEST_RECORDS_COL_ALIAS => $alias
                )
            )
        );

        // Try the same with a combination that does not exist.
        $this->assertFalse(
            DBHelper::keyExists(
                DBHelperTestCase::TEST_RECORDS_TABLE,
                array(
                    DBHelperTestCase::TEST_RECORDS_COL_LABEL => $label,
                    DBHelperTestCase::TEST_RECORDS_COL_ALIAS => 'other-alias-'.$this->getTestCounter()
                )
            )
        );
    }

    public function test_getTablesList() : void
    {
        $this->assertContains(DBHelperTestCase::TEST_RECORDS_TABLE, DBHelper::getTablesList());
    }

    public function test_columnExists() : void
    {
        $this->assertTrue(DBHelper::columnExists(
            DBHelperTestCase::TEST_RECORDS_TABLE,
            DBHelperTestCase::TEST_RECORDS_COL_ALIAS
        ));

        $this->assertFalse(DBHelper::columnExists(
            DBHelperTestCase::TEST_RECORDS_TABLE,
            'unknown-column-'.$this->getTestCounter()
        ));
    }
}
